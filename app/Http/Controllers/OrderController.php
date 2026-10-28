<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * Listado administrativo de pedidos con filtros, búsqueda y métricas rápidas.
     */
    public function index(Request $request): View
    {
        $query = Order::with(['items', 'warehouse', 'user'])
            ->latest();

        // Filtro por búsqueda (código de pedido, nombre o teléfono)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filtro por almacén
        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        // Filtro por fecha desde / hasta
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $orders = $query->paginate(15)->withQueryString();

        // Estadísticas rápidas para los KPI cards
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'confirmed' => Order::where('status', 'confirmed')->count(),
            'in_preparation' => Order::where('status', 'in_preparation')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'total_sales' => Order::whereIn('status', ['confirmed', 'dispatched', 'completed'])->sum('total'),
        ];

        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.orders.index', compact('orders', 'stats', 'warehouses'));
    }

    /**
     * Detalle completo del pedido, items, trazabilidad de estados y reservas de stock.
     */
    public function show(Order $order): View
    {
        $order->load([
            'items.product',
            'items.reservation',
            'warehouse',
            'user',
            'statusHistory.user',
        ]);

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Actualiza el estado del pedido y gestiona la liberación o despacho de inventario.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,in_preparation,dispatched,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        $this->orderService->updateStatus(
            order: $order,
            newStatus: $request->status,
            notes: $request->notes,
            userId: auth()->id()
        );

        return back()->with('success', "Estado del pedido {$order->order_number} actualizado a '".ucfirst($request->status)."' exitosamente.");
    }

    /**
     * Confirma el pago de un pedido en un solo clic.
     */
    public function confirmPayment(Order $order): RedirectResponse
    {
        $this->orderService->updateStatus(
            order: $order,
            newStatus: 'confirmed',
            notes: 'Pago confirmado y verificado por '.auth()->user()->name,
            userId: auth()->id()
        );

        return back()->with('success', "¡Pago confirmado! El pedido {$order->order_number} ahora figura como 'Confirmado' y las métricas del cliente se han actualizado.");
    }
}
