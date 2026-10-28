<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * Muestra la vista del carrito de compras / solicitud.
     */
    public function index(): View
    {
        $cart = session()->get('cart', []);
        $items = [];
        $subtotal = 0;

        foreach ($cart as $productId => $details) {
            $product = Product::published()->find($productId);
            if ($product) {
                $qty = (int) $details['quantity'];
                $itemSubtotal = $product->price * $qty;
                $subtotal += $itemSubtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $itemSubtotal,
                ];
            }
        }

        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();

        return view('public.cart', compact('items', 'subtotal', 'warehouses'));
    }

    /**
     * Añade un producto al carrito.
     */
    public function add(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:20',
        ]);

        $productId = (int) $request->product_id;
        $quantity = (int) ($request->quantity ?? 1);

        $product = Product::published()->findOrFail($productId);
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        if ($request->boolean('buy_now')) {
            return redirect()->route('carrito')->with('success', "'{$product->name}' agregado. Complete sus datos para coordinar por WhatsApp.");
        }

        return back()->with('success', "Laptop '{$product->name}' agregada al carrito.");
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:20',
        ]);

        $cart = session()->get('cart', []);
        $productId = (int) $request->product_id;

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = (int) $request->quantity;
            session()->put('cart', $cart);
        }

        return redirect()->route('carrito')->with('success', 'Cantidad actualizada correctamente.');
    }

    /**
     * Elimina un producto del carrito.
     */
    public function remove(Request $request): RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
        ]);

        $cart = session()->get('cart', []);
        $productId = (int) $request->product_id;

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return redirect()->route('carrito')->with('success', 'Producto eliminado del carrito.');
    }

    /**
     * Vacía el carrito por completo.
     */
    public function clear(): RedirectResponse
    {
        session()->forget('cart');

        return redirect()->route('carrito')->with('success', 'El carrito ha sido vaciado.');
    }

    /**
     * Procesa la solicitud, crea el pedido en la DB, reserva stock y genera el mensaje WhatsApp.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:120',
            'customer_city' => 'required|string|max:80',
            'customer_address' => 'nullable|string|max:300',
            'document_type' => 'nullable|in:dni,ruc,ce',
            'document_number' => 'nullable|string|max:20',
            'invoice_type' => 'required|in:boleta,factura,nota_venta',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('catalogo')->with('error', 'Su carrito está vacío. Seleccione una laptop primero.');
        }

        $cartItems = [];
        foreach ($cart as $productId => $details) {
            $cartItems[] = [
                'product_id' => $productId,
                'quantity' => $details['quantity'],
            ];
        }

        $order = $this->orderService->createOrder(
            customerData: $request->all(),
            cartItems: $cartItems,
            warehouseId: $request->warehouse_id,
            userId: auth()->id()
        );

        // Limpiar carrito de compras
        session()->forget('cart');

        return redirect()->route('pedido.confirmacion', $order->order_number)
            ->with('success', "¡Solicitud {$order->order_number} registrada con éxito! Stock reservado preventivamente.");
    }

    /**
     * Pantalla de confirmación del pedido con botón oficial para abrir WhatsApp.
     */
    public function confirmation(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'warehouse'])
            ->firstOrFail();

        return view('public.order_confirmation', compact('order'));
    }
}
