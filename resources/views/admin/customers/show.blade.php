@extends('layouts.admin')

@section('title', $customer->name . ' - Clientes')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-300 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>Volver a Clientes</span>
        </a>
    </div>

    <!-- Perfil del Cliente (Tarjeta Blanca) -->
    <div class="card-surface rounded-3xl border border-slate-100 p-6 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900">{{ $customer->name }}</h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    {{ $customer->phone ?: 'Sin teléfono' }} &bull; {{ $customer->email ?: 'Sin correo electrónico' }} &bull; {{ $customer->city ?: 'Ciudad no especificada' }}
                </p>
            </div>
            @if($customer->phone)
            <a
                target="_blank"
                rel="noopener"
                href="{{ $customer->whatsapp_url }}"
                class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-xs font-black text-slate-950 shadow-sm transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Abrir WhatsApp</span>
            </a>
            @endif
        </div>

        <div class="mt-6 grid grid-cols-3 gap-3 text-center border-t border-slate-100 pt-5">
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                <p class="text-2xl font-black text-slate-900 font-mono">{{ $customer->orders_count }}</p>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5 block">Compras</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                <p class="text-2xl font-black text-emerald-700 font-mono">{{ $customer->formatted_total_spent }}</p>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5 block">Total Acumulado</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200">
                <p class="text-xl font-black text-slate-800 font-mono">{{ $customer->last_purchase_at?->format('d/m/Y') ?? '—' }}</p>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-0.5 block">Última compra</span>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasPermission('customers.manage'))
    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Formulario Nota Interna -->
        <form method="POST" action="{{ route('admin.customers.notes.store', $customer) }}" class="card-surface rounded-3xl border border-slate-100 p-6 shadow-sm space-y-3">
            @csrf
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Nueva Nota Interna</h2>
            <textarea
                name="note"
                required
                class="w-full rounded-2xl border border-slate-200 bg-slate-50 p-3.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all"
                rows="3"
                placeholder="Seguimiento, preferencia de modelo, acuerdo comercial..."
            ></textarea>
            <div class="flex items-center gap-2">
                <select name="type" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white">
                    <option value="general">General</option>
                    <option value="followup">Seguimiento</option>
                    <option value="sale">Venta</option>
                    <option value="complaint">Incidencia</option>
                </select>
                <button class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2 text-xs font-black text-slate-950 shadow-sm transition-all">
                    Guardar Nota
                </button>
            </div>
        </form>

        <!-- Estado del Cliente -->
        <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="card-surface rounded-3xl border border-slate-100 p-6 shadow-sm space-y-3">
            @csrf
            @method('PATCH')
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Estado de Cuenta</h2>
            <p class="text-xs text-slate-500">Actualiza la condición comercial de este cliente.</p>
            <div class="flex items-center gap-2 pt-2">
                <select name="status" class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white">
                    @foreach(['active'=>'Activo','inactive'=>'Inactivo','blocked'=>'Bloqueado'] as $value => $label)
                        <option value="{{ $value }}" @selected($customer->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-2.5 text-xs font-black text-white shadow-sm transition-all">
                    Actualizar
                </button>
            </div>
        </form>
    </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <!-- Historial de Pedidos -->
        <div class="card-surface rounded-3xl border border-slate-100 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Historial de Pedidos</h2>
            <div class="space-y-2">
                @forelse($customer->orders as $order)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono font-bold text-slate-900 hover:text-emerald-700 hover:underline">
                                {{ $order->order_number }}
                            </a>
                            @if($order->status === 'pending')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                    Pendiente de pago
                                </span>
                            @elseif(in_array($order->status, ['confirmed', 'completed']))
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    ✓ Pagado
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                    {{ ucfirst($order->status) }}
                                </span>
                            @endif
                        </div>
                        <span class="text-[10px] text-slate-400 block mt-0.5">
                            {{ $order->created_at->format('d/m/Y H:i') }} &bull; {{ $order->items_count ?? $order->items->count() }} ítem(s) &bull; <strong class="text-slate-800 font-mono">{{ $order->formatted_total }}</strong>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($order->status === 'pending')
                        <form action="{{ route('admin.orders.confirmPayment', $order) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirmar que el pago de este pedido fue verificado?');">
                            @csrf
                            <button
                                type="submit"
                                class="px-3 py-1.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-slate-950 font-black text-[11px] shadow-sm transition-all flex items-center gap-1"
                                title="Marcar como Pago Confirmado"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                <span>Confirmar Pago</span>
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('admin.orders.show', $order) }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-200 text-slate-700 font-bold text-[11px] transition-colors">
                            Ver
                        </a>
                    </div>
                </div>
                @empty
                <p class="py-6 text-center text-xs text-slate-400 font-medium">Sin pedidos registrados todavía.</p>
                @endforelse
            </div>
        </div>

        <!-- Notas Internas -->
        <div class="card-surface rounded-3xl border border-slate-100 p-6 shadow-sm space-y-4">
            <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider">Notas y Bitácora</h2>
            <div class="space-y-2">
                @forelse($customer->notes as $note)
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 space-y-1">
                    <p class="leading-relaxed">{{ $note->note }}</p>
                    <span class="block text-[10px] text-slate-400 font-medium">
                        {{ $note->user?->name ?? 'Sistema' }} &bull; {{ $note->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                @empty
                <p class="py-6 text-center text-xs text-slate-400 font-medium">Sin notas internas registradas.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
