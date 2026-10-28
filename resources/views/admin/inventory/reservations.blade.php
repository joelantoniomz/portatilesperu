@extends('layouts.admin')

@section('title', 'Control de Reservas de Stock')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold mb-2">
                <span>Gestión de Bloqueos Preventivos</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Reservas de Stock Activas</h2>
            <p class="text-slate-500 text-sm mt-1">
                Laptops bloqueadas temporalmente durante proceso de compra o cotización. Previene sobreventa.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.inventory.index') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver a Inventario</span>
            </a>
        </div>
    </div>

    <!-- Filtros de Estado -->
    <div class="card-surface p-4 border border-slate-100 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.inventory.reservations') }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-colors {{ !request('status') ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Todas
        </a>
        <a href="{{ route('admin.inventory.reservations', ['status' => 'pending']) }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-colors {{ request('status') === 'pending' ? 'bg-amber-500 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100' }}">
            Pendientes
        </a>
        <a href="{{ route('admin.inventory.reservations', ['status' => 'confirmed']) }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-colors {{ request('status') === 'confirmed' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
            Confirmadas
        </a>
        <a href="{{ route('admin.inventory.reservations', ['status' => 'dispatched']) }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-colors {{ request('status') === 'dispatched' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
            Despachadas
        </a>
        <a href="{{ route('admin.inventory.reservations', ['status' => 'cancelled']) }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-colors {{ request('status') === 'cancelled' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
            Canceladas
        </a>
        <a href="{{ route('admin.inventory.reservations', ['status' => 'expired']) }}" class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-colors {{ request('status') === 'expired' ? 'bg-slate-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            Expiradas
        </a>
    </div>

    <!-- Tabla de Reservas -->
    <div class="card-surface border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-400 uppercase font-black tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4"># Reserva</th>
                        <th class="py-3.5 px-4">Producto & SKU</th>
                        <th class="py-3.5 px-4">Almacén</th>
                        <th class="py-3.5 px-4 text-center">Cant.</th>
                        <th class="py-3.5 px-4 text-center">Estado</th>
                        <th class="py-3.5 px-4">Referencia / Vence</th>
                        <th class="py-3.5 px-4">Usuario</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($reservations as $res)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">
                            #{{ $res->id }}
                        </td>

                        <td class="py-3 px-4">
                            <a href="{{ route('admin.inventory.product', $res->product_id) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors">
                                {{ $res->product->name }}
                            </a>
                            <div class="text-[10px] font-mono text-slate-400">{{ $res->product->sku }}</div>
                        </td>

                        <td class="py-3 px-4 whitespace-nowrap">
                            <span class="font-semibold text-slate-800">{{ $res->warehouse->name }}</span>
                            <span class="text-[10px] text-slate-400 block font-mono">{{ $res->warehouse->code }}</span>
                        </td>

                        <td class="py-3 px-4 text-center font-bold text-slate-900 text-sm">
                            {{ $res->quantity }}
                        </td>

                        <td class="py-3 px-4 text-center whitespace-nowrap">
                            @php
                                $statusBadges = [
                                    'pending'    => 'bg-amber-100 text-amber-800 border-amber-200',
                                    'confirmed'  => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'dispatched' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'cancelled'  => 'bg-rose-100 text-rose-800 border-rose-200',
                                    'expired'    => 'bg-slate-100 text-slate-700 border-slate-200',
                                ];
                                $statusLabels = [
                                    'pending'    => 'Pendiente',
                                    'confirmed'  => 'Confirmada',
                                    'dispatched' => 'Despachada',
                                    'cancelled'  => 'Cancelada',
                                    'expired'    => 'Expirada',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black border {{ $statusBadges[$res->status] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $statusLabels[$res->status] ?? ucfirst($res->status) }}
                            </span>
                        </td>

                        <td class="py-3 px-4">
                            <div class="font-semibold text-slate-800 text-[11px]">{{ $res->reference_type }}: {{ $res->reference_id ?? 'N/A' }}</div>
                            @if($res->expires_at)
                                <div class="text-[10px] text-slate-500 {{ $res->expires_at->isPast() ? 'text-rose-500 font-bold' : '' }}">
                                    Vence: {{ $res->expires_at->format('d/m/Y H:i') }}
                                </div>
                            @endif
                        </td>

                        <td class="py-3 px-4 text-slate-600 text-[11px] whitespace-nowrap">
                            {{ $res->user?->name ?? 'Cliente / Web' }}
                        </td>

                        <td class="py-3 px-4 text-right">
                            @if(in_array($res->status, ['pending', 'confirmed']) && auth()->user()->hasPermission('inventory.manage'))
                            <form method="POST" action="{{ route('admin.inventory.reservations.cancel', $res->id) }}" onsubmit="return confirm('¿Confirma que desea cancelar esta reserva y liberar el stock?');" class="inline-flex items-center gap-1">
                                @csrf
                                <input type="hidden" name="reason" value="Cancelado manualmente por el administrador">
                                <button type="submit" class="px-2.5 py-1 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-[11px] font-bold transition-colors">
                                    Liberar Stock
                                </button>
                            </form>
                            @else
                            <span class="text-slate-400 text-[11px]">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            No se encontraron reservas con el filtro seleccionado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reservations->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50/50">
            {{ $reservations->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
