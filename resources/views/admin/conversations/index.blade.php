@extends('layouts.admin')

@section('title', 'Bandeja WhatsApp - Portátiles Perú')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Fase 7 &bull; Modo Enlace</span>
                <h1 class="text-2xl font-black text-white tracking-tight">Bandeja de conversaciones</h1>
            </div>
            <p class="text-xs text-emerald-100/70 mt-1">
                Gestiona la atención comercial de WhatsApp; el modo enlace abre chats directos oficiales con el cliente.
            </p>
        </div>
    </div>

    <!-- KPIs Blancas -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach(['total'=>['Total', 'slate'], 'open'=>['Abiertas', 'sky'], 'in_progress'=>['En atención', 'amber'], 'resolved'=>['Resueltas', 'emerald']] as $key => [$label, $color])
        <div class="card-surface rounded-2xl border border-slate-100 p-4 shadow-sm">
            <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">{{ $label }}</span>
            <p class="mt-1 text-2xl font-black text-slate-900 font-mono">{{ $stats[$key] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Nueva Conversación (Tarjeta Blanca) -->
    @if(auth()->user()->hasPermission('whatsapp.manage'))
    <div class="card-surface rounded-3xl border border-slate-100 p-5 shadow-sm space-y-3">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 block">Iniciar o Registrar Conversación</span>
        <form action="{{ route('admin.conversations.store') }}" method="POST" class="grid gap-3 sm:grid-cols-4">
            @csrf
            <input
                name="customer_name"
                required
                placeholder="Nombre del cliente *"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all"
            >
            <input
                name="customer_phone"
                required
                placeholder="Teléfono WhatsApp (ej. 987654321) *"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all"
            >
            <input
                name="order_reference"
                placeholder="N° Pedido (opcional)"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all"
            >
            <button class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-xs font-black text-slate-950 shadow-sm transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nueva conversación</span>
            </button>
        </form>
    </div>
    @endif

    <!-- Barra de Filtros (Tarjeta Blanca) -->
    <form class="card-surface grid gap-3 rounded-3xl border border-slate-100 p-4 sm:grid-cols-4 shadow-sm" method="GET" action="{{ route('admin.conversations.index') }}">
        <input
            name="search"
            value="{{ request('search') }}"
            placeholder="Cliente, teléfono o pedido..."
            class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all sm:col-span-2"
        >
        <select
            name="status"
            class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all font-medium"
        >
            <option value="">Todos los estados</option>
            @foreach(['open'=>'Abierta','in_progress'=>'En atención','resolved'=>'Resuelta','archived'=>'Archivada'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2.5 text-xs font-black text-slate-950 shadow-sm transition-all flex items-center justify-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <span>Filtrar</span>
        </button>
    </form>

    <!-- Lista de Conversaciones (Tarjetas Blancas) -->
    <div class="space-y-2.5">
        @forelse($conversations as $conversation)
        <a
            href="{{ route('admin.conversations.show', $conversation) }}"
            class="card-surface block rounded-2xl border border-slate-100 p-4 shadow-sm hover:border-emerald-300 hover:shadow transition-all"
        >
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="font-bold text-slate-900 text-sm">{{ $conversation->customer_name ?: 'Cliente sin nombre' }}</p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        <span class="font-mono text-slate-700 font-semibold">{{ $conversation->order_reference ?: $conversation->customer_phone }}</span>
                        &bull;
                        <span>{{ $conversation->last_message_preview ?: 'Sin actividad registrada' }}</span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="rounded-full bg-slate-100 border border-slate-200 px-3 py-1 text-[11px] font-bold text-slate-700">
                        {{ $conversation->status_label }}
                    </span>
                    <span class="text-xs font-bold text-emerald-600 hover:underline">Ver chat &rarr;</span>
                </div>
            </div>
        </a>
        @empty
        <div class="card-surface rounded-3xl border border-slate-100 p-12 text-center text-xs text-slate-400 shadow-sm font-medium">
            Aún no hay conversaciones. Las solicitudes nuevas o pedidos de compra crean una automáticamente en modo enlace.
        </div>
        @endforelse
    </div>

    @if($conversations->hasPages())
    <div class="card-surface rounded-2xl p-4 border border-slate-100 shadow-sm">
        {{ $conversations->links() }}
    </div>
    @endif

</div>
@endsection
