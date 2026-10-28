@extends('layouts.admin')

@section('title', 'Editar Laptop: ' . $product->name)

@section('content')
<div class="space-y-6">

    <!-- Encabezado -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex items-center justify-between">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Fase 2: Edición de Producto</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Editar Laptop: {{ $product->name }}</h2>
            <p class="text-slate-500 text-sm mt-0.5">
                SKU: <strong class="font-mono text-slate-700">{{ $product->sku }}</strong> — Modifique precios, especificaciones o imágenes.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.show', $product) }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Ver Ficha
            </a>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                &larr; Volver
            </a>
        </div>
    </div>

    <!-- Formulario de Edición -->
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Asistente Gemini AI (Google AI) -->
        <div class="card-surface p-6 sm:p-7 border-2 border-emerald-400/50 bg-gradient-to-br from-emerald-950 via-slate-900 to-emerald-900 text-white rounded-3xl shadow-xl space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-emerald-400 to-teal-300 text-slate-950 flex items-center justify-center font-black text-base shadow-md">✨</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-black tracking-tight text-white">Asistente IA Gemini</h3>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Gemini 1.5 Flash</span>
                        </div>
                        <p class="text-xs text-emerald-100/70">Actualiza o completa automáticamente especificaciones, descripciones y taxonomía de esta laptop.</p>
                    </div>
                </div>

                <div class="shrink-0">
                    <button type="button" data-open-gemini-modal id="gemini-api-key-badge" class="px-3.5 py-1.5 rounded-full text-xs font-bold {{ $hasGeminiKey ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30' : 'bg-amber-400/20 text-amber-300 border border-amber-400/30' }} hover:scale-105 transition-all">
                        {{ $hasGeminiKey ? '🟢 API Key Conectada' : '⚡ Configurar API Key' }}
                    </button>
                </div>
            </div>

            <div class="space-y-3 pt-1">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="gemini-prompt-input" value="{{ $product->name }}" placeholder="Escriba el modelo o pegue especificaciones actualizadas..." class="w-full px-4 py-3 rounded-2xl bg-white/10 border border-white/20 text-white text-xs placeholder:text-emerald-100/40 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:bg-white/15">
                    <button type="button" id="btn-gemini-generate" class="shrink-0 px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/30 transition-all hover:scale-[1.02] flex items-center justify-center gap-2">
                        <span>✨ Regenerar con Gemini</span>
                    </button>
                </div>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-emerald-100/80 pt-1">
                    <span class="font-bold text-[11px] uppercase tracking-wider text-emerald-300">Incluir en actualización:</span>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="check-gemini-identification" checked class="w-3.5 h-3.5 rounded text-emerald-500 bg-white/20 border-white/30 focus:ring-0">
                        <span>1. Identificación</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="check-gemini-descriptions" checked class="w-3.5 h-3.5 rounded text-emerald-500 bg-white/20 border-white/30 focus:ring-0">
                        <span>2. Descripciones</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="check-gemini-specs" checked class="w-3.5 h-3.5 rounded text-emerald-500 bg-white/20 border-white/30 focus:ring-0">
                        <span>3. Ficha Técnica</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Sección 1: Identificación -->
        <div class="card-surface p-6 sm:p-8 border border-slate-100 space-y-5">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs">1</span>
                    <span>Identificación y Clasificación</span>
                </h3>
                <button type="button" data-gemini-section="identification" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs flex items-center gap-1.5 transition-colors border border-emerald-200">
                    <span>✨ Autocompletar con Gemini</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre Comercial *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Código SKU Único *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Categoría *</label>
                    <select name="category_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Subcategoría</label>
                    <select name="subcategory_id" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">Ninguna / Principal</option>
                        @foreach($categories as $category)
                            @foreach($category->subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ old('subcategory_id', $product->subcategory_id) == $sub->id ? 'selected' : '' }}>{{ $category->name }} &rarr; {{ $sub->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Marca *</label>
                    <select name="brand_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Modelo</label>
                    <select name="product_model_id" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="">Sin modelo específico</option>
                        @foreach($brands as $brand)
                            @foreach($brand->models as $model)
                            <option value="{{ $model->id }}" {{ old('product_model_id', $product->product_model_id) == $model->id ? 'selected' : '' }}>{{ $brand->name }} - {{ $model->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Código de Barras / EAN</label>
                    <div class="flex gap-2">
                        <input id="barcode-input" type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm font-mono focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <button type="button" data-open-barcode-scanner class="shrink-0 rounded-2xl border border-emerald-200 bg-emerald-50 px-3 text-xs font-bold text-emerald-700 hover:bg-emerald-100">Escanear</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección 2: Precios y Visibilidad -->
        <div class="card-surface p-6 sm:p-8 border border-slate-100 space-y-5">
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs">2</span>
                <span>Precios y Visibilidad</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Precio Unitario *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">S/</span>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price) }}" required class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-slate-200 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Moneda</label>
                    <select name="currency" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="PEN" {{ old('currency', $product->currency) === 'PEN' ? 'selected' : '' }}>PEN - Soles Peruanos</option>
                        <option value="USD" {{ old('currency', $product->currency) === 'USD' ? 'selected' : '' }}>USD - Dólares Americanos</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Estado en Catálogo *</label>
                    <select name="status" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <option value="published" {{ old('status', $product->status) === 'published' ? 'selected' : '' }}>Publicado (Visible)</option>
                        <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Borrador (Oculto)</option>
                        <option value="archived" {{ old('status', $product->status) === 'archived' ? 'selected' : '' }}>Archivado (Inactivo)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alerta Stock Mínimo</label>
                    <input type="number" name="min_stock_alert" value="{{ old('min_stock_alert', $product->min_stock_alert) }}" min="0" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div class="sm:col-span-2 flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <span class="text-xs font-semibold text-slate-700">Producto Destacado en Inicio</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                        <span class="text-xs font-semibold text-slate-700">Etiquetar como Novedad</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Sección 3: Descripciones -->
        <div class="card-surface p-6 sm:p-8 border border-slate-100 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs">3</span>
                    <span>Descripciones Comerciales</span>
                </h3>
                <button type="button" data-gemini-section="descriptions" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs flex items-center gap-1.5 transition-colors border border-emerald-200">
                    <span>✨ Redactar con Gemini</span>
                </button>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Resumen Corto para WhatsApp y Catálogo</label>
                <textarea name="short_description" rows="2" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">{{ old('short_description', $product->short_description) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Descripción Detallada</label>
                <textarea name="description" rows="4" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        <!-- Sección 4: Ficha Técnica Estructurada -->
        <div class="card-surface p-6 sm:p-8 border border-slate-100 space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs">4</span>
                        <span>Ficha Técnica Estructurada</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Estos datos se utilizan para el catálogo web y las 8 plantillas de catálogo PDF.</p>
                </div>
                <button type="button" data-gemini-section="specs" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs flex items-center gap-1.5 transition-colors border border-emerald-200 self-start sm:self-auto">
                    <span>✨ Extraer Ficha con Gemini</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                @php $specs = $product->technical_specs ?? []; @endphp
                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Procesador (CPU)</label>
                    <input type="text" name="technical_specs[procesador]" value="{{ old('technical_specs.procesador', $specs['procesador'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Memoria RAM</label>
                    <input type="text" name="technical_specs[ram]" value="{{ old('technical_specs.ram', $specs['ram'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Almacenamiento (SSD)</label>
                    <input type="text" name="technical_specs[almacenamiento]" value="{{ old('technical_specs.almacenamiento', $specs['almacenamiento'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Pantalla</label>
                    <input type="text" name="technical_specs[pantalla]" value="{{ old('technical_specs.pantalla', $specs['pantalla'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Tarjeta Gráfica (GPU)</label>
                    <input type="text" name="technical_specs[grafica]" value="{{ old('technical_specs.grafica', $specs['grafica'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Sistema Operativo</label>
                    <input type="text" name="technical_specs[sistema_operativo]" value="{{ old('technical_specs.sistema_operativo', $specs['sistema_operativo'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Conectividad / Puertos</label>
                    <input type="text" name="technical_specs[conectividad]" value="{{ old('technical_specs.conectividad', $specs['conectividad'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Garantía Oficial</label>
                    <input type="text" name="technical_specs[garantia]" value="{{ old('technical_specs.garantia', $specs['garantia'] ?? '') }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-1">Peso (kg) / Dimensiones</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="number" step="0.01" name="weight" value="{{ old('weight', $product->weight) }}" placeholder="kg" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        <input type="text" name="dimensions" value="{{ old('dimensions', $product->dimensions) }}" placeholder="cm x cm x cm" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>
            <div id="additional-specifications" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                @foreach($specs as $key => $value)
                    @if(!in_array($key, ['procesador', 'ram', 'almacenamiento', 'pantalla', 'grafica', 'sistema_operativo', 'conectividad', 'garantia'], true))
                    <div class="space-y-2" data-specification-row>
                        <div class="flex gap-2"><input type="text" value="{{ $key }}" data-specification-key class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-bold"><button type="button" data-remove-specification class="rounded-xl px-2 text-rose-600">×</button></div>
                        <input type="text" value="{{ $value }}" data-specification-value class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    @endif
                @endforeach
            </div>
            <button type="button" data-add-specification class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100">+ Agregar otro dato técnico</button>
        </div>

        <!-- Sección 5: Galería de Imágenes Existente y Nueva -->
        <div class="card-surface p-6 sm:p-8 border border-slate-100 space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs">5</span>
                        <span>Galería de Imágenes</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Gestione las fotos existentes o agregue nuevas fotos por archivo o URL individual.</p>
                </div>
                <span id="gallery-counter-badge" class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">Máx. 8 imágenes</span>
            </div>

            <!-- Galería Actual -->
            @if($product->images->count() > 0)
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase">Imágenes Actuales del Producto ({{ $product->images->count() }})</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                    @foreach($product->images as $img)
                    <div class="relative group rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 aspect-square shadow-xs">
                        <img src="{{ $img->url }}" alt="{{ $img->alt_text }}" class="w-full h-full object-cover">
                        @if($img->is_primary)
                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-500 text-white shadow">Principal</span>
                        @endif
                        <button type="submit" form="delete-img-{{ $img->id }}" onclick="return confirm('¿Eliminar esta imagen?')" class="absolute top-2 right-2 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold flex items-center justify-center shadow transition-all opacity-90 group-hover:opacity-100">&times;</button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Subir Nuevas Imágenes Locales -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 uppercase">1. Elegir fotos locales adicionales (JPG, PNG, WEBP máx 4MB)</label>
                <div class="p-5 rounded-2xl border-2 border-dashed border-slate-200 hover:border-emerald-500 transition-colors bg-slate-50/50">
                    <input type="file" id="local-file-input" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-500 cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1.5">Puede seleccionar varias imágenes al mismo tiempo desde su explorador de archivos.</p>
                </div>
                <div id="local-files-preview" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 pt-2 hidden"></div>
            </div>

            <!-- Opción única para agregar URLs de imágenes 1 a 1 -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <label class="block text-xs font-bold text-slate-700 uppercase">2. Agregar imágenes por URL (Una a una con previsualización)</label>
                <div class="flex gap-2">
                    <input type="url" id="image-url-input" placeholder="Pegue la URL de la imagen aquí (Ej: https://images.unsplash.com/...)" class="w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button type="button" id="btn-add-image-url" class="shrink-0 px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Agregar Imagen</span>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400">Pegue un enlace directo y presione "Agregar Imagen" para añadirla a la galería con vista previa inmediata.</p>

                <!-- Contenedor donde se van agregando una a una las imágenes con vista previa y botón de quitar -->
                <div id="url-images-container" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 pt-2">
                    @if(old('image_urls'))
                        @foreach(old('image_urls') as $oldUrl)
                            @if(filter_var($oldUrl, FILTER_VALIDATE_URL))
                            <div class="relative group rounded-2xl overflow-hidden border border-slate-200 bg-white aspect-square shadow-xs flex flex-col items-center justify-center p-1.5" data-url-card>
                                <img src="{{ $oldUrl }}" alt="Vista previa" class="w-full h-full object-cover rounded-xl" onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\' fill=\'%23f1f5f9\'%3E%3Ctext x=\'50%25\' y=\'50%25\' dominant-baseline=\'middle\' text-anchor=\'middle\' font-size=\'10\' fill=\'%2394a3b8\'%3EError URL%3C/text%3E%3C/svg%3E'">
                                <input type="hidden" name="image_urls[]" value="{{ $oldUrl }}">
                                <button type="button" data-remove-url-image class="absolute top-1.5 right-1.5 w-6 h-6 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center shadow-md transition-all">&times;</button>
                                <span class="absolute bottom-1.5 left-1.5 px-2 py-0.5 rounded text-[9px] font-bold bg-slate-900/80 text-white backdrop-blur-xs">URL</span>
                            </div>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        @if($product->images->count() > 0)
            @foreach($product->images as $img)
            <form id="delete-img-{{ $img->id }}" action="{{ route('admin.products.deleteImage', [$product, $img]) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            @endforeach
        @endif

        <!-- Botones de Guardar -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.products.index') }}" class="px-6 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 text-white font-bold text-xs shadow-xl shadow-emerald-500/20 transition-all hover:scale-[1.01]">
                Guardar Cambios del Producto
            </button>
        </div>
    </form>

</div>
@include('admin.products.partials.product-form-tools')
@endsection
