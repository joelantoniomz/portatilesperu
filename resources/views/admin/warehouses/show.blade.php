@extends('layouts.admin')

@section('title', 'Almacén: ' . $warehouse->name)

@section('content')
<div class="space-y-6">

    <!-- Encabezado con Volver y Acciones -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.warehouses.index') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded font-mono text-xs font-bold bg-slate-100 text-slate-700">{{ $warehouse->code }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $warehouse->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $warehouse->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $warehouse->name }}</h2>
                <p class="text-slate-500 text-xs mt-0.5">
                    {{ $warehouse->address ?? 'Sin dirección' }} &bull; {{ $warehouse->city ?? 'Lima' }} &bull; Resp: {{ $warehouse->responsible_name ?? 'No asignado' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.inventory.movements', ['warehouse_id' => $warehouse->id]) }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Kardex Almacén</span>
            </a>

            @if(auth()->user()->hasPermission('inventory.manage'))
            <a href="{{ route('admin.warehouses.edit', $warehouse) }}" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                Editar Datos
            </a>
            <form action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que desea eliminar este almacén? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2.5 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 transition-all">
                    Eliminar Almacén
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Tabla de Existencias en este Almacén -->
    <div class="card-surface border border-slate-100 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Inventario de Productos en esta Sede</h3>
            <span class="text-xs text-slate-400 font-medium">{{ $balances->total() }} registros</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Producto & SKU</th>
                        <th class="py-3.5 px-4 text-center">Físico</th>
                        <th class="py-3.5 px-4 text-center">Reservado</th>
                        <th class="py-3.5 px-4 text-center">Disponible</th>
                        <th class="py-3.5 px-4 text-center">Alerta Mín.</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($balances as $balance)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4">
                            <a href="{{ route('admin.inventory.product', $balance->product_id) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors">
                                {{ $balance->product->name }}
                            </a>
                            <div class="font-mono text-[10px] text-slate-400 font-semibold">{{ $balance->product->sku }}</div>
                        </td>

                        <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm">
                            {{ $balance->qty_physical }}
                        </td>

                        <td class="py-3 px-4 text-center font-bold text-amber-600 text-sm">
                            {{ $balance->qty_reserved }}
                        </td>

                        <td class="py-3 px-4 text-center">
                            @if($balance->qty_available <= 0)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-700">Agotado</span>
                            @elseif($balance->is_low_stock)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-700">{{ $balance->qty_available }} (Bajo)</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-700">{{ $balance->qty_available }}</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-center text-slate-400">
                            {{ $balance->min_alert ?? 0 }}
                        </td>

                        <td class="py-3 px-4 text-center">
                            @if($balance->qty_physical == 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Sin Stock</span>
                            @elseif($balance->is_low_stock)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Alerta Reorden</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Normal</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.inventory.entry', ['product_id' => $balance->product_id, 'warehouse_id' => $warehouse->id]) }}" class="px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] transition-colors">
                                    + Entrada
                                </a>
                                <a href="{{ route('admin.inventory.adjustment', ['product_id' => $balance->product_id, 'warehouse_id' => $warehouse->id]) }}" class="px-2 py-1 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold text-[11px] transition-colors">
                                    Ajustar
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 text-xs">
                            No hay existencias registradas en este almacén.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($balances->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $balances->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
