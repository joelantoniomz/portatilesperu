@extends('layouts.admin')

@section('title', 'Gestión de Almacenes y Sedes')

@section('content')
<div class="space-y-6">

    <!-- Encabezado y Acción -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Configuración de Sedes Físicas</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Almacenes y Tiendas</h2>
            <p class="text-slate-500 text-sm mt-1">
                Administre los puntos de venta, almacén central, locales en distritos y stock en tránsito.
            </p>
        </div>

        @if(auth()->user()->hasPermission('inventory.manage'))
        <a href="{{ route('admin.warehouses.create') }}" class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nuevo Almacén</span>
        </a>
        @endif
    </div>

    <!-- Grid de Almacenes -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($warehouses as $wh)
        <div class="card-surface p-6 border border-slate-100 shadow-sm flex flex-col justify-between hover:border-emerald-500/40 transition-all">
            <div class="space-y-3">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-slate-100 text-slate-700">
                            {{ $wh->code }}
                        </span>
                        <h3 class="text-lg font-black text-slate-900 mt-1">
                            <a href="{{ route('admin.warehouses.show', $wh) }}" class="hover:text-emerald-600 transition-colors">
                                {{ $wh->name }}
                            </a>
                        </h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $wh->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                        {{ $wh->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <div class="text-xs text-slate-500 space-y-1">
                    @if($wh->address)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate">{{ $wh->address }} {{ $wh->city ? "({$wh->city})" : '' }}</span>
                        </div>
                    @endif
                    @if($wh->responsible_name)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Resp: {{ $wh->responsible_name }}</span>
                        </div>
                    @endif
                    @if($wh->phone)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ $wh->phone }}</span>
                        </div>
                    @endif
                </div>

                <!-- Métricas del Almacén -->
                <div class="grid grid-cols-2 gap-2 pt-3 border-t border-slate-100 text-center">
                    <div class="p-2 rounded-xl bg-slate-50">
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Productos</span>
                        <span class="text-sm font-black text-slate-800">{{ $wh->inventory_balances_count ?? 0 }}</span>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50">
                        <span class="text-[10px] text-slate-400 block font-semibold uppercase">Movimientos</span>
                        <span class="text-sm font-black text-slate-800">{{ $wh->stock_movements_count ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 mt-3 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('admin.warehouses.show', $wh) }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                    <span>Ver Existencias</span>
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                @if(auth()->user()->hasPermission('inventory.manage'))
                <div class="flex items-center gap-1.5">
                    <a href="{{ route('admin.warehouses.edit', $wh) }}" title="Editar almacén" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                    <form action="{{ route('admin.warehouses.destroy', $wh) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que desea eliminar el almacén {{ $wh->name }} ({{ $wh->code }})? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Eliminar almacén" class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-3 card-surface p-12 text-center text-slate-400">
            No se han registrado almacenes aún.
        </div>
        @endforelse
    </div>

    @if($warehouses->hasPages())
    <div class="p-4 border-t border-slate-100">
        {{ $warehouses->links() }}
    </div>
    @endif

</div>
@endsection
