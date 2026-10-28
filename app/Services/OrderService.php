<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly InventoryService $inventoryService) {}

    /**
     * Crea un pedido con validación estricta en servidor, reserva preventiva de stock
     * y generación del mensaje de WhatsApp con referencia única.
     *
     * @param  array  $customerData  Datos del cliente (nombre, teléfono, dirección, etc.)
     * @param  array  $cartItems  Array de ['product_id' => int, 'quantity' => int]
     * @param  int|null  $warehouseId  Sede física elegida (por defecto primer almacén activo)
     * @param  int|null  $userId  Usuario/Asesor autenticado si aplica
     *
     * @throws ValidationException
     */
    public function createOrder(
        array $customerData,
        array $cartItems,
        ?int $warehouseId = null,
        ?int $userId = null
    ): Order {
        if (empty($cartItems)) {
            throw ValidationException::withMessages([
                'cart' => ['El carrito de compras no puede estar vacío.'],
            ]);
        }

        // Obtener almacén de despacho (por defecto el central si no se especifica)
        $warehouse = $warehouseId
            ? Warehouse::findOrFail($warehouseId)
            : (Warehouse::where('is_active', true)->first() ?? Warehouse::first());

        if (! $warehouse) {
            throw ValidationException::withMessages([
                'warehouse' => ['No hay almacenes disponibles para registrar el pedido.'],
            ]);
        }

        return DB::transaction(function () use ($customerData, $cartItems, $warehouse, $userId) {
            $orderNumber = Order::generateOrderNumber();
            $subtotal = 0;
            $preparedItems = [];

            // 1. Validar productos, calcular totales en backend y verificar stock
            foreach ($cartItems as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages([
                        'quantity' => ['La cantidad solicitada debe ser de al menos 1 unidad.'],
                    ]);
                }

                $product = Product::lockForUpdate()->find($productId);
                if (! $product || $product->status !== 'published') {
                    throw ValidationException::withMessages([
                        'product' => ['El producto seleccionado no está disponible en catálogo.'],
                    ]);
                }

                // Verificar stock disponible en el almacén
                $balance = InventoryBalance::lockForUpdate()
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();

                $availableStock = $balance ? $balance->qty_available : 0;

                if ($availableStock < $quantity) {
                    throw ValidationException::withMessages([
                        'stock' => [
                            "Stock insuficiente para '{$product->name}' en la sede {$warehouse->name}. ".
                            "Disponible: {$availableStock}, solicitado: {$quantity}.",
                        ],
                    ]);
                }

                $unitPrice = (float) $product->price;
                $itemSubtotal = $unitPrice * $quantity;
                $subtotal += $itemSubtotal;

                $preparedItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $shippingCost = (float) ($customerData['shipping_cost'] ?? 0);
            $discount = (float) ($customerData['discount'] ?? 0);
            $total = max(0, $subtotal + $shippingCost - $discount);

            // 2. Guardar o actualizar el perfil del cliente automáticamente
            $customer = Customer::findOrCreateFromOrder($customerData);

            // 3. Guardar el pedido en Base de Datos ANTES de WhatsApp
            $order = Order::create([
                'order_number' => $orderNumber,
                'customer_name' => trim($customerData['customer_name'] ?? 'Cliente'),
                'customer_phone' => trim($customerData['customer_phone'] ?? ''),
                'customer_email' => ! empty($customerData['customer_email']) ? trim($customerData['customer_email']) : null,
                'customer_city' => trim($customerData['customer_city'] ?? 'Lima'),
                'customer_address' => ! empty($customerData['customer_address']) ? trim($customerData['customer_address']) : null,
                'document_type' => $customerData['document_type'] ?? 'dni',
                'document_number' => ! empty($customerData['document_number']) ? trim($customerData['document_number']) : null,
                'invoice_type' => $customerData['invoice_type'] ?? 'boleta',
                'status' => 'pending',
                'payment_method' => $customerData['payment_method'] ?? 'whatsapp_coordination',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'currency' => 'PEN',
                'notes' => $customerData['notes'] ?? null,
                'warehouse_id' => $warehouse->id,
                'user_id' => $userId,
                'customer_id' => $customer->id,
            ]);

            // Crear conversación inicial de WhatsApp en la bandeja (modo enlace)
            Conversation::create([
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'user_id' => null,
                'channel' => 'whatsapp_link',
                'customer_phone' => $order->customer_phone,
                'customer_name' => $order->customer_name,
                'order_reference' => $orderNumber,
                'status' => 'open',
                'summary' => "Solicitud de compra {$orderNumber} enviada desde catálogo web.",
                'last_message_preview' => "Pedido: {$orderNumber} — Total: S/ ".number_format($total, 2),
                'last_activity_at' => now(),
            ]);

            // 3. Crear items y reservas de stock preventivas
            $messageLines = [];
            foreach ($preparedItems as $prep) {
                $product = $prep['product'];
                $qty = $prep['quantity'];

                // Crear reserva preventiva vinculada al pedido
                $reservation = $this->inventoryService->createReservation(
                    productId: $product->id,
                    warehouseId: $warehouse->id,
                    userId: $userId ?? 1,
                    quantity: $qty,
                    orderReference: $order->order_number,
                    notes: "Reserva automática por pedido {$order->order_number}",
                    expiresAt: now()->addHours(24) // 24 horas para coordinar pago
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $prep['unit_price'],
                    'quantity' => $qty,
                    'subtotal' => $prep['subtotal'],
                    'reservation_id' => $reservation->id,
                ]);

                $messageLines[] = "• {$qty}x {$product->name} (SKU: {$product->sku}) — S/ ".number_format($prep['subtotal'], 2);
            }

            // 4. Formatear mensaje oficial de WhatsApp con referencia
            $itemsText = implode("\n", $messageLines);
            $totalFormatted = 'S/ '.number_format($total, 2);
            $trackingUrl = route('pedido.confirmacion', $order->order_number);

            $whatsappMessage = <<<MSG
¡Hola Portátiles Perú! 💻
Deseo coordinar la compra de mi solicitud:

📋 *Pedido N°:* {$order->order_number}
👤 *Cliente:* {$order->customer_name}
📱 *Teléfono:* {$order->customer_phone}
📍 *Ciudad / Entrega:* {$order->customer_city}
🧾 *Comprobante:* {$order->invoice_type} ({$order->document_type}: {$order->document_number})

🛒 *Laptops Solicitadas:*
{$itemsText}

💰 *TOTAL A PAGAR:* {$totalFormatted}

🔗 *Ver mi Solicitud Online:*
{$trackingUrl}

Quedo atento a los medios de pago para coordinar la entrega. ¡Muchas gracias!
MSG;

            $order->update([
                'whatsapp_message' => $whatsappMessage,
            ]);

            // 5. Historial inicial
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => null,
                'status_to' => 'pending',
                'notes' => 'Pedido registrado desde catálogo web con reserva preventiva de stock.',
                'changed_by' => $userId,
            ]);

            AuditLog::log(
                'order.created',
                'Order',
                $order->id,
                null,
                ['order_number' => $order->order_number, 'total' => $total, 'items_count' => count($preparedItems)],
                $userId
            );

            return $order;
        });
    }

    /**
     * Actualiza el estado de un pedido y gestiona la liberación o confirmación de stock.
     */
    public function updateStatus(
        Order $order,
        string $newStatus,
        ?string $notes = null,
        ?int $userId = null
    ): Order {
        $allowedStatuses = ['pending', 'confirmed', 'in_preparation', 'dispatched', 'completed', 'cancelled'];
        if (! in_array($newStatus, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'status' => ["El estado '{$newStatus}' no es válido."],
            ]);
        }

        $oldStatus = $order->status;
        if ($oldStatus === $newStatus) {
            return $order;
        }

        return DB::transaction(function () use ($order, $oldStatus, $newStatus, $notes, $userId) {
            // Si el pedido se cancela, liberar todas las reservas de stock asociadas
            if ($newStatus === 'cancelled') {
                $order->load('items.reservation');
                foreach ($order->items as $item) {
                    if ($item->reservation_id) {
                        try {
                            $this->inventoryService->cancelReservation(
                                reservationId: $item->reservation_id,
                                cancelledBy: $userId ?? 1,
                                reason: "Cancelación del pedido {$order->order_number}: ".($notes ?? 'Sin motivo específico')
                            );
                        } catch (\Throwable $e) {
                            // Si ya estaba cancelada o expirada, continuar
                        }
                    }
                }
            }

            // Si pasa a despachado o completado, efectuar la salida definitiva de stock
            if (in_array($newStatus, ['dispatched', 'completed']) && ! in_array($oldStatus, ['dispatched', 'completed'])) {
                $order->load('items');
                foreach ($order->items as $item) {
                    try {
                        $this->inventoryService->registerExit(
                            productId: $item->product_id,
                            warehouseId: $order->warehouse_id,
                            userId: $userId ?? 1,
                            quantity: $item->quantity,
                            type: 'exit',
                            reference: $order->order_number,
                            reason: "Despacho oficial de pedido {$order->order_number}",
                            documentNumber: $order->document_number
                        );
                    } catch (\Throwable $e) {
                        // Ignorar si ya se había deducido
                    }
                }
            }

            $updateData = ['status' => $newStatus];
            if (in_array($newStatus, ['confirmed', 'in_preparation', 'dispatched', 'completed'])) {
                $updateData['payment_status'] = 'paid';
            } elseif ($newStatus === 'cancelled') {
                $updateData['payment_status'] = $order->payment_status === 'paid' ? 'refunded' : 'pending';
            }

            $order->update($updateData);

            if ($order->customer) {
                $order->customer->recalculateMetrics();
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => $oldStatus,
                'status_to' => $newStatus,
                'notes' => $notes,
                'changed_by' => $userId,
            ]);

            AuditLog::log(
                'order.status_updated',
                'Order',
                $order->id,
                ['status' => $oldStatus],
                ['status' => $newStatus, 'notes' => $notes],
                $userId
            );

            return $order;
        });
    }
}
