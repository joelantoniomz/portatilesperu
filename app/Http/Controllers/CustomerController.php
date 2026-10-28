<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'city' => 'nullable|string|max:80',
        ]);

        if (empty($data['phone']) && empty($data['email'])) {
            return back()->withErrors(['phone' => 'Indica un teléfono o correo para identificar al cliente.'])->withInput();
        }

        $customer = Customer::create([...$data, 'source' => 'manual', 'status' => 'active']);
        AuditLog::log('customer.created', Customer::class, $customer->id, null, ['source' => 'manual']);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Cliente registrado correctamente.');
    }

    /**
     * Listado de clientes con filtros, búsqueda y métricas.
     */
    public function index(Request $request): View
    {
        $query = Customer::withCount('orders')->latest();

        if ($search = $request->input('search')) {
            $query->filter(['search' => $search]);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($city = $request->input('city')) {
            $query->where('city', 'like', "%{$city}%");
        }

        $customers = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'active')->count(),
            'inactive' => Customer::where('status', 'inactive')->count(),
            'recurrent' => Customer::where('orders_count', '>', 1)->count(),
        ];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    /**
     * Ficha detallada del cliente: pedidos, notas, métricas y acceso directo WhatsApp.
     */
    public function show(Customer $customer): View
    {
        $customer->load([
            'orders.items',
            'notes.user',
            'conversations',
        ]);

        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Añade una nota interna sobre el cliente (vendedor / admin).
     */
    public function addNote(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'note' => 'required|string|max:1000',
            'type' => 'required|in:general,followup,complaint,sale',
        ]);

        CustomerNote::create([
            'customer_id' => $customer->id,
            'user_id' => auth()->id(),
            'note' => $request->note,
            'type' => $request->type,
        ]);
        AuditLog::log('customer.note_added', Customer::class, $customer->id, null, ['type' => $request->type]);

        return back()->with('success', 'Nota registrada correctamente sobre el cliente.');
    }

    /**
     * Actualiza el estado o datos básicos del cliente.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $request->validate([
            'status' => 'required|in:active,inactive,blocked',
            'internal_notes' => 'nullable|string|max:2000',
        ]);

        $customer->update([
            'status' => $request->status,
            'internal_notes' => $request->internal_notes,
        ]);
        AuditLog::log('customer.updated', Customer::class, $customer->id, null, ['status' => $request->status]);

        return back()->with('success', "Estado del cliente {$customer->name} actualizado correctamente.");
    }

    /**
     * Recalcula métricas del cliente desde sus pedidos.
     */
    public function recalculate(Customer $customer): RedirectResponse
    {
        $customer->recalculateMetrics();

        return back()->with('success', "Métricas de {$customer->name} actualizadas desde sus pedidos.");
    }
}
