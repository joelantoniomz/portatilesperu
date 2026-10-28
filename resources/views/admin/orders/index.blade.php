@extends('layouts.admin')

@section('title', 'Gestión de Pedidos & WhatsApp - Portátiles Perú')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Fase 6</span>
                <h1 class="text-2xl font-black text-white tracking-tight">Pedidos & Coordinación WhatsApp</h1>
            </div>
            <p class="text-xs text-emerald-100/70 mt-1">
                Monitorea solicitudes de compra registradas con código de referencia, reservas de stock preventivas y estados de despacho.
            </p>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-white border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3 shadow-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Tarjetas de Métricas Rápidas (KPIs Blancas) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="card-surface rounded-2xl p-4 border border-slate-100 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-500 block">Total Pedidos</span>
            <span class="text-2xl font-black text-slate-900 font-mono mt-1 block">{{ $stats['total'] }}</span>
        </div>
        <div class="card-surface rounded-2xl p-4 border border-amber-100 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-amber-600 block">Pendientes</span>
            <span class="text-2xl font-black text-amber-600 font-mono mt-1 block">{{ $stats['pending'] }}</span>
        </div>
        <div class="card-surface rounded-2xl p-4 border border-sky-100 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-sky-600 block">Confirmados</span>
            <span class="text-2xl font-black text-sky-600 font-mono mt-1 block">{{ $stats['confirmed'] }}</span>
        </div>
        <div class="card-surface rounded-2xl p-4 border border-purple-100 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-purple-600 block">En Preparación</span>
            <span class="text-2xl font-black text-purple-600 font-mono mt-1 block">{{ $stats['in_preparation'] }}</span>
        </div>
        <div class="card-surface rounded-2xl p-4 border border-emerald-100 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-emerald-600 block">Completados</span>
            <span class="text-2xl font-black text-emerald-600 font-mono mt-1 block">{{ $stats['completed'] }}</span>
        </div>
        <div class="card-surface rounded-2xl p-4 border border-emerald-100 shadow-sm col-span-2 sm:col-span-1">
            <span class="text-[10px] uppercase font-bold text-emerald-700 block">Ventas Totales</span>
            <span class="text-xl font-black text-emerald-600 font-mono mt-1 block">S/ {{ number_format($stats['total_sales'], 2) }}</span>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda (Tarjeta Blanca) -->
    <div class="card-surface rounded-3xl p-5 border border-slate-100 shadow-sm">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Buscar -->
            <div class="lg:col-span-2">
                <label for="search" class="text-[11px] font-bold text-slate-700 block mb-1">Buscar</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="N° Pedido (PED-...), cliente, teléfono o email..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all"
                >
            </div>

            <!-- Estado -->
            <div>
                <label for="status" class="text-[11px] font-bold text-slate-700 block mb-1">Estado</label>
                <select
                    id="status"
                    name="status"
                    class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all font-medium"
                >
                    <option value="">Todos los Estados</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente (Reserva Activa)</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmado (Pago OK)</option>
                    <option value="in_preparation" {{ request('status') === 'in_preparation' ? 'selected' : '' }}>En Preparación</option>
                    <option value="dispatched" {{ request('status') === 'dispatched' ? 'selected' : '' }}>Despachado</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completado</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>

            <!-- Almacén -->
            <div>
                <label for="warehouse_id" class="text-[11px] font-bold text-slate-700 block mb-1">Almacén</label>
                <select
                    id="warehouse_id"
                    name="warehouse_id"
                    class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all font-medium"
                >
                    <option value="">Todas las Sedes</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Botones -->
            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-sm hover:shadow transition-all flex items-center justify-center gap-1.5"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Filtrar</span>
                </button>
                @if(request()->anyFilled(['search', 'status', 'warehouse_id', 'date_from', 'date_to']))
                <a
                    href="{{ route('admin.orders.index') }}"
                    class="p-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 border border-slate-200 text-xs transition-colors"
                    title="Limpiar filtros"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla Principal de Pedidos (Superficie Blanca) -->
    <div class="card-surface rounded-3xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-5">Pedido / Fecha</th>
                        <th class="py-3.5 px-5">Cliente & Contacto</th>
                        <th class="py-3.5 px-5">Almacén Despacho</th>
                        <th class="py-3.5 px-5">Laptops Solicitadas</th>
                        <th class="py-3.5 px-5 text-right">Total</th>
                        <th class="py-3.5 px-5 text-center">Estado</th>
                        <th class="py-3.5 px-5 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $ord)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Pedido / Fecha -->
                        <td class="py-4 px-5">
                            <a href="{{ route('admin.orders.show', $ord) }}" class="font-mono font-bold text-emerald-700 hover:text-emerald-600 hover:underline block text-xs">
                                {{ $ord->order_number }}
                            </a>
                            <span class="text-[11px] text-slate-400 block mt-0.5">
                                {{ $ord->created_at->format('d/m/Y H:i') }}
                            </span>
                        </td>

                        <!-- Cliente -->
                        <td class="py-4 px-5">
                            <span class="font-bold text-slate-900 block">{{ $ord->customer_name }}</span>
                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                <span>{{ $ord->customer_phone }}</span>
                                @php
                                    $cleanPhone = preg_replace('/[^0-9]/', '', $ord->customer_phone);
                                    if (!str_starts_with($cleanPhone, '51') && strlen($cleanPhone) === 9) {
                                        $cleanPhone = '51' . $cleanPhone;
                                    }
                                @endphp
                                <a
                                    href="https://wa.me/{{ $cleanPhone }}"
                                    target="_blank"
                                    class="text-emerald-600 hover:text-emerald-700"
                                    title="Abrir chat directo con cliente"
                                >
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                    </svg>
                                </a>
                            </div>
                            <span class="text-[11px] text-slate-400 block">{{ $ord->customer_city }}</span>
                        </td>

                        <!-- Almacén -->
                        <td class="py-4 px-5 text-slate-700 font-medium">
                            {{ $ord->warehouse?->name ?? 'Central' }}
                        </td>

                        <!-- Laptops -->
                        <td class="py-4 px-5">
                            <span class="font-bold text-slate-900 block">{{ $ord->items->count() }} ítem(s)</span>
                            <span class="text-[11px] text-slate-500 truncate max-w-xs block">
                                {{ $ord->items->pluck('product_name')->implode(', ') }}
                            </span>
                        </td>

                        <!-- Total -->
                        <td class="py-4 px-5 text-right">
                            <span class="font-mono font-black text-sm text-slate-900">
                                {{ $ord->formatted_total }}
                            </span>
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">{{ $ord->invoice_type }}</span>
                        </td>

                        <!-- Estado -->
                        <td class="py-4 px-5 text-center">
                            @php
                                $statusStyles = [
                                    'pending'        => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'confirmed'      => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'in_preparation' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'dispatched'     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'completed'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelled'      => 'bg-rose-50 text-rose-700 border-rose-200',
                                ];
                                $statusLabels = [
                                    'pending'        => 'Pendiente',
                                    'confirmed'      => 'Confirmado',
                                    'in_preparation' => 'En Preparación',
                                    'dispatched'     => 'Despachado',
                                    'completed'      => 'Completado',
                                    'cancelled'      => 'Cancelado',
                                ];
                            @endphp
                            <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-black uppercase border {{ $statusStyles[$ord->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                {{ $statusLabels[$ord->status] ?? $ord->status }}
                            </span>
                        </td>

                        <!-- Acciones -->
                        <td class="py-4 px-5 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a
                                    href="{{ route('admin.orders.show', $ord) }}"
                                    class="p-2 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 hover:border-emerald-200 transition-colors"
                                    title="Ver Ficha y Cambiar Estado"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                <a
                                    href="{{ $ord->whatsapp_url }}"
                                    target="_blank"
                                    class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-500 text-emerald-700 hover:text-slate-950 border border-emerald-200 transition-all"
                                    title="Abrir WhatsApp oficial del pedido"
                                >
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                            No se encontraron pedidos registrados con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
