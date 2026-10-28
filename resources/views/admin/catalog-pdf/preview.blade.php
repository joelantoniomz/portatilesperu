@extends('layouts.admin')

@section('title', 'Previsualización de Catálogo - Portátiles Perú')

@section('content')
<div class="space-y-6">

    <!-- Migas de Pan y Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.catalog-pdf.index') }}" class="text-xs font-bold text-emerald-300 hover:text-white transition-colors">
                    &larr; Generador de Catálogos
                </a>
                <span class="text-emerald-400/40">/</span>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                    Maqueta Visual
                </span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight mt-1">Diseño y Maqueta del Catálogo</h1>
            <p class="text-xs text-emerald-100/70 mt-0.5">
                Comprueba la diagramación con imágenes, nombres, categorías, precios y especificaciones antes de descargar.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.catalog-pdf.index') }}"
                class="px-4 py-2.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm transition-colors"
            >
                Modificar Filtros
            </a>
        </div>
    </div>

    <!-- Layout Principal: Maqueta a la Izquierda, Acciones de Descarga al Costado -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Columna Izquierda: Maqueta Visual del Catálogo (8 Columnas) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Portada del Catálogo -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-3 bg-gradient-to-r from-emerald-950 via-emerald-900 to-slate-900 text-white">
                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500 text-slate-950">
                        {{ strtoupper($options['template']) }}
                    </span>
                    <span class="text-xs text-emerald-200 font-mono">{{ now()->format('d/m/Y') }}</span>
                </div>
                <h2 class="text-2xl font-black tracking-tight">{{ $options['company_name'] }}</h2>
                <p class="text-sm font-bold text-emerald-300">{{ $options['cover_title'] }}</p>
                <div class="pt-2 border-t border-white/10 text-xs text-slate-300 space-y-1">
                    <p><strong>Líneas:</strong> {{ $options['category_names'] ?: 'Todas las categorías' }}</p>
                    <p><strong>WhatsApp:</strong> {{ $options['company_phone'] ?: 'No especificado' }} &bull; <strong>Email:</strong> {{ $options['company_email'] ?: 'No especificado' }}</p>
                    <p><strong>Medios de Pago:</strong> {{ $options['payment_methods'] }}</p>
                </div>
            </div>

            <!-- Grilla de Productos Maquetados con Imágenes -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                        Productos Incluidos ({{ $products->count() }} Laptops)
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">
                        Diagramación: {{ $options['columns'] }} por fila en hoja {{ strtoupper($options['paper']) }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 {{ ($options['columns'] ?? 4) == 3 ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }} gap-4">
                    @forelse($products as $product)
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-emerald-400 hover:shadow-md transition-all flex flex-col justify-between">
                        <div>
                            <!-- Imagen del Producto -->
                            <div class="w-full h-36 bg-slate-50 rounded-xl overflow-hidden mb-3 border border-slate-100 flex items-center justify-center p-2 relative group">
                                @if($product->primary_image_url)
                                    <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.onerror=null; this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'200\' viewBox=\'0 0 300 200\' fill=\'%23f1f5f9\'%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-family=\'sans-serif\' font-size=\'12\' font-weight=\'bold\' fill=\'%23059669\'%3EPortátiles Perú%3C/text%3E%3C/svg%3E';" class="max-h-full max-w-full object-contain">
                                @else
                                    <div class="text-xs font-bold text-slate-400 text-center">Sin imagen</div>
                                @endif
                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-md text-[9px] font-bold bg-white/90 text-slate-700 shadow-xs border border-slate-200">
                                    {{ $product->brand?->name }}
                                </span>
                            </div>

                            <!-- Categoría y Nombre -->
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 block mb-0.5">
                                {{ $product->category?->name }}
                            </span>
                            <h4 class="text-xs font-bold text-slate-900 line-clamp-2 leading-tight mb-2" title="{{ $product->name }}">
                                {{ $product->name }}
                            </h4>

                            <!-- SKU y Stock -->
                            <div class="flex items-center justify-between text-[11px] text-slate-500 mb-2 font-mono">
                                @if($options['show_sku'])
                                    <span class="font-bold text-slate-700">{{ $product->sku }}</span>
                                @endif
                                @if($options['show_stock'])
                                    <span class="text-sky-700 font-semibold">&bull; {{ $product->total_available_stock }} u. disp.</span>
                                @endif
                            </div>

                            <!-- Especificaciones Técnicas Clave -->
                            @if($options['show_specs'] && !empty($product->technical_specs) && is_array($product->technical_specs))
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 text-[10.5px] text-slate-600 space-y-1 mb-3">
                                @php $count = 0; @endphp
                                @foreach($product->technical_specs as $k => $v)
                                    @if($count < 3 && is_string($v))
                                        <div class="truncate">
                                            <strong class="text-slate-800">{{ ucfirst(str_replace('_', ' ', $k)) }}:</strong> {{ $v }}
                                        </div>
                                        @php $count++; @endphp
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Precio -->
                        @if($options['show_prices'])
                        <div class="pt-2 border-t border-slate-100 flex items-baseline justify-between">
                            <span class="text-[10px] uppercase font-bold text-slate-400">Precio:</span>
                            <span class="text-base font-black text-emerald-700 font-mono">
                                {{ $product->formatted_price }}
                            </span>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="col-span-full py-12 text-center text-slate-400">
                        No hay productos seleccionados para esta plantilla.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Panel Lateral de Descarga ("Al costadito") (4 Columnas) -->
        <div class="lg:col-span-4 space-y-6 sticky top-24">

            <!-- Tarjeta de Descarga Directa -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-5">
                <div>
                    <span class="text-[10px] uppercase font-bold tracking-wider text-emerald-700 block">Descargar Catálogo</span>
                    <h3 class="text-lg font-black text-slate-900 mt-0.5">Exportar Plantilla Lista</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Genera el documento en alta resolución con las imágenes y especificaciones visualizadas.
                    </p>
                </div>

                <!-- Formulario para Descargar PDF Directamente -->
                <form method="POST" action="{{ route('admin.catalog-pdf.generate') }}" class="space-y-3">
                    @csrf
                    <!-- Parámetros Ocultos Replicados -->
                    <input type="hidden" name="template" value="{{ $options['template'] }}">
                    <input type="hidden" name="paper" value="{{ $options['paper'] }}">
                    <input type="hidden" name="orientation" value="{{ $options['orientation'] }}">
                    <input type="hidden" name="columns" value="{{ $options['columns'] }}">
                    <input type="hidden" name="company_name" value="{{ $options['company_name'] }}">
                    <input type="hidden" name="company_phone" value="{{ $options['company_phone'] }}">
                    <input type="hidden" name="company_email" value="{{ $options['company_email'] }}">
                    <input type="hidden" name="payment_methods" value="{{ $options['payment_methods'] }}">
                    <input type="hidden" name="cover_title" value="{{ $options['cover_title'] }}">
                    <input type="hidden" name="show_prices" value="{{ $options['show_prices'] ? 1 : 0 }}">
                    <input type="hidden" name="show_sku" value="{{ $options['show_sku'] ? 1 : 0 }}">
                    <input type="hidden" name="show_stock" value="{{ $options['show_stock'] ? 1 : 0 }}">
                    <input type="hidden" name="show_specs" value="{{ $options['show_specs'] ? 1 : 0 }}">
                    @foreach($data['category_ids'] ?? [] as $cid)
                        <input type="hidden" name="category_ids[]" value="{{ $cid }}">
                    @endforeach
                    @foreach($data['brand_ids'] ?? [] as $bid)
                        <input type="hidden" name="brand_ids[]" value="{{ $bid }}">
                    @endforeach
                    @foreach($data['product_ids'] ?? [] as $pid)
                        <input type="hidden" name="product_ids[]" value="{{ $pid }}">
                    @endforeach

                    <!-- Botón Descargar PDF -->
                    <button
                        type="submit"
                        name="format"
                        value="pdf"
                        onclick="this.form.querySelector('[name=download]').value = '1'"
                        class="w-full py-3.5 px-4 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-md hover:shadow transition-all flex items-center justify-center gap-2 hover:scale-[1.01]"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Descargar Catálogo PDF</span>
                    </button>

                    <!-- Botón Previsualizar PDF en Nueva Pestaña -->
                    <button
                        type="submit"
                        name="format"
                        value="pdf"
                        formtarget="_blank"
                        onclick="this.form.querySelector('[name=download]').value = ''"
                        class="w-full py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>Abrir PDF en Pantalla Completa</span>
                    </button>

                    <!-- Botón Descargar Excel / CSV -->
                    <button
                        type="submit"
                        name="format"
                        value="csv"
                        class="w-full py-2.5 px-4 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm transition-all flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Descargar en Excel (CSV)</span>
                    </button>

                    <input type="hidden" name="download" value="1">
                </form>
            </div>

            <!-- Resumen de Configuración -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-3 text-xs">
                <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider border-b border-slate-100 pb-2">
                    Resumen del Documento
                </h4>
                <div class="space-y-2 text-slate-600">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Plantilla:</span>
                        <span class="font-bold text-slate-800 capitalize">{{ $options['template'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Total productos:</span>
                        <span class="font-bold text-slate-800">{{ $products->count() }} equipos</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Formato hoja:</span>
                        <span class="font-bold text-slate-800 uppercase">{{ $options['paper'] }} ({{ $options['orientation'] }})</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Columnas:</span>
                        <span class="font-bold text-slate-800">{{ $options['columns'] }} por fila</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <a
                        href="{{ route('admin.catalog-pdf.index') }}"
                        class="block text-center text-xs font-bold text-emerald-700 hover:text-emerald-800 hover:underline"
                    >
                        &larr; Volver a cambiar categorías o marcas
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
