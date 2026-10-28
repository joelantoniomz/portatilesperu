@extends('layouts.public')

@section('title', $product->name . ' - Portátiles Perú')
@section('meta_description', 'Compra ' . $product->name . ' al mejor precio en Lima, Perú. SKU: ' . $product->sku . '. Stock disponible y garantía oficial.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">

    <!-- Breadcrumb / Migas de Pan -->
    <nav class="flex items-center gap-2 text-xs text-slate-400 font-medium">
        <a href="{{ route('home') }}" class="hover:text-emerald-400 transition-colors">Inicio</a>
        <span>/</span>
        <a href="{{ route('catalogo') }}" class="hover:text-emerald-400 transition-colors">Catálogo</a>
        <span>/</span>
        <a href="{{ route('catalogo', ['categoria' => $product->category->slug]) }}" class="hover:text-emerald-400 transition-colors">
            {{ $product->category->name }}
        </a>
        <span>/</span>
        <span class="text-slate-200 truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <!-- Ficha Principal en 2 Columnas -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">

        <!-- Columna Izquierda: Galería de Imágenes -->
        <div class="lg:col-span-6 space-y-4 sticky top-24">
            <div class="glass-card rounded-3xl p-4 border border-emerald-500/25 shadow-2xl relative overflow-hidden">
                <div class="aspect-video sm:aspect-square w-full rounded-2xl overflow-hidden bg-forest-900 flex items-center justify-center border border-emerald-500/10">
                    <img id="mainGalleryImage" src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover transition-all duration-300">
                </div>

                <!-- Badges de Estado sobre imagen -->
                <div class="absolute top-6 left-6 flex flex-wrap gap-1.5">
                    @if($product->is_new)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-500 text-forest-950 shadow-md">Nuevo en Caja</span>
                    @endif
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-forest-950/80 text-emerald-400 border border-emerald-500/30">
                        {{ $product->brand?->name }}
                    </span>
                </div>
            </div>

            <!-- Miniaturas de la Galería -->
            @if($product->images->count() > 1)
            <div class="flex items-center gap-3 overflow-x-auto pb-2">
                @foreach($product->images as $img)
                <button
                    type="button"
                    onclick="switchMainImage('{{ $img->url }}', this)"
                    class="gallery-thumb w-20 h-20 rounded-2xl overflow-hidden border-2 {{ $loop->first ? 'border-emerald-400 scale-105' : 'border-emerald-950 opacity-70' }} hover:opacity-100 transition-all flex-shrink-0 bg-forest-900"
                >
                    <img src="{{ $img->url }}" alt="Miniatura" class="w-full h-full object-cover">
                </button>
                @endforeach
            </div>
            @endif

            <!-- Garantía de tienda y descarga de ficha -->
            <div class="glass-card rounded-2xl p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">Garantía oficial de tienda</h4>
                        <p class="text-[11px] text-slate-400">Cobertura de 12 a 24 meses y soporte especializado</p>
                    </div>
                </div>

                <a href="{{ route('catalogo.ficha-tecnica', $product->slug) }}" class="rounded-xl border border-emerald-500/25 bg-forest-900 px-3 py-1.5 text-xs font-bold text-emerald-400 transition-colors hover:bg-forest-800">↓ Descargar ficha PDF</a>
            </div>
        </div>

        <!-- Columna Derecha: Especificaciones, Precio y Compra WhatsApp -->
        <div class="lg:col-span-6 space-y-6">

            <!-- Encabezado de la Laptop -->
            <div class="space-y-3 pb-6 border-b border-emerald-950">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-lg bg-forest-900 text-emerald-400 font-mono text-xs font-bold border border-emerald-500/25">
                        SKU: {{ $product->sku }}
                    </span>
                    <span class="text-xs font-semibold text-slate-400">
                        {{ $product->category->name }}
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-white leading-tight">
                    {{ $product->name }}
                </h1>

                @if($product->short_description)
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    {{ $product->short_description }}
                </p>
                @endif
            </div>

            <!-- Bloque de Precio y Stock Global -->
            <div class="glass-card rounded-3xl p-6 border border-emerald-500/25 space-y-4">
                <div class="flex items-baseline justify-between">
                    <div>
                        <span class="text-xs uppercase font-bold text-slate-400 block">Precio Contado (Inc. IGV)</span>
                        <div class="flex items-baseline gap-3 mt-0.5">
                            <span class="text-3xl sm:text-4xl font-black text-emerald-400 tracking-tight">
                                {{ $product->formatted_price }}
                            </span>
                            @if($product->currency === 'PEN')
                                <span class="text-xs font-mono text-slate-400">~ ${{ number_format($product->price / 3.75, 2) }} USD</span>
                            @endif
                        </div>
                    </div>

                    <!-- Indicador de Disponibilidad Global -->
                    <div class="text-right">
                        @if($globalStock['total_available'] > 3)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full badge-stock-ok text-xs font-black">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>Disponible para Entrega</span>
                            </span>
                        @elseif($globalStock['total_available'] > 0)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full badge-stock-low text-xs font-black">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                <span>¡Últimas {{ $globalStock['total_available'] }} unidades!</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full badge-stock-out text-xs font-black">
                                <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                <span>Bajo Pedido (Consulte tiempo)</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Stock por Almacén / Tienda -->
                <div class="pt-3 border-t border-emerald-950/80">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Disponibilidad en Tiendas Lima:</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($globalStock['by_warehouse'] as $whStock)
                        <div class="p-2.5 rounded-xl bg-forest-900 border border-emerald-500/15 text-xs">
                            <span class="font-bold text-slate-200 block text-[11px] truncate">{{ $whStock['warehouse_name'] }}</span>
                            <span class="text-emerald-400 font-mono text-[10px] font-bold">
                                {{ $whStock['qty_available'] }} disp.
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Acciones Principales: Agregar al Carrito y Comprar Ahora -->
                <div class="pt-3 space-y-3">
                    <form action="{{ route('carrito.agregar') }}" method="POST" class="space-y-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="flex items-center gap-3">
                            <div class="w-28 flex items-center gap-1.5 bg-forest-900 border border-emerald-500/30 rounded-2xl px-3 py-2.5">
                                <label for="product_qty" class="text-xs text-slate-400 font-bold">Cant:</label>
                                <select id="product_qty" name="quantity" class="bg-transparent text-white font-bold text-xs focus:outline-none w-full cursor-pointer">
                                    @for($i = 1; $i <= min(10, max(1, $globalStock['total_available'])); $i++)
                                        <option value="{{ $i }}" class="bg-forest-900 text-white">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <a href="https://wa.me/51987654321?text=Hola%20Portatiles%20Peru,%20deseo%20comprar%20{{ urlencode($product->name) }}" target="_blank" class="flex flex-1 items-center justify-center gap-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-500 px-6 py-3.5 text-sm font-black text-white shadow-xl shadow-emerald-500/30 transition-all hover:from-emerald-500 hover:to-teal-400">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                                <span>Comprar por WhatsApp</span>
                            </a>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                class="flex-1 py-3 px-4 rounded-2xl bg-forest-900 hover:bg-forest-850 text-emerald-400 font-bold text-xs border border-emerald-500/30 hover:border-emerald-400 transition-all flex items-center justify-center gap-2"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Agregar al carrito</span>
                            </button>
                        </div>
                    </form>
                </div>
                <p class="text-[11px] text-slate-400 text-center sm:text-left">
                    ⚡ Se reservará el stock preventivo por 24h &bull; Generación de código único antes de WhatsApp
                </p>
            </div>

            <!-- Ficha Técnica Completa -->
            <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-4">
                <h3 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    <span>Especificaciones Técnicas Detalladas</span>
                </h3>

                <div class="divide-y divide-emerald-950/80 text-xs">
                    @if(!empty($product->technical_specs))
                        @foreach($product->technical_specs as $key => $value)
                        <div class="py-2.5 flex items-start justify-between gap-4">
                            <span class="text-slate-400 font-semibold capitalize">{{ str_replace('_', ' ', $key) }}:</span>
                            <span class="text-white font-mono text-right font-bold">{{ is_array($value) ? implode(', ', $value) : $value }}</span>
                        </div>
                        @endforeach
                    @endif

                    @if($product->weight)
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <span class="text-slate-400 font-semibold">Peso:</span>
                        <span class="text-white font-mono font-bold">{{ $product->weight }} kg</span>
                    </div>
                    @endif

                    @if($product->dimensions)
                    <div class="py-2.5 flex items-start justify-between gap-4">
                        <span class="text-slate-400 font-semibold">Dimensiones:</span>
                        <span class="text-white font-mono font-bold">{{ $product->dimensions }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Descripción Completa si existe -->
            @if($product->description)
            <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-3">
                <h3 class="text-sm font-black text-white uppercase tracking-wider">Descripción del Producto</h3>
                <div class="text-xs text-slate-300 leading-relaxed space-y-2 whitespace-pre-line">
                    {{ $product->description }}
                </div>
            </div>
            @endif

        </div>

    </div>

    <!-- Laptops Similares Recomendadas -->
    @if($relatedProducts->isNotEmpty())
    <section class="pt-8 border-t border-emerald-950">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-black text-white tracking-tight">Otras Laptops Recomendadas</h3>
                <p class="text-slate-400 text-xs mt-0.5">Modelos similares en categoría o marca</p>
            </div>
            <a href="{{ route('catalogo') }}" class="text-xs font-bold text-emerald-400 hover:underline">
                Ver Todo &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($relatedProducts as $rel)
            <div class="glass-card rounded-3xl p-4 flex flex-col justify-between group hover:border-emerald-500/40 transition-all">
                <div class="space-y-3">
                    <div class="aspect-video w-full rounded-2xl overflow-hidden bg-forest-900 border border-emerald-500/15">
                        <img src="{{ $rel->primary_image_url }}" alt="{{ $rel->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                    <span class="font-mono text-[10px] text-slate-400 font-semibold block">{{ $rel->sku }}</span>
                    <h4 class="font-bold text-white text-xs line-clamp-2 leading-tight group-hover:text-emerald-400 transition-colors">
                        <a href="{{ route('catalogo.detalle', $rel->slug) }}">{{ $rel->name }}</a>
                    </h4>
                </div>
                <div class="pt-3 mt-3 border-t border-emerald-950 flex items-center justify-between">
                    <span class="text-sm font-black text-emerald-400">{{ $rel->formatted_price }}</span>
                    <a href="{{ route('catalogo.detalle', $rel->slug) }}" class="p-2 rounded-xl bg-emerald-500/10 hover:bg-emerald-500 text-emerald-400 hover:text-forest-950 transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

</div>

<script>
function switchMainImage(url, button) {
    const mainImg = document.getElementById('mainGalleryImage');
    mainImg.style.opacity = '0.4';
    setTimeout(() => {
        mainImg.src = url;
        mainImg.style.opacity = '1';
    }, 150);

    document.querySelectorAll('.gallery-thumb').forEach(btn => {
        btn.classList.remove('border-emerald-400', 'scale-105');
        btn.classList.add('border-emerald-950', 'opacity-70');
    });

    button.classList.add('border-emerald-400', 'scale-105');
    button.classList.remove('border-emerald-950', 'opacity-70');
}
</script>
@endsection
