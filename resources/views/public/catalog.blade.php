@extends('layouts.public')

@section('title', 'Catálogo de Laptops en Lima, Perú — Portátiles Perú')
@section('meta_description', 'Explora nuestro catálogo completo de laptops para ingeniería, gaming, oficina y diseño con entrega inmediata en Lima y envíos a todo el Perú.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header de Catálogo -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-6">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-50 text-violet-700 text-xs font-bold mb-2">
                <span class="w-2 h-2 rounded-full bg-violet-600"></span>
                <span>Catálogo Actualizado 2026</span>
            </div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight font-display">Laptops & Estaciones de Trabajo</h1>
            <p class="text-sm text-slate-500 mt-1">
                Mostrando {{ $products->total() }} equipos certificados con garantía local y stock en Lima.
            </p>
        </div>

        <!-- Selector de ordenamiento -->
        <form method="GET" action="{{ route('catalogo') }}" class="flex items-center gap-3">
            @foreach(request()->except(['orden', 'page']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <label for="ordenSelect" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Ordenar por:</label>
            <select
                id="ordenSelect"
                name="orden"
                onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-violet-500"
            >
                <option value="novedades" {{ request('orden', 'novedades') === 'novedades' ? 'selected' : '' }}>Más recientes</option>
                <option value="precio_bajo" {{ request('orden') === 'precio_bajo' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                <option value="precio_alto" {{ request('orden') === 'precio_alto' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                <option value="nombre_asc" {{ request('orden') === 'nombre_asc' ? 'selected' : '' }}>Nombre: A &rarr; Z</option>
            </select>
        </form>
    </div>

    <!-- Layout: Filtros + Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Sidebar de Filtros -->
        <aside class="lg:col-span-3 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        <span>Filtros</span>
                    </h2>
                    @if(request()->anyFilled(['q', 'categoria', 'marca', 'min_precio', 'max_precio', 'en_stock']))
                        <a href="{{ route('catalogo') }}" class="text-xs font-bold text-violet-600 hover:text-violet-700 hover:underline">
                            Limpiar todo
                        </a>
                    @endif
                </div>

                <form method="GET" action="{{ route('catalogo') }}" class="space-y-5">
                    @if(request('orden'))
                        <input type="hidden" name="orden" value="{{ request('orden') }}">
                    @endif

                    <!-- Búsqueda rápida -->
                    <div>
                        <label for="filterQ" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Búsqueda rápida</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="filterQ"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Modelo, CPU, SKU..."
                                class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                            >
                            <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Categorías -->
                    <div>
                        <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">Categoría</span>
                        <div class="space-y-1">
                            <a href="{{ route('catalogo', request()->except(['categoria', 'page'])) }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ !request('categoria') ? 'bg-violet-50 text-violet-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span>Todas las categorías</span>
                            </a>
                            @foreach($categories as $cat)
                                <a href="{{ route('catalogo', array_merge(request()->except(['page']), ['categoria' => $cat->slug])) }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request('categoria') === $cat->slug ? 'bg-violet-50 text-violet-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span>{{ $cat->name }}</span>
                                    <span class="text-[11px] font-mono text-slate-400">({{ $cat->products_count }})</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Marcas -->
                    <div>
                        <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2.5">Marca Oficial</span>
                        <div class="space-y-1 max-h-48 overflow-y-auto pr-1">
                            <a href="{{ route('catalogo', request()->except(['marca', 'page'])) }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ !request('marca') ? 'bg-violet-50 text-violet-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <span>Todas las marcas</span>
                            </a>
                            @foreach($brands as $b)
                                <a href="{{ route('catalogo', array_merge(request()->except(['page']), ['marca' => $b->slug])) }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition-colors {{ request('marca') === $b->slug ? 'bg-violet-50 text-violet-700 font-bold' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <span>{{ $b->name }}</span>
                                    <span class="text-[11px] font-mono text-slate-400">({{ $b->products_count }})</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rango de Precios -->
                    <div>
                        <span class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Precio (S/)</span>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                name="min_precio"
                                value="{{ request('min_precio') }}"
                                placeholder="Mínimo"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                            >
                            <input
                                type="number"
                                name="max_precio"
                                value="{{ request('max_precio') }}"
                                placeholder="Máximo"
                                class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                            >
                        </div>
                    </div>

                    <!-- Solo en Stock -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                        <label for="catalogInStock" class="text-xs font-bold text-slate-700 cursor-pointer">Solo con stock</label>
                        <input
                            type="checkbox"
                            name="en_stock"
                            id="catalogInStock"
                            value="1"
                            {{ request('en_stock') ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-violet-600 focus:ring-violet-500 border-slate-300"
                        >
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-sm">
                        Aplicar Filtros
                    </button>
                </form>
            </div>
        </aside>

        <!-- Listado de Productos -->
        <main class="lg:col-span-9 space-y-6">

            @if(request()->anyFilled(['q', 'categoria', 'marca', 'en_stock']))
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-bold text-slate-400">Filtros activos:</span>
                    @if(request('q'))
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-violet-50 text-violet-700 border border-violet-200">
                            "{{ request('q') }}"
                        </span>
                    @endif
                    @if(request('categoria'))
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-violet-50 text-violet-700 border border-violet-200">
                            Categoría: {{ request('categoria') }}
                        </span>
                    @endif
                    @if(request('marca'))
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-violet-50 text-violet-700 border border-violet-200">
                            Marca: {{ request('marca') }}
                        </span>
                    @endif
                    @if(request('en_stock'))
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Solo en Stock
                        </span>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($products as $product)
                    <div class="group bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm hover:shadow-xl hover:border-violet-300 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <!-- Imagen y Badges -->
                            <div class="relative aspect-[4/3] rounded-xl overflow-hidden bg-slate-50 mb-3 flex items-center justify-center">
                                <img
                                    src="{{ $product->primary_image_url }}"
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-contain p-3 group-hover:scale-105 transition-transform duration-300"
                                >
                                <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1">
                                    @if($product->is_new)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-violet-600 text-white shadow-sm">
                                            Nuevo
                                        </span>
                                    @endif
                                    @if($product->brand)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white shadow-sm">
                                            {{ $product->brand->name }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- SKU y Stock -->
                            <div class="flex items-center justify-between text-[11px] mb-2">
                                <span class="font-mono text-slate-400 font-semibold">{{ $product->sku }}</span>
                                @if($product->total_available_stock > 3)
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]">
                                        En Stock
                                    </span>
                                @elseif($product->total_available_stock > 0)
                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold text-[10px]">
                                        Últimas {{ $product->total_available_stock }} u.
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold text-[10px]">
                                        Agotado
                                    </span>
                                @endif
                            </div>

                            <!-- Título -->
                            <h3 class="font-bold text-sm text-slate-900 leading-snug line-clamp-2 group-hover:text-violet-600 transition-colors">
                                <a href="{{ route('catalogo.detalle', $product->slug) }}">
                                    {{ $product->name }}
                                </a>
                            </h3>

                            <!-- Specs Chips -->
                            @if(!empty($product->technical_specs))
                                <div class="flex flex-wrap gap-1 mt-2.5">
                                    @if(!empty($product->technical_specs['procesador']))
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-mono">
                                            {{ Str::limit($product->technical_specs['procesador'], 15) }}
                                        </span>
                                    @endif
                                    @if(!empty($product->technical_specs['ram']))
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-mono">
                                            {{ $product->technical_specs['ram'] }}
                                        </span>
                                    @endif
                                    @if(!empty($product->technical_specs['almacenamiento']))
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-mono">
                                            {{ $product->technical_specs['almacenamiento'] }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Precio y Acciones -->
                        <div class="mt-4 pt-3 border-t border-slate-100 space-y-2">
                            <div class="flex items-baseline justify-between">
                                <div>
                                    <span class="text-[10px] text-slate-400 font-bold block uppercase">Precio</span>
                                    <span class="text-xl font-black text-slate-900 font-display">
                                        {{ $product->formatted_price }}
                                    </span>
                                </div>
                                <a href="{{ route('catalogo.ficha-tecnica', $product->slug) }}" class="text-[11px] font-bold text-violet-600 hover:text-violet-700 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>PDF</span>
                                </a>
                            </div>

                            @php
                                $cWaNumber = \App\Models\Setting::getWhatsappNumber();
                                $cPdfUrl = route('catalogo.ficha-tecnica', $product->slug);
                                $cWaText = "¡Hola! 💻 Deseo cotizar/comprar esta laptop:\n"
                                         . "📌 *Producto:* {$product->name}\n"
                                         . "🏷️ *SKU:* {$product->sku}\n"
                                         . "💰 *Precio:* {$product->formatted_price}\n"
                                         . "📄 *Ficha PDF:* {$cPdfUrl}";
                                $cWaUrl = "https://wa.me/{$cWaNumber}?text=" . urlencode($cWaText);
                            @endphp

                            <div class="grid grid-cols-2 gap-2">
                                <a
                                    href="{{ $cWaUrl }}"
                                    target="_blank"
                                    onclick="window.open('{{ $cPdfUrl }}', '_blank')"
                                    class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-sm"
                                >
                                    <span>WhatsApp</span>
                                </a>

                                <form action="{{ route('carrito.agregar') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-violet-600 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition-colors shadow-sm">
                                        <span>+ Carrito</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-slate-200/80 space-y-4">
                        <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-black text-slate-900">No encontramos productos para tu búsqueda</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">
                            Prueba ajustando los filtros o utilizando términos más generales en el buscador.
                        </p>
                        <a href="{{ route('catalogo') }}" class="inline-block px-5 py-2.5 rounded-xl bg-violet-600 text-white text-xs font-bold hover:bg-violet-700 transition-colors">
                            Ver todo el catálogo
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Paginación -->
            @if($products->hasPages())
                <div class="pt-6">
                    {{ $products->links() }}
                </div>
            @endif

        </main>

    </div>

</div>
@endsection
