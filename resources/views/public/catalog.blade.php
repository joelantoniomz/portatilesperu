@extends('layouts.public')

@section('title', 'Catálogo de Laptops en Lima, Perú - Portátiles Perú')
@section('meta_description', 'Explora nuestro catálogo de laptops gaming, ultrabooks y estaciones de trabajo. Filtra por marca, categoría y especificaciones con stock disponible en Lima.')

@section('content')
<div class="mx-auto max-w-[1440px] space-y-5 px-4 py-5 sm:px-6 lg:px-8">

    <!-- Encabezado de Sección -->
    <div class="flex flex-col items-start justify-between gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold mb-2">
                <span>Catálogo Oficial 2026</span>
            </div>
            <h1 class="text-2xl font-black tracking-tight text-slate-950">Laptops Destacadas</h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Mostrando {{ $products->total() }} laptops publicadas con garantía oficial y disponibilidad en sedes.
            </p>
        </div>

        <!-- Selector de Ordenamiento -->
        <form method="GET" action="{{ route('catalogo') }}" class="flex items-center gap-2">
            @foreach(request()->except(['orden', 'page']) as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <label for="ordenSelect" class="whitespace-nowrap text-xs font-semibold text-slate-500">Ordenar por:</label>
            <select
                id="ordenSelect"
                name="orden"
                onchange="this.form.submit()"
                class="cursor-pointer rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
            >
                <option value="novedades" {{ request('orden', 'novedades') === 'novedades' ? 'selected' : '' }}>Más recientes</option>
                <option value="precio_bajo" {{ request('orden') === 'precio_bajo' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                <option value="precio_alto" {{ request('orden') === 'precio_alto' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                <option value="nombre_asc" {{ request('orden') === 'nombre_asc' ? 'selected' : '' }}>Nombre (A-Z)</option>
            </select>
        </form>
    </div>

    <!-- Layout en 2 Columnas: Filtros Laterales + Grid de Productos -->
    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-12">

        <!-- Sidebar de Filtros -->
        <aside class="lg:col-span-2 xl:col-span-2">
            <div class="glass-card space-y-5 rounded-2xl border border-slate-200 p-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="flex items-center gap-2 text-sm font-bold tracking-wide text-slate-900">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>Filtros</span>
                    </h2>

                    @if(request()->anyFilled(['q', 'categoria', 'marca', 'min_precio', 'max_precio', 'en_stock']))
                    <a href="{{ route('catalogo') }}" class="text-[11px] font-bold text-emerald-400 hover:underline">
                        Limpiar todo
                    </a>
                    @endif
                </div>

                <form method="GET" action="{{ route('catalogo') }}" class="space-y-5">
                    @if(request('orden'))
                        <input type="hidden" name="orden" value="{{ request('orden') }}">
                    @endif

                    <!-- Búsqueda en texto -->
                    <div>
                        <label for="filterQueryInput" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Búsqueda rápida</label>
                        <input
                            id="filterQueryInput"
                            type="text"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Modelo, SKU, CPU..."
                            class="w-full px-3.5 py-2 rounded-xl bg-forest-900 border border-emerald-500/20 text-white placeholder-slate-500 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-400"
                        >
                    </div>

                    <!-- Filtro por Categorías -->
                    <div>
                        <span class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Categoría</span>
                        <div class="space-y-1.5 text-xs">
                            <a href="{{ route('catalogo', array_merge(request()->except(['categoria', 'page']))) }}" class="flex items-center justify-between py-1 px-2 rounded-lg transition-colors {{ !request('categoria') ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-400 hover:text-white' }}">
                                <span>Todas las categorías</span>
                            </a>
                            @foreach($categories as $cat)
                            <a href="{{ route('catalogo', array_merge(request()->except(['page']), ['categoria' => $cat->slug])) }}" class="flex items-center justify-between py-1 px-2 rounded-lg transition-colors {{ request('categoria') === $cat->slug ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-400 hover:text-white' }}">
                                <span>{{ $cat->name }}</span>
                                <span class="font-mono text-[10px] text-slate-500">({{ $cat->products_count }})</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Filtro por Marcas -->
                    <div>
                        <span class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Marca Oficial</span>
                        <div class="space-y-1.5 text-xs max-h-48 overflow-y-auto pr-1">
                            <a href="{{ route('catalogo', array_merge(request()->except(['marca', 'page']))) }}" class="flex items-center justify-between py-1 px-2 rounded-lg transition-colors {{ !request('marca') ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-400 hover:text-white' }}">
                                <span>Todas las marcas</span>
                            </a>
                            @foreach($brands as $b)
                            <a href="{{ route('catalogo', array_merge(request()->except(['page']), ['marca' => $b->slug])) }}" class="flex items-center justify-between py-1 px-2 rounded-lg transition-colors {{ request('marca') === $b->slug ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-slate-400 hover:text-white' }}">
                                <span>{{ $b->name }}</span>
                                <span class="font-mono text-[10px] text-slate-500">({{ $b->products_count }})</span>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Rango de Precios -->
                    <div>
                        <span class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Rango de Precio (S/)</span>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                name="min_precio"
                                value="{{ request('min_precio') }}"
                                placeholder="Mínimo"
                                class="w-full px-3 py-1.5 rounded-xl bg-forest-900 border border-emerald-500/20 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            >
                            <input
                                type="number"
                                name="max_precio"
                                value="{{ request('max_precio') }}"
                                placeholder="Máximo"
                                class="w-full px-3 py-1.5 rounded-xl bg-forest-900 border border-emerald-500/20 text-white text-xs placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            >
                        </div>
                    </div>

                    <!-- Toggle Solo en Stock -->
                    <div class="pt-2 border-t border-emerald-950 flex items-center justify-between">
                        <label for="enStockCheck" class="text-xs font-bold text-slate-300 cursor-pointer">
                            Solo con Stock Disponible
                        </label>
                        <input
                            type="checkbox"
                            name="en_stock"
                            id="enStockCheck"
                            value="1"
                            {{ request('en_stock') ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-emerald-500 bg-forest-900 border-emerald-500/30 focus:ring-emerald-400"
                        >
                    </div>

                    <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-all">
                        Aplicar Filtros
                    </button>
                </form>
            </div>
        </aside>

        <!-- Grid de Laptops -->
        <div class="space-y-5 lg:col-span-10 xl:col-span-10">

            <section class="relative overflow-hidden rounded-2xl bg-[#03120d] px-6 py-6 text-white sm:px-9 sm:py-7">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_62%_25%,rgba(34,197,94,0.42),transparent_28%)]"></div>
                <div class="absolute -right-16 -top-24 h-72 w-72 rounded-full border border-emerald-400/20"></div>
                <div class="relative grid items-center gap-5 md:grid-cols-[1fr_auto]">
                    <div class="max-w-xl">
                        <span class="inline-flex rounded-full bg-emerald-500 px-3 py-1 text-[10px] font-black tracking-wide text-slate-950">NUEVO</span>
                        <h2 class="mt-3 text-3xl font-black leading-[1.05] sm:text-4xl">LAPTOPS<br>DE <span class="text-emerald-400">ALTO RENDIMIENTO</span></h2>
                        <p class="mt-3 max-w-md text-sm text-slate-200">Potencia, velocidad y diseño en un solo equipo. Ideales para estudiar, trabajar o jugar.</p>
                        <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-lg font-black text-white/90"><span>ASUS</span><span>Lenovo</span><span>HP</span><span>acer</span><span>Dell</span></div>
                    </div>
                    <div class="hidden min-w-44 space-y-3 border-l border-white/10 pl-6 text-xs sm:block">
                        <p><span class="text-xl text-emerald-400">▣</span> <b class="ml-2">Procesadores</b><br><span class="ml-8 text-slate-300">Intel / AMD</span></p>
                        <p><span class="text-xl text-emerald-400">▧</span> <b class="ml-2">Gráficos</b><br><span class="ml-8 text-slate-300">RTX / Radeon</span></p>
                        <p><span class="text-xl text-emerald-400">♢</span> <b class="ml-2">Garantía</b><br><span class="ml-8 text-slate-300">1 año</span></p>
                        <a href="{{ route('catalogo', ['categoria' => 'laptops']) }}" class="mt-2 block rounded-full bg-emerald-500 px-4 py-3 text-center font-bold text-slate-950 transition hover:bg-emerald-400">Ver todas las laptops&nbsp; →</a>
                    </div>
                </div>
            </section>

            @if(request()->anyFilled(['q', 'categoria', 'marca', 'en_stock']))
            <!-- Pills de filtros activos -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-400 font-semibold">Filtros aplicados:</span>
                @if(request('q'))
                    <span class="px-2.5 py-1 rounded-full text-xs bg-forest-800 border border-emerald-500/30 text-emerald-300">
                        "{{ request('q') }}"
                    </span>
                @endif
                @if(request('categoria'))
                    <span class="px-2.5 py-1 rounded-full text-xs bg-forest-800 border border-emerald-500/30 text-emerald-300">
                        Categoría: {{ request('categoria') }}
                    </span>
                @endif
                @if(request('marca'))
                    <span class="px-2.5 py-1 rounded-full text-xs bg-forest-800 border border-emerald-500/30 text-emerald-300">
                        Marca: {{ request('marca') }}
                    </span>
                @endif
                @if(request('en_stock'))
                    <span class="px-2.5 py-1 rounded-full text-xs bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">
                        En Stock
                    </span>
                @endif
            </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @forelse($products as $product)
                <div class="glass-card group flex flex-col justify-between rounded-2xl border border-slate-100 p-3 transition-all hover:-translate-y-1 hover:border-emerald-400 hover:shadow-lg">
                    <div class="space-y-3">
                        <!-- Imagen -->
                        <div class="relative aspect-[4/3] w-full overflow-hidden rounded-xl bg-slate-50">
                            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105">

                            <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1">
                                @if($product->is_new)
                                    <span class="rounded-full bg-emerald-500 px-2 py-0.5 text-[9px] font-extrabold uppercase text-slate-950">Nuevo</span>
                                @endif
                                @if($product->brand)
                                    <span class="rounded-full bg-slate-950 px-2 py-0.5 text-[9px] font-bold text-white">{{ $product->brand->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- SKU & Stock -->
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="font-mono text-slate-400 font-semibold">{{ $product->sku }}</span>
                            @if($product->total_available_stock > 3)
                                <span class="px-2 py-0.5 rounded-full badge-stock-ok font-bold">En Stock</span>
                            @elseif($product->total_available_stock > 0)
                                <span class="px-2 py-0.5 rounded-full badge-stock-low font-bold">{{ $product->total_available_stock }} unidades</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full badge-stock-out font-bold">Agotado</span>
                            @endif
                        </div>

                        <!-- Nombre Laptop -->
                        <h3 class="line-clamp-2 text-sm font-black leading-tight text-slate-900 transition-colors group-hover:text-emerald-600">
                            <a href="{{ route('catalogo.detalle', $product->slug) }}">
                                {{ $product->name }}
                            </a>
                        </h3>

                        <p class="line-clamp-2 text-[11px] leading-4 text-slate-500">{{ $product->short_description ?: 'Equipo seleccionado con garantía oficial de tienda y asesoría especializada.' }}</p>

                        <!-- Especificaciones Rápidas -->
                        @if(!empty($product->technical_specs))
                        <div class="flex flex-wrap gap-1.5 border-t border-slate-100 pt-2 text-[10px] text-slate-500">
                            @if(!empty($product->technical_specs['procesador']))
                                <span class="rounded bg-slate-50 px-2 py-0.5 font-mono">{{ Str::limit($product->technical_specs['procesador'], 16) }}</span>
                            @endif
                            @if(!empty($product->technical_specs['ram']))
                                <span class="rounded bg-slate-50 px-2 py-0.5 font-mono">{{ $product->technical_specs['ram'] }}</span>
                            @endif
                            @if(!empty($product->technical_specs['almacenamiento']))
                                <span class="rounded bg-slate-50 px-2 py-0.5 font-mono">{{ $product->technical_specs['almacenamiento'] }}</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Precio y Acciones -->
                    <div class="mt-3 space-y-2 border-t border-slate-100 pt-3">
                        <div>
                            <span class="block text-[10px] font-semibold text-slate-400">Precio contado</span>
                            <span class="text-xl font-black text-emerald-600">{{ $product->formatted_price }}</span>
                        </div>

                        @php
                            $cardWaNumber = \App\Models\Setting::getWhatsappNumber();
                            $cardPdfUrl = route('catalogo.ficha-tecnica', $product->slug);
                            $cardWaText = "¡Hola! 💻 Deseo comprar esta laptop:\n"
                                        . "📌 *Producto:* {$product->name}\n"
                                        . "🏷️ *SKU:* {$product->sku}\n"
                                        . "💰 *Precio:* {$product->formatted_price}\n"
                                        . "📄 *Ficha Técnica PDF:* {$cardPdfUrl}";
                            $cardWaUrl = "https://wa.me/{$cardWaNumber}?text=" . urlencode($cardWaText);
                        @endphp
                        <a href="{{ $cardWaUrl }}" target="_blank" onclick="window.open('{{ $cardPdfUrl }}', '_blank')" class="block rounded-lg bg-emerald-500 px-3 py-2.5 text-center text-xs font-black text-slate-950 transition hover:bg-emerald-400">◉ &nbsp;Comprar por WhatsApp</a>
                        <form action="{{ route('carrito.agregar') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-center text-xs font-bold text-slate-700 transition hover:border-emerald-500 hover:bg-emerald-50">Agregar al carrito</button>
                        </form>
                        <a href="{{ route('catalogo.detalle', $product->slug) }}" class="block rounded-lg border border-emerald-500 px-3 py-2 text-center text-xs font-bold text-emerald-700 transition hover:bg-emerald-50">▧ &nbsp;Ver ficha técnica</a>
                        <a href="{{ route('catalogo.ficha-tecnica', $product->slug) }}" class="block text-center text-[11px] font-bold text-emerald-700 hover:underline">↓ Descargar PDF</a>
                    </div>
                </div>
                @empty
                <div class="col-span-3 glass-card rounded-3xl p-12 text-center text-slate-400 space-y-4">
                    <svg class="w-12 h-12 mx-auto text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <h3 class="text-base font-bold text-white">No se encontraron laptops con los filtros aplicados</h3>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">
                        Intenta modificando los términos de búsqueda o eliminando los filtros de categoría o precio.
                    </p>
                    <a href="{{ route('catalogo') }}" class="inline-block px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all">
                        Ver Todo el Catálogo
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

        </div>

    </div>

</div>
@endsection
