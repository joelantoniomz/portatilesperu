@extends('layouts.admin')

@section('title', 'Kardex de Movimientos de Inventario')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Kardex General Inmutable</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Historial de Movimientos de Inventario</h2>
            <p class="text-slate-500 text-sm mt-1">
                Registro criptográfico y trazable de todas las entradas, salidas, traslados y ajustes por almacén y usuario.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver a Balances</span>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="card-surface p-5 border border-slate-100">
        <form method="GET" action="{{ route('admin.inventory.movements') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <select name="warehouse_id" class="w-full px-3.5 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los almacenes</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                        {{ $wh->name }} ({{ $wh->code }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="type" class="w-full px-3.5 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los tipos</option>
                    <option value="entry" {{ request('type') == 'entry' ? 'selected' : '' }}>Entrada Regular</option>
                    <option value="exit" {{ request('type') == 'exit' ? 'selected' : '' }}>Salida Regular</option>
                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Ajuste Físico</option>
                    <option value="transfer_in" {{ request('type') == 'transfer_in' ? 'selected' : '' }}>Traslado Entrada</option>
                    <option value="transfer_out" {{ request('type') == 'transfer_out' ? 'selected' : '' }}>Traslado Salida</option>
                    <option value="reservation" {{ request('type') == 'reservation' ? 'selected' : '' }}>Reserva Temporal</option>
                    <option value="release" {{ request('type') == 'release' ? 'selected' : '' }}>Liberación Reserva</option>
                    <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>Devolución</option>
                </select>
            </div>

            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" title="Desde" class="w-full px-3.5 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <input type="date" name="date_to" value="{{ request('date_to') }}" title="Hasta" class="w-full px-3.5 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Filtrar</span>
                </button>
                @if(request()->anyFilled(['warehouse_id', 'type', 'date_from', 'date_to', 'product_id']))
                <a href="{{ route('admin.inventory.movements') }}" class="px-3 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold flex items-center">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Kardex -->
    <div class="card-surface border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Fecha & Hora</th>
                        <th class="py-3.5 px-4">Producto & SKU</th>
                        <th class="py-3.5 px-4">Almacén</th>
                        <th class="py-3.5 px-4 text-center">Tipo</th>
                        <th class="py-3.5 px-4 text-center">Cant.</th>
                        <th class="py-3.5 px-4 text-center">Antes → Después</th>
                        <th class="py-3.5 px-4">Referencia / Motivo</th>
                        <th class="py-3.5 px-4">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($movements as $mov)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            {{ $mov->moved_at ? $mov->moved_at->format('d/m/Y H:i') : $mov->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="py-3 px-4">
                            <a href="{{ route('admin.inventory.product', $mov->product_id) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors">
                                {{ $mov->product->name }}
                            </a>
                            <div class="text-[10px] font-mono text-slate-400 font-semibold">{{ $mov->product->sku }}</div>
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="font-semibold text-slate-800">{{ $mov->warehouse->name }}</span>
                            <span class="text-[10px] text-slate-400 block font-mono">{{ $mov->warehouse->code }}</span>
                        </td>

                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @php
                                $badgeClasses = match($mov->type) {
                                    'entry', 'transfer_in', 'return' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'exit', 'transfer_out'           => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'adjustment'                     => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'reservation'                    => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'release'                        => 'bg-purple-100 text-purple-800 border-purple-200',
                                    default                          => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                                $typeLabels = [
                                    'entry'        => 'Entrada',
                                    'exit'         => 'Salida',
                                    'adjustment'   => 'Ajuste',
                                    'transfer_in'  => 'Traslado In',
                                    'transfer_out' => 'Traslado Out',
                                    'reservation'  => 'Reserva',
                                    'release'      => 'Liberación',
                                    'return'       => 'Devolución',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black border {{ $badgeClasses }}">
                                {{ $typeLabels[$mov->type] ?? ucfirst($mov->type) }}
                            </span>
                        </td>

                        <td class="py-3 px-4 text-center font-bold text-sm whitespace-nowrap">
                            @if(in_array($mov->type, ['entry', 'transfer_in', 'return', 'release']))
                                <span class="text-emerald-600">+{{ $mov->quantity }}</span>
                            @elseif(in_array($mov->type, ['exit', 'transfer_out', 'reservation']))
                                <span class="text-rose-600">-{{ $mov->quantity }}</span>
                            @else
                                <span class="text-slate-800">{{ $mov->quantity > 0 ? "+{$mov->quantity}" : $mov->quantity }}</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            {{ $mov->balance_before }} → <span class="font-bold text-slate-800">{{ $mov->balance_after }}</span>
                        </td>

                        <td class="py-3 px-4 max-w-xs">
                            @if($mov->reference)
                                <div class="font-semibold text-slate-800 text-[11px] truncate" title="{{ $mov->reference }}">
                                    Ref: {{ $mov->reference }}
                                </div>
                            @endif
                            @if($mov->reason)
                                <div class="text-[10px] text-slate-500 truncate" title="{{ $mov->reason }}">
                                    {{ $mov->reason }}
                                </div>
                            @endif
                            @if(!$mov->reference && !$mov->reason)
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-slate-600 whitespace-nowrap text-[11px]">
                            {{ $mov->user?->name ?? 'Sistema' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            No hay movimientos registrados con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
