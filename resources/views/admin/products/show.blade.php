@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="space-y-6">

    <!-- Encabezado con Acciones Rápidas -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase font-mono bg-slate-100 text-slate-800 border border-slate-200">
                    SKU: {{ $product->sku }}
                </span>

                @if($product->status === 'published')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Publicado en Catálogo</span>
                    </span>
                @elseif($product->status === 'draft')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>Borrador</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        <span>Archivado</span>
                    </span>
                @endif
            </div>

            <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $product->name }}</h2>
            <p class="text-slate-500 text-xs mt-1">
                {{ $product->category->name }} &bull; {{ $product->brand->name }} @if($product->model) &bull; {{ $product->model->name }} @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if(auth()->user()->hasPermission('catalog.manage'))
            <!-- Selector Rápido de Estado -->
            <form action="{{ route('admin.products.toggleStatus', $product) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <select name="status" onchange="this.form.submit()" class="px-3.5 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="published" {{ $product->status === 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="draft" {{ $product->status === 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="archived" {{ $product->status === 'archived' ? 'selected' : '' }}>Archivado</option>
                </select>
            </form>

            <a href="{{ route('admin.products.edit', $product) }}" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>Editar</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('inventory.view'))
            <a href="{{ route('admin.inventory.product', $product) }}" class="px-4 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span>Stock & Kardex</span>
            </a>

            <a href="{{ route('admin.qr.label', $product) }}" target="_blank" class="px-3.5 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs transition-colors flex items-center gap-1.5" title="Etiqueta QR">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>QR</span>
            </a>
            @endif

            <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Contenido en 2 Columnas: Galería y Datos Comerciales -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Columna Izquierda: Galería de Fotos -->
        <div class="lg:col-span-5 space-y-4">
            <div class="card-surface p-4 border border-slate-100">
                <div class="aspect-video w-full rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center">
                    <img id="mainImage" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>

                @if($product->images->count() > 1)
                <div class="grid grid-cols-4 gap-2 mt-3">
                    @foreach($product->images as $img)
                    <button type="button" onclick="document.getElementById('mainImage').src = '{{ $img->url }}'" class="aspect-video rounded-xl overflow-hidden border-2 border-slate-200 hover:border-emerald-500 transition-colors focus:border-emerald-600">
                        <img src="{{ $img->url }}" alt="{{ $img->alt_text }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Tarjeta de Códigos y QR -->
            <div class="card-surface p-5 border border-slate-100 space-y-3">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identificadores para Inventario</h4>
                <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                    <span class="text-slate-500">SKU Primario:</span>
                    <span class="font-bold font-mono text-slate-900">{{ $product->sku }}</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1 border-b border-slate-100">
                    <span class="text-slate-500">Código de Barras (EAN):</span>
                    <span class="font-mono text-slate-700">{{ $product->barcode ?? 'No configurado' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1">
                    <span class="text-slate-500">Código QR Generado:</span>
                    <span class="font-mono text-emerald-700 font-bold">{{ $product->qr_code }}</span>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Ficha Técnica y Detalles -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Precio y Resumen -->
            <div class="card-surface p-6 border border-slate-100 space-y-3">
                <div class="flex items-baseline justify-between">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase">Precio Unitario</span>
                        <p class="text-3xl font-black text-slate-900">{{ $product->formatted_price }}</p>
                    </div>
                    <span class="text-xs text-slate-400">Incluye IGV según legislación peruana</span>
                </div>

                @if($product->short_description)
                <p class="text-sm text-slate-600 leading-relaxed pt-2 border-t border-slate-100">
                    {{ $product->short_description }}
                </p>
                @endif
            </div>

            <!-- Ficha Técnica Estructurada -->
            <div class="card-surface p-6 border border-slate-100 space-y-4">
                <h3 class="text-base font-extrabold text-slate-900">Especificaciones Técnicas</h3>

                @if(!empty($product->technical_specs))
                <div class="divide-y divide-slate-100 text-xs">
                    @foreach($product->technical_specs as $key => $val)
                    <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <span class="font-bold text-slate-700 uppercase text-[11px] w-48">{{ str_replace('_', ' ', $key) }}:</span>
                        <span class="text-slate-600 font-medium flex-1">{{ $val }}</span>
                    </div>
                    @endforeach

                    @if($product->weight)
                    <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <span class="font-bold text-slate-700 uppercase text-[11px] w-48">PESO:</span>
                        <span class="text-slate-600 font-medium flex-1">{{ $product->weight }} kg</span>
                    </div>
                    @endif

                    @if($product->dimensions)
                    <div class="py-2.5 flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                        <span class="font-bold text-slate-700 uppercase text-[11px] w-48">DIMENSIONES:</span>
                        <span class="text-slate-600 font-medium flex-1">{{ $product->dimensions }}</span>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-xs text-slate-400 py-4">No se han registrado especificaciones técnicas aún.</p>
                @endif
            </div>

            <!-- Descripción Completa -->
            @if($product->description)
            <div class="card-surface p-6 border border-slate-100 space-y-2">
                <h3 class="text-base font-extrabold text-slate-900">Descripción Comercial Completa</h3>
                <div class="text-xs text-slate-600 leading-relaxed space-y-2">
                    {!! nl2br(e($product->description)) !!}
                </div>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
