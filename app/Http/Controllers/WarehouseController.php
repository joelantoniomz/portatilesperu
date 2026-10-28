<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $warehouses = Warehouse::withCount(['inventoryBalances', 'stockMovements'])
            ->latest('id')
            ->paginate(15);

        return view('admin.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('admin.warehouses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:warehouses,code',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:80',
            'responsible_name' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $warehouse = Warehouse::create($data);

        return redirect()->route('admin.warehouses.show', $warehouse)
            ->with('success', "Almacén '{$warehouse->name}' creado correctamente.");
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->loadCount(['inventoryBalances', 'stockMovements']);
        $balances = $warehouse->inventoryBalances()
            ->with('product:id,name,sku')
            ->latest('id')
            ->paginate(20);

        return view('admin.warehouses.show', compact('warehouse', 'balances'));
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:warehouses,code,'.$warehouse->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:80',
            'responsible_name' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $warehouse->update($data);

        AuditLog::log('warehouse_updated', 'warehouses', $warehouse->id, null, ['name' => $warehouse->name]);

        return redirect()->route('admin.warehouses.show', $warehouse)
            ->with('status', 'Almacén actualizado correctamente.');
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $activeStock = $warehouse->inventoryBalances()->where('qty_physical', '>', 0)->count();

        if ($activeStock > 0) {
            return back()->withErrors(['error' => "No se puede eliminar el almacén '{$warehouse->name}' porque cuenta con {$activeStock} producto(s) con stock físico activo. Debe transferir o dar salida a la mercadería antes de eliminarlo."]);
        }

        if ($warehouse->stockReservations()->where('status', 'active')->exists()) {
            return back()->withErrors(['error' => "No se puede eliminar el almacén '{$warehouse->name}' porque tiene reservas de stock activas pendientes."]);
        }

        if ($warehouse->stockMovements()->exists()) {
            return back()->withErrors(['error' => "No se puede eliminar el almacén '{$warehouse->name}' porque cuenta con registros históricos en el Kardex de movimientos. Para conservar la trazabilidad contable, puede desactivarlo marcándolo como Inactivo."]);
        }

        $name = $warehouse->name;
        $code = $warehouse->code;

        $warehouse->inventoryBalances()->delete();
        $warehouse->delete();

        AuditLog::log('warehouse_deleted', 'warehouses', null, null, ['name' => $name, 'code' => $code]);

        return redirect()->route('admin.warehouses.index')
            ->with('status', "Almacén '{$name}' ({$code}) eliminado correctamente.");
    }
}
