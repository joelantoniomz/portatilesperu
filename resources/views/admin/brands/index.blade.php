@extends('layouts.admin')

@section('title', 'Marcas y Modelos')

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Fase 2: Fabricantes y Líneas</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Marcas y Líneas de Modelos</h2>
            <p class="text-slate-500 text-sm mt-1">
                Clasificación por fabricante (Lenovo, ASUS, HP, Dell, Apple, MSI) y familias de producto con opción de alta y baja.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Formulario Crear Marca -->
        @if(auth()->user()->hasPermission('catalog.manage'))
        <div class="lg:col-span-4">
            <div class="card-surface p-6 border border-slate-100 space-y-4 shadow-sm sticky top-4">
                <h3 class="text-base font-extrabold text-slate-900">Registrar Nueva Marca</h3>
                <form action="{{ route('admin.brands.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre de la Marca *</label>
                        <input type="text" name="name" required placeholder="Ej: Acer, Razer, Gigabyte" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">URL de Logo (Opcional)</label>
                        <input type="url" name="logo_url" placeholder="https://..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-2xl bg-[#0c3823] hover:bg-emerald-800 text-white font-bold text-xs shadow-md shadow-emerald-950/20 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Registrar Marca</span>
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Listado de Marcas y Modelos -->
        <div class="{{ auth()->user()->hasPermission('catalog.manage') ? 'lg:col-span-8' : 'lg:col-span-12' }} space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($brands as $brand)
                <div class="card-surface p-5 border border-slate-100 space-y-3 shadow-sm hover:border-emerald-500/30 transition-all">
                    <!-- Cabecera de Marca con Nombre, Conteo y Botón Eliminar -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2.5">
                            @if($brand->logo_url)
                            <img src="{{ $brand->logo_url }}" alt="{{ $brand->name }}" class="w-6 h-6 object-contain">
                            @endif
                            <h4 class="text-base font-extrabold text-slate-900">{{ $brand->name }}</h4>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                {{ $brand->products_count }} laptops
                            </span>
                            @if(auth()->user()->hasPermission('catalog.manage'))
                            <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('¿Seguro que desea eliminar la marca {{ $brand->name }}? Esta acción no se puede deshacer.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Eliminar marca" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Lista de Modelos con Opción de Eliminar cada Modelo -->
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Modelos:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($brand->models as $mod)
                            <span class="inline-flex items-center gap-1.5 pl-2.5 pr-1.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-100">
                                <span>{{ $mod->name }}</span>
                                @if(auth()->user()->hasPermission('catalog.manage'))
                                <form action="{{ route('admin.brands.models.destroy', [$brand, $mod]) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar el modelo {{ $mod->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar modelo" class="w-4 h-4 rounded hover:bg-rose-200 text-rose-600 flex items-center justify-center transition-colors text-[10px] font-bold">
                                        ✕
                                    </button>
                                </form>
                                @endif
                            </span>
                            @empty
                            <span class="text-xs text-slate-400 italic">Sin modelos asignados.</span>
                            @endforelse
                        </div>
                    </div>

                    @if(auth()->user()->hasPermission('catalog.manage'))
                    <!-- Agregar Modelo Rápido -->
                    <form action="{{ route('admin.brands.storeModel', $brand) }}" method="POST" class="pt-2 border-t border-slate-100 flex items-center gap-2">
                        @csrf
                        <input type="text" name="name" required placeholder="Nuevo modelo..." class="flex-1 px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-slate-50 text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none">
                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs transition-colors">
                            + Agregar
                        </button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
