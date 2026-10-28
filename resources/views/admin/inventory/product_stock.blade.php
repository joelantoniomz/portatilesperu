@extends('layouts.admin')

@section('title', 'Kardex y Stock: ' . $product->name)

@section('content')
<div class="space-y-6">

    <!-- Encabezado con Volver y Acción QR -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.inventory.index') }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $product->sku }}</span>
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">{{ ucfirst($product->status) }}</span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $product->name }}</h2>
                <p class="text-slate-500 text-xs mt-0.5">
                    Marca: <strong>{{ $product->brand?->name ?? 'N/A' }}</strong> &bull; Precio: <strong>{{ $product->formatted_price }}</strong>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.qr.label', $product->id) }}" class="px-4 py-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Imprimir Etiqueta QR</span>
            </a>
            <a href="{{ route('admin.products.edit', $product->id) }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Editar Laptop
            </a>
        </div>
    </div>

    <!-- Indicadores de Stock Global de este producto -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Físico Total</span>
            <p class="text-2xl font-black text-slate-900 mt-0.5">{{ $globalStock['total_physical'] }} <span class="text-xs font-normal text-slate-400">unid.</span></p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Reservado Total</span>
            <p class="text-2xl font-black text-amber-600 mt-0.5">{{ $globalStock['total_reserved'] }} <span class="text-xs font-normal text-slate-400">unid.</span></p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Disponible Venta</span>
            <p class="text-2xl font-black text-emerald-600 mt-0.5">{{ $globalStock['total_available'] }} <span class="text-xs font-normal text-slate-400">unid.</span></p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Almacenes c/ Stock</span>
            <p class="text-2xl font-black text-slate-900 mt-0.5">{{ count($balances) }}</p>
        </div>
    </div>

    <!-- Distribución de Stock por Almacén -->
    <div class="card-surface border border-slate-100 p-6">
        <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4">Stock Actual por Almacén</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @forelse($balances as $b)
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ $b->warehouse->name }}</h4>
                        <span class="font-mono text-xs text-slate-400">{{ $b->warehouse->code }}</span>
                    </div>
                    @if($b->qty_available <= 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-700">Agotado</span>
                    @elseif($b->is_low_stock)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-700">Bajo Stock</span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-700">OK</span>
                    @endif
                </div>

                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-200 text-center">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-semibold">Físico</span>
                        <span class="text-base font-black text-slate-800">{{ $b->qty_physical }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-amber-600 block font-semibold">Reservado</span>
                        <span class="text-base font-black text-amber-600">{{ $b->qty_reserved }}</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-emerald-600 block font-semibold">Disponible</span>
                        <span class="text-base font-black text-emerald-600">{{ $b->qty_available }}</span>
                    </div>
                </div>

                @if(auth()->user()->hasPermission('inventory.manage'))
                <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-200/60">
                    <a href="{{ route('admin.inventory.entry', ['product_id' => $product->id, 'warehouse_id' => $b->warehouse_id]) }}" class="text-[11px] font-bold text-emerald-600 hover:underline">
                        + Entrada
                    </a>
                    <span class="text-slate-300">&bull;</span>
                    <a href="{{ route('admin.inventory.adjustment', ['product_id' => $product->id, 'warehouse_id' => $b->warehouse_id]) }}" class="text-[11px] font-bold text-amber-600 hover:underline">
                        Ajustar
                    </a>
                </div>
                @endif
            </div>
            @empty
            <div class="col-span-3 text-center py-6 text-slate-400 text-xs">
                No hay saldos registrados para este producto en ningún almacén.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Historial Kardex de este Producto -->
    <div class="card-surface border border-slate-100 overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Kardex de Movimientos de esta Laptop</h3>
            <span class="text-xs text-slate-400 font-medium">Orden cronológico inmutable</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">Fecha & Hora</th>
                        <th class="py-3 px-4">Almacén</th>
                        <th class="py-3 px-4 text-center">Tipo</th>
                        <th class="py-3 px-4 text-center">Cant.</th>
                        <th class="py-3 px-4 text-center">Saldo Antes → Después</th>
                        <th class="py-3 px-4">Referencia / Motivo</th>
                        <th class="py-3 px-4">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($movements as $mov)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            {{ $mov->moved_at ? $mov->moved_at->format('d/m/Y H:i') : $mov->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-slate-800 whitespace-nowrap">
                            {{ $mov->warehouse->name }}
                        </td>
                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black bg-slate-100 text-slate-700">
                                {{ ucfirst($mov->type) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-bold text-sm whitespace-nowrap">
                            @if(in_array($mov->type, ['entry', 'transfer_in', 'return', 'release']))
                                <span class="text-emerald-600">+{{ $mov->quantity }}</span>
                            @else
                                <span class="text-rose-600">-{{ $mov->quantity }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-500 whitespace-nowrap">
                            {{ $mov->balance_before }} → <span class="font-bold text-slate-800">{{ $mov->balance_after }}</span>
                        </td>
                        <td class="py-3 px-4 max-w-xs">
                            @if($mov->reference)
                                <div class="font-semibold text-slate-800 text-[11px]">{{ $mov->reference }}</div>
                            @endif
                            @if($mov->reason)
                                <div class="text-[10px] text-slate-500">{{ $mov->reason }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-slate-600 text-[11px] whitespace-nowrap">
                            {{ $mov->user?->name ?? 'Sistema' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            No hay movimientos registrados para este producto.
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
