@extends('layouts.admin')

@section('title', 'Catálogos y Exportaciones')

@section('content')
<div class="space-y-6">

    <!-- Encabezado con Pasos -->
    <div class="card-surface rounded-3xl border border-slate-100 p-6 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-800 border border-emerald-400/30">Generador de Catálogos</span>
        </div>
        <h1 class="mt-2 text-2xl font-black text-slate-900 tracking-tight">Diseña, previsualiza y descarga tu catálogo</h1>
        <p class="mt-1 text-xs text-slate-500">Selecciona un diseño, una o varias categorías y marcas. La previsualización se muestra aquí mismo.</p>
        
        <div class="mt-5 grid gap-2 text-xs sm:grid-cols-4">
            @foreach(['Selecciona contenido', 'Elige plantilla', 'Configura el PDF', 'Genera y descarga'] as $step)
            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-slate-700 font-medium">
                <span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-500 text-[10px] font-black text-slate-950">{{ $loop->iteration }}</span>
                <span>{{ $step }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Formulario de Configuración (Tarjeta Blanca) -->
    <form id="catalog-form" method="POST" action="{{ route('admin.catalog-pdf.generate') }}" target="catalog-preview" class="card-surface space-y-6 rounded-3xl border border-slate-100 p-6 shadow-sm">
        @csrf
        
        <!-- 1. Elige el diseño -->
        <div>
            <p class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-700">1. Elige el diseño</p>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                @foreach($templates as $template)
                <label class="group cursor-pointer">
                    <input class="peer sr-only" type="radio" name="template" value="{{ $template }}" {{ $loop->first ? 'checked' : '' }}>
                    <span class="block overflow-hidden rounded-2xl border border-slate-200 bg-slate-50 p-3 transition peer-checked:border-emerald-500 peer-checked:ring-2 peer-checked:ring-emerald-500/20 hover:border-slate-300">
                        <span class="{{ in_array($template, ['premium', 'moderno', 'corporativo']) ? 'bg-slate-900 text-white' : 'bg-white text-slate-900' }} flex h-20 flex-col justify-between rounded-xl p-3 shadow-inner border border-slate-200/60">
                            <span class="h-2 w-16 rounded bg-emerald-500"></span>
                            <span class="grid grid-cols-2 gap-1.5"><i class="h-5 rounded bg-slate-200/50"></i><i class="h-5 rounded bg-slate-200/50"></i></span>
                        </span>
                        <span class="mt-2.5 block text-xs font-black capitalize text-slate-800 text-center">{{ $template }}</span>
                    </span>
                </label>
                @endforeach
            </div>
        </div>

        <!-- 2. Categorías & 3. Marcas -->
        <div class="grid gap-5 lg:grid-cols-2">
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-700">2. Categorías</p>
                <div class="max-h-48 space-y-2.5 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    @foreach($categories as $category)
                    <label class="flex items-center gap-2.5 text-xs text-slate-700 font-medium hover:text-slate-900 cursor-pointer">
                        <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>{{ $category->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-700">3. Marcas</p>
                <div class="max-h-48 space-y-2.5 overflow-y-auto rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    @foreach($brands as $brand)
                    <label class="flex items-center gap-2.5 text-xs text-slate-700 font-medium hover:text-slate-900 cursor-pointer">
                        <input type="checkbox" name="brand_ids[]" value="{{ $brand->id }}" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>{{ $brand->name }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Configuración de Hoja y Columnas -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="text-xs font-bold text-slate-700">Papel
                <select name="paper" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="a4">A4</option>
                    <option value="letter">Carta</option>
                </select>
            </label>
            <label class="text-xs font-bold text-slate-700">Orientación
                <select name="orientation" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="portrait">Vertical</option>
                    <option value="landscape">Horizontal</option>
                </select>
            </label>
            <label class="text-xs font-bold text-slate-700">Productos por fila
                <select name="columns" class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-semibold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    <option value="3">3 productos</option>
                    <option value="4" selected>4 productos</option>
                </select>
            </label>
            <div class="sm:col-span-2 lg:col-span-1 flex flex-wrap content-end gap-x-4 gap-y-2 text-xs text-slate-700">
                @foreach(['show_prices'=>'Precios','show_sku'=>'SKU','show_stock'=>'Stock','show_specs'=>'Especificaciones'] as $field => $label)
                <label class="flex items-center gap-1.5 cursor-pointer font-medium">
                    <input type="checkbox" name="{{ $field }}" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>

        <!-- Datos que aparecerán en la descarga -->
        <div class="grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:grid-cols-2">
            <p class="sm:col-span-2 text-xs font-bold uppercase tracking-wider text-slate-700">Datos que aparecerán en la descarga</p>
            <input name="company_name" value="{{ $settings['company_name'] ?? 'Portátiles Perú' }}" placeholder="Nombre de empresa" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            <input name="company_phone" value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="Celular / WhatsApp" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            <input name="company_email" type="email" value="{{ $settings['company_email'] ?? '' }}" placeholder="Correo de empresa" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            <input name="cover_title" value="Catálogo de productos" placeholder="Título de portada" class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
            <textarea name="payment_methods" placeholder="Métodos de pago" class="sm:col-span-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ $settings['payment_methods'] ?? 'Yape · Plin · Visa · Mastercard' }}</textarea>
        </div>

        <!-- Selección manual de productos (opcional) -->
        <details class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-700">
            <summary class="cursor-pointer font-bold text-slate-900">Selección manual de productos (opcional)</summary>
            <p class="mt-2 text-slate-500">Si seleccionas productos específicos aquí, se aplicarán además los filtros de categoría y marca.</p>
            <div class="mt-3 grid max-h-48 grid-cols-1 gap-2 overflow-y-auto sm:grid-cols-2 xl:grid-cols-3">
                @foreach($products as $product)
                <label class="flex gap-2 cursor-pointer font-medium hover:text-slate-900">
                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <span>{{ $product->sku }} — {{ $product->name }}</span>
                </label>
                @endforeach
            </div>
        </details>

        <!-- Botones de Acción -->
        <div class="flex flex-wrap gap-3 pt-2">
            <button
                type="submit"
                formaction="{{ route('admin.catalog-pdf.preview') }}"
                formtarget="_self"
                class="rounded-xl bg-emerald-500 hover:bg-emerald-400 px-5 py-3 text-xs font-black text-slate-950 shadow-md hover:shadow transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>Ver Diseño / Maqueta y Descargar</span>
            </button>
            <button
                type="submit"
                name="format"
                value="pdf"
                onclick="this.form.querySelector('[name=download]').value = ''"
                class="rounded-xl bg-slate-900 hover:bg-slate-800 px-5 py-3 text-xs font-bold text-white shadow-sm transition-all"
            >
                Previsualizar PDF en Marco
            </button>
            <button
                type="submit"
                name="format"
                value="pdf"
                formaction="{{ route('admin.catalog-pdf.generate') }}"
                formtarget="_self"
                onclick="this.form.querySelector('[name=download]').value = '1'"
                class="rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 px-5 py-3 text-xs font-bold shadow-sm transition-all"
            >
                Descargar PDF Directo
            </button>
            <button
                type="submit"
                name="format"
                value="csv"
                formtarget="_self"
                class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-5 py-3 text-xs font-bold text-slate-700 shadow-sm transition-all"
            >
                Descargar Excel (CSV)
            </button>
            <input type="hidden" name="download" value="">
        </div>
    </form>

    <!-- Previsualización -->
    <section class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-5 py-3.5">
            <div>
                <h2 class="text-sm font-black text-slate-900">Previsualización del catálogo</h2>
                <p class="text-xs text-slate-500">El PDF se renderiza en tiempo real sin salir del panel.</p>
            </div>
            <button
                type="submit"
                form="catalog-form"
                name="format"
                value="pdf"
                onclick="this.form.querySelector('[name=download]').value = ''"
                class="rounded-xl bg-slate-900 hover:bg-slate-800 px-3.5 py-2 text-xs font-bold text-white shadow-sm transition-all"
            >
                Actualizar vista
            </button>
        </div>
        <iframe name="catalog-preview" title="Previsualización del catálogo PDF" class="h-[760px] w-full bg-white" src="about:blank"></iframe>
    </section>

</div>
@endsection
