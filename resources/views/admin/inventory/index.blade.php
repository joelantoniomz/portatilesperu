@extends('layouts.admin')

@section('title', 'Control de Inventario y Stock')

@section('content')
<div class="space-y-6">

    <!-- Encabezado y Acciones -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Fase 3: Inventario Multialmacén, Trazabilidad & QR</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Inventario de Laptops & Periféricos</h2>
            <p class="text-slate-500 text-sm mt-1">
                Monitoreo en tiempo real de stock físico, reservado y disponible con prevención de sobreventa.
            </p>
        </div>

        @if(auth()->user()->hasPermission('inventory.manage'))
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.inventory.entry') }}" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Entrada</span>
            </a>

            <a href="{{ route('admin.inventory.exit') }}" class="px-4 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
                <span>Salida</span>
            </a>

            <a href="{{ route('admin.inventory.adjustment') }}" class="px-4 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-md shadow-amber-600/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Ajuste Físico</span>
            </a>

            <a href="{{ route('admin.inventory.scanner') }}" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Escanear QR</span>
            </a>
        </div>
        @endif
    </div>

    <!-- Indicadores de Stock Global -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Productos en Catálogo</span>
            <p class="text-xl font-black text-slate-900 mt-0.5">{{ $summary['total_products'] }}</p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Stock Físico Total</span>
            <p class="text-xl font-black text-slate-900 mt-0.5">{{ $summary['total_physical'] }} <span class="text-xs font-medium text-slate-400">u.</span></p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Stock Reservado</span>
            <p class="text-xl font-black text-amber-600 mt-0.5">{{ $summary['total_reserved'] }} <span class="text-xs font-medium text-slate-400">u.</span></p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Stock Disponible</span>
            <p class="text-xl font-black text-emerald-600 mt-0.5">{{ $summary['total_available'] }} <span class="text-xs font-medium text-slate-400">u.</span></p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-amber-500 uppercase tracking-wider">Bajo Stock Alerta</span>
            <p class="text-xl font-black text-amber-500 mt-0.5">{{ $summary['low_stock_count'] }}</p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider">Agotados (Sin Stock)</span>
            <p class="text-xl font-black text-rose-600 mt-0.5">{{ $summary['out_of_stock'] }}</p>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card-surface p-5 border border-slate-100">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU, producto..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <select name="warehouse_id" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="stock_filter" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los niveles de stock</option>
                    <option value="low" {{ request('stock_filter') == 'low' ? 'selected' : '' }}>⚠️ Solo con stock bajo</option>
                    <option value="out" {{ request('stock_filter') == 'out' ? 'selected' : '' }}>⛔ Solo agotados (Stock 0)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all flex items-center justify-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Filtrar</span>
                </button>
                @if(request()->anyFilled(['search', 'warehouse_id', 'stock_filter']))
                <a href="{{ route('admin.inventory.index') }}" class="px-3 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold flex items-center">
                    Limpiar
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Balances por Almacén -->
    <div class="card-surface border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Producto & SKU</th>
                        <th class="py-3.5 px-4">Almacén</th>
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
                            <div class="font-bold text-slate-900 leading-tight">
                                <a href="{{ route('admin.inventory.product', $balance->product_id) }}" class="hover:text-emerald-600 transition-colors">
                                    {{ $balance->product->name }}
                                </a>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="font-mono text-[11px] text-slate-500 font-semibold">{{ $balance->product->sku }}</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-500 font-medium">{{ ucfirst($balance->product->status) }}</span>
                            </div>
                        </td>

                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800">{{ $balance->warehouse->name }}</div>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $balance->warehouse->code }}</span>
                        </td>

                        <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm">
                            {{ $balance->qty_physical }}
                        </td>

                        <td class="py-3 px-4 text-center font-bold text-amber-600 text-sm">
                            {{ $balance->qty_reserved }}
                        </td>

                        <td class="py-3 px-4 text-center">
                            @if($balance->qty_available <= 0)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-700">
                                    0 (Agotado)
                                </span>
                            @elseif($balance->is_low_stock)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-amber-100 text-amber-700">
                                    {{ $balance->qty_available }} (Bajo)
                                </span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-700">
                                    {{ $balance->qty_available }}
                                </span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-center text-slate-400 font-medium">
                            {{ $balance->min_alert ?? 0 }}
                        </td>

                        <td class="py-3 px-4 text-center">
                            @if($balance->qty_physical == 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Sin Existencias</span>
                            @elseif($balance->qty_available == 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">100% Reservado</span>
                            @elseif($balance->is_low_stock)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800">Alerta Reorden</span>
                            @else
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">Óptimo</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.inventory.product', $balance->product_id) }}" title="Kardex de este producto" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </a>
                                <a href="{{ route('admin.qr.label', $balance->product_id) }}" title="Ver Etiqueta QR" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="font-bold text-slate-600">No se encontraron balances de stock</p>
                            <p class="text-xs mt-1">Registre entradas o traslados para inicializar los balances en almacenes.</p>
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
