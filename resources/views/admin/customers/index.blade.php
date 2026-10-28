@extends('layouts.admin')

@section('title', 'Clientes - Portátiles Perú')

@section('content')
<div class="space-y-6">

    <!-- Encabezado y Nuevo Cliente -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between pb-2">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Fase 7</span>
                <h1 class="text-2xl font-black text-white tracking-tight">Clientes</h1>
            </div>
            <p class="text-xs text-emerald-100/70 mt-1">Perfiles, historial comercial y seguimiento de clientes.</p>
        </div>

        @if(auth()->user()->hasPermission('customers.manage'))
        <form method="POST" action="{{ route('admin.customers.store') }}" class="card-surface grid grid-cols-1 sm:grid-cols-4 gap-2 rounded-2xl border border-slate-100 p-3 shadow-sm">
            @csrf
            <input name="name" required placeholder="Nombre *" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all">
            <input name="phone" placeholder="Teléfono" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all">
            <input name="email" type="email" placeholder="Correo" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all">
            <button class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2 text-xs font-black text-slate-950 shadow-sm transition-all">
                Nuevo cliente
            </button>
        </form>
        @endif
    </div>

    <!-- KPIs Blancas -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach(['total'=>['Total', 'slate'], 'active'=>['Activos', 'emerald'], 'recurrent'=>['Recurrentes', 'sky'], 'inactive'=>['Inactivos', 'amber']] as $key => [$label, $color])
        <div class="card-surface rounded-2xl border border-slate-100 p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">{{ $label }}</span>
            <p class="mt-1 text-2xl font-black text-slate-900 font-mono">{{ $stats[$key] }}</p>
        </div>
        @endforeach
    </div>

    <!-- Barra de Filtros (Tarjeta Blanca) -->
    <form class="card-surface grid gap-3 rounded-3xl border border-slate-100 p-4 sm:grid-cols-4 shadow-sm" method="GET" action="{{ route('admin.customers.index') }}">
        <input
            name="search"
            value="{{ request('search') }}"
            placeholder="Nombre, teléfono, correo o documento..."
            class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all sm:col-span-2"
        >
        <select
            name="status"
            class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all font-medium"
        >
            <option value="">Todos los estados</option>
            @foreach(['active'=>'Activo','inactive'=>'Inactivo','blocked'=>'Bloqueado'] as $value => $label)
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

    <!-- Tabla Principal de Clientes (Tarjeta Blanca) -->
    <div class="card-surface rounded-3xl border border-slate-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-[11px] uppercase font-bold tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                        <th class="py-3.5 px-5">Cliente</th>
                        <th class="py-3.5 px-5">Contacto</th>
                        <th class="py-3.5 px-5">Compras</th>
                        <th class="py-3.5 px-5">Total</th>
                        <th class="py-3.5 px-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-4 px-5 font-bold text-slate-900">
                            {{ $customer->name }}
                            <span class="mt-0.5 block text-[11px] font-normal text-slate-400">{{ $customer->city ?: 'Sin ciudad' }}</span>
                        </td>
                        <td class="py-4 px-5 text-slate-700">
                            {{ $customer->phone ?: '—' }}
                            <span class="mt-0.5 block text-[11px] text-slate-400">{{ $customer->email ?: '' }}</span>
                        </td>
                        <td class="py-4 px-5 font-mono text-slate-800 font-semibold">{{ $customer->orders_count }}</td>
                        <td class="py-4 px-5 font-mono font-bold text-slate-900">{{ $customer->formatted_total_spent }}</td>
                        <td class="py-4 px-5 text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 border border-slate-200 hover:border-emerald-200 font-bold transition-all">
                                Ver Ficha
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-slate-400 font-medium">
                            Aún no hay clientes registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-100 bg-slate-50">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
