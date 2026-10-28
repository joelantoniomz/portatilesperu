<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryBalance;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\StockReservation;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderProcessingTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $unauthorizedUser;

    private Warehouse $warehouse;

    private Product $productA;

    private Product $productB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear almacén principal
        $this->warehouse = Warehouse::create([
            'name' => 'Tienda Miraflores (Principal)',
            'code' => 'ALM-MIRA',
            'city' => 'Lima',
            'address' => 'Av. Petit Thouars 5356',
            'is_active' => true,
        ]);

        // 2. Crear categoría y marca
        $category = Category::create([
            'name' => 'Laptops Gaming',
            'slug' => 'laptops-gaming',
            'is_active' => true,
            'order_index' => 1,
        ]);

        $brand = Brand::create([
            'name' => 'ASUS ROG',
            'slug' => 'asus-rog',
            'is_active' => true,
        ]);

        // 3. Crear productos de prueba con precios en backend
        $this->productA = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'ASUS ROG Strix G16 RTX 4070',
            'slug' => 'asus-rog-strix-g16-rtx-4070',
            'sku' => 'ASUS-ROG-G16-4070',
            'price' => 7299.00,
            'currency' => 'PEN',
            'condition' => 'new',
            'status' => 'published',
        ]);

        $this->productB = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Lenovo Legion Pro 5 RTX 4060',
            'slug' => 'lenovo-legion-pro-5-rtx-4060',
            'sku' => 'LEN-LEG-5-4060',
            'price' => 5499.00,
            'currency' => 'PEN',
            'condition' => 'new',
            'status' => 'published',
        ]);

        // 4. Registrar stock físico inicial usando InventoryService
        $inventoryService = app(InventoryService::class);
        $systemUser = User::factory()->create();

        $inventoryService->registerEntry(
            productId: $this->productA->id,
            warehouseId: $this->warehouse->id,
            userId: $systemUser->id,
            quantity: 10,
            unitCost: 6000.00,
            reference: 'FAC-001',
            reason: 'Stock inicial producto A'
        );

        $inventoryService->registerEntry(
            productId: $this->productB->id,
            warehouseId: $this->warehouse->id,
            userId: $systemUser->id,
            quantity: 3,
            unitCost: 4500.00,
            reference: 'FAC-002',
            reason: 'Stock inicial producto B'
        );

        // 5. Configurar roles y permisos
        $orderView = Permission::create(['name' => 'orders.view', 'display_name' => 'Ver pedidos', 'module' => 'pedidos']);
        $orderManage = Permission::create(['name' => 'orders.manage', 'display_name' => 'Gestionar pedidos', 'module' => 'pedidos']);

        $adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        $adminRole->permissions()->sync([$orderView->id, $orderManage->id]);

        $this->adminUser = User::factory()->create(['status' => 'active']);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $clientRole = Role::create(['name' => 'cliente', 'display_name' => 'Cliente']);
        $this->unauthorizedUser = User::factory()->create(['status' => 'active']);
        $this->unauthorizedUser->roles()->sync([$clientRole->id]);
    }

    public function test_user_can_view_cart_and_add_products(): void
    {
        $response = $this->get(route('carrito'));
        $response->assertStatus(200);
        $response->assertSee('Tu carrito está actualmente vacío');

        // Agregar Laptop A al carrito
        $addResponse = $this->post(route('carrito.agregar'), [
            'product_id' => $this->productA->id,
            'quantity' => 2,
        ]);

        $addResponse->assertSessionHas('cart');
        $cart = session('cart');
        $this->assertArrayHasKey($this->productA->id, $cart);
        $this->assertEquals(2, $cart[$this->productA->id]['quantity']);

        // Ahora la vista de carrito muestra el producto y el subtotal calculado
        $cartView = $this->get(route('carrito'));
        $cartView->assertStatus(200);
        $cartView->assertSee($this->productA->name);
        $cartView->assertSee('ASUS-ROG-G16-4070');
        $cartView->assertSee('14,598.00'); // 7299 * 2
    }

    public function test_user_can_update_quantity_and_remove_items_from_cart(): void
    {
        // Agregar al carrito
        $this->post(route('carrito.agregar'), [
            'product_id' => $this->productA->id,
            'quantity' => 1,
        ]);

        // Actualizar a 3
        $updateResponse = $this->post(route('carrito.actualizar'), [
            'product_id' => $this->productA->id,
            'quantity' => 3,
        ]);
        $updateResponse->assertRedirect(route('carrito'));
        $this->assertEquals(3, session('cart')[$this->productA->id]['quantity']);

        // Eliminar del carrito
        $removeResponse = $this->post(route('carrito.eliminar'), [
            'product_id' => $this->productA->id,
        ]);
        $removeResponse->assertRedirect(route('carrito'));
        $this->assertArrayNotHasKey($this->productA->id, session('cart', []));
    }

    public function test_order_is_saved_in_database_with_unique_reference_before_whatsapp(): void
    {
        // 1. Simular producto en carrito
        session()->put('cart', [
            $this->productA->id => ['quantity' => 1],
            $this->productB->id => ['quantity' => 2],
        ]);

        $customerData = [
            'customer_name' => 'Renato Silva Cárdenas',
            'customer_phone' => '987654321',
            'customer_email' => 'renato@empresa.pe',
            'customer_city' => 'Lima - San Isidro',
            'customer_address' => 'Av. Javier Prado Este 450',
            'invoice_type' => 'factura',
            'document_type' => 'ruc',
            'document_number' => '20608976541',
            'warehouse_id' => $this->warehouse->id,
            'notes' => 'Requiere despacho en caja sellada con precinto de garantía.',
        ];

        // 2. Realizar checkout
        $response = $this->post(route('pedido.checkout'), $customerData);

        // 3. Verificar que el pedido existe en Base de Datos
        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertStringStartsWith('PED-'.date('Y').'-', $order->order_number);
        $this->assertEquals('Renato Silva Cárdenas', $order->customer_name);
        $this->assertEquals('987654321', $order->customer_phone);
        $this->assertEquals('pending', $order->status);

        // Subtotal esperado: (7299 * 1) + (5499 * 2) = 7299 + 10998 = 18297
        $this->assertEquals(18297.00, (float) $order->total);
        $this->assertEquals(2, $order->items()->count());

        // Verificar items del pedido
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->productA->id,
            'product_sku' => $this->productA->sku,
            'product_name' => $this->productA->name,
            'quantity' => 1,
            'unit_price' => 7299.00,
            'subtotal' => 7299.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->productB->id,
            'product_sku' => $this->productB->sku,
            'product_name' => $this->productB->name,
            'quantity' => 2,
            'unit_price' => 5499.00,
            'subtotal' => 10998.00,
        ]);

        // Verificar que las reservas preventivas fueron creadas y redujeron qty_available
        $balanceA = InventoryBalance::where('product_id', $this->productA->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(10, $balanceA->qty_physical);
        $this->assertEquals(1, $balanceA->qty_reserved);
        $this->assertEquals(9, $balanceA->qty_available);

        $balanceB = InventoryBalance::where('product_id', $this->productB->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(3, $balanceB->qty_physical);
        $this->assertEquals(2, $balanceB->qty_reserved);
        $this->assertEquals(1, $balanceB->qty_available);

        // 4. Verificar mensaje oficial de WhatsApp generado con referencia y contenido
        $this->assertNotNull($order->whatsapp_message);
        $this->assertStringContainsString($order->order_number, $order->whatsapp_message);
        $this->assertStringContainsString('Renato Silva Cárdenas', $order->whatsapp_message);
        $this->assertStringContainsString('18,297.00', $order->whatsapp_message);
        $this->assertStringContainsString($this->productA->sku, $order->whatsapp_message);
        $this->assertStringContainsString($this->productB->sku, $order->whatsapp_message);

        // 5. Verificar redirección a pantalla de confirmación
        $response->assertRedirect(route('pedido.confirmacion', $order->order_number));

        // 6. Verificar pantalla de confirmación pública
        $confirmationView = $this->get(route('pedido.confirmacion', $order->order_number));
        $confirmationView->assertStatus(200);
        $confirmationView->assertSee($order->order_number);
        $confirmationView->assertSee('Reserva de Stock Temporal Activa');
        $confirmationView->assertSee('Abrir WhatsApp para Coordinar');
        $confirmationView->assertSee(e($order->whatsapp_url), false);
    }

    public function test_backend_calculates_and_validates_prices_not_trusting_client(): void
    {
        $orderService = app(OrderService::class);

        // Intentar enviar datos con manipulación maliciosa de precios o intentos de descuento no autorizados
        $order = $orderService->createOrder(
            customerData: [
                'customer_name' => 'Cliente Intentando Precio Falso',
                'customer_phone' => '999888777',
                'customer_city' => 'Lima',
                'unit_price' => 1.00, // Atacante intenta pagar S/ 1.00
                'total' => 1.00,
            ],
            cartItems: [
                ['product_id' => $this->productA->id, 'quantity' => 1],
            ],
            warehouseId: $this->warehouse->id
        );

        // El servidor debe ignorar cualquier parámetro manipulado y calcular estrictamente S/ 7299.00
        $this->assertEquals(7299.00, (float) $order->total);
        $this->assertEquals(7299.00, (float) $order->subtotal);
        $this->assertEquals(7299.00, (float) $order->items->first()->unit_price);
    }

    public function test_order_creation_fails_if_quantity_is_invalid(): void
    {
        $orderService = app(OrderService::class);

        $this->expectException(ValidationException::class);

        $orderService->createOrder(
            customerData: [
                'customer_name' => 'Prueba Cantidad Inválida',
                'customer_phone' => '911222333',
                'customer_city' => 'Lima',
            ],
            cartItems: [
                ['product_id' => $this->productA->id, 'quantity' => 0], // Inválido <= 0
            ],
            warehouseId: $this->warehouse->id
        );
    }

    public function test_order_creation_fails_if_insufficient_stock(): void
    {
        $orderService = app(OrderService::class);

        // Producto B solo tiene 3 unidades en stock; solicitamos 5
        $this->expectException(ValidationException::class);

        $orderService->createOrder(
            customerData: [
                'customer_name' => 'Prueba Stock Insuficiente',
                'customer_phone' => '911222444',
                'customer_city' => 'Lima',
            ],
            cartItems: [
                ['product_id' => $this->productB->id, 'quantity' => 5],
            ],
            warehouseId: $this->warehouse->id
        );

        // Verificar que no se creó ningún pedido ni reserva
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, StockReservation::count());
    }

    public function test_order_cancellation_releases_preventive_stock_reservations(): void
    {
        $orderService = app(OrderService::class);

        $order = $orderService->createOrder(
            customerData: [
                'customer_name' => 'Cliente que cancelará',
                'customer_phone' => '987111222',
                'customer_city' => 'Lima',
            ],
            cartItems: [
                ['product_id' => $this->productB->id, 'quantity' => 2],
            ],
            warehouseId: $this->warehouse->id
        );

        // Antes de cancelar: 3 físico, 2 reservado, 1 disponible
        $balance = InventoryBalance::where('product_id', $this->productB->id)
            ->where('warehouse_id', $this->warehouse->id)
            ->first();
        $this->assertEquals(1, $balance->qty_available);
        $this->assertEquals(2, $balance->qty_reserved);

        // Cancelar el pedido mediante updateStatus
        $orderService->updateStatus(
            order: $order,
            newStatus: 'cancelled',
            notes: 'Cliente desistió por motivos personales.',
            userId: $this->adminUser->id
        );

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        // La reserva debe estar cancelada y el saldo disponible restaurado
        $balance->refresh();
        $this->assertEquals(3, $balance->qty_physical);
        $this->assertEquals(0, $balance->qty_reserved);
        $this->assertEquals(3, $balance->qty_available);

        $reservation = StockReservation::where('order_reference', $order->order_number)->first();
        $this->assertEquals('cancelled', $reservation->status);
    }

    public function test_admin_can_view_orders_and_filter_by_status(): void
    {
        $orderService = app(OrderService::class);

        $order1 = $orderService->createOrder(
            customerData: ['customer_name' => 'Carlos Cliente', 'customer_phone' => '999111222', 'customer_city' => 'Lima'],
            cartItems: [['product_id' => $this->productA->id, 'quantity' => 1]],
            warehouseId: $this->warehouse->id
        );

        $order2 = $orderService->createOrder(
            customerData: ['customer_name' => 'Ana Compradora', 'customer_phone' => '999333444', 'customer_city' => 'Arequipa'],
            cartItems: [['product_id' => $this->productB->id, 'quantity' => 1]],
            warehouseId: $this->warehouse->id
        );

        // Admin con permiso orders.view accede con éxito
        $response = $this->actingAs($this->adminUser)->get(route('admin.orders.index'));
        $response->assertStatus(200);
        $response->assertSee($order1->order_number);
        $response->assertSee($order2->order_number);
        $response->assertSee('Carlos Cliente');
        $response->assertSee('Ana Compradora');

        // Ver detalle del pedido 1
        $showResponse = $this->actingAs($this->adminUser)->get(route('admin.orders.show', $order1));
        $showResponse->assertStatus(200);
        $showResponse->assertSee($order1->order_number);
        $showResponse->assertSee($this->productA->name);

        // Usuario sin permiso recibe 403 Forbidden
        $forbiddenResponse = $this->actingAs($this->unauthorizedUser)->get(route('admin.orders.index'));
        $forbiddenResponse->assertStatus(403);
    }

    public function test_admin_with_manage_permission_can_transition_order_status(): void
    {
        $orderService = app(OrderService::class);

        $order = $orderService->createOrder(
            customerData: ['customer_name' => 'Pedro Pérez', 'customer_phone' => '987000111', 'customer_city' => 'Lima'],
            cartItems: [['product_id' => $this->productA->id, 'quantity' => 1]],
            warehouseId: $this->warehouse->id
        );

        // Cambiar a confirmado
        $response = $this->actingAs($this->adminUser)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'confirmed',
            'notes' => 'Transferencia BCP verificada en ventanilla.',
        ]);

        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('confirmed', $order->status);

        // Cambiar a despachado
        $dispatchResponse = $this->actingAs($this->adminUser)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'dispatched',
            'notes' => 'Entregado a motorizado con guía de remisión.',
        ]);

        $dispatchResponse->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('dispatched', $order->status);

        // Verificar historial
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id,
            'status_from' => 'confirmed',
            'status_to' => 'dispatched',
            'notes' => 'Entregado a motorizado con guía de remisión.',
        ]);
    }
}
