@extends('layouts.public')

@section('title', 'Portátiles Perú - Lo Último en Laptops de Alta Gama, Gamer y Profesionales')

@section('content')
<div class="space-y-12 pb-16">

    <!-- 1. Hero Carousel Interactivo (Estilo del Diseño de Referencia) -->
    <section class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8 pt-6">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-[#0A0D14] text-white shadow-2xl border border-slate-800">
            <!-- Glows decorativos de fondo -->
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-indigo-600/30 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-violet-600/25 blur-3xl pointer-events-none"></div>

            <!-- Diapositivas del Carrusel -->
            <div id="heroCarousel" class="relative min-h-[460px] sm:min-h-[500px] flex items-center">
                <!-- Slide 1: Gamer & Potencia -->
                <div class="hero-slide active w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center p-8 sm:p-12 lg:p-16 transition-opacity duration-500">
                    <div class="lg:col-span-7 space-y-6 z-10">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/20 border border-indigo-400/40 text-indigo-300 text-xs font-bold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-indigo-400 animate-pulse"></span>
                            <span>{{ \App\Models\Setting::getValue('home_hero_badge', 'Nueva Generación 2026') }}</span>
                        </div>
                        <h1 class="text-4xl sm:text-6xl font-black heading-font tracking-tight text-white leading-tight">
                            {{ \App\Models\Setting::getValue('home_hero_title', 'Future Technology Today.') }}
                        </h1>
                        <p class="text-slate-300 text-sm sm:text-base max-w-xl leading-relaxed">
                            {{ \App\Models\Setting::getValue('home_hero_subtitle', 'Descubre las laptops más avanzadas del mercado con procesadores Intel Core Ultra, AMD Ryzen 9000 y gráficos dedicados NVIDIA RTX 4000. Potencia pura para gaming y trabajo exigente.') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <a href="{{ route('catalogo') }}" class="px-8 py-3.5 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-indigo-600/30 transition-all hover:scale-105 flex items-center gap-2">
                                <span>Explorar Catálogo</span>
                                <span>→</span>
                            </a>
                            <a href="{{ \App\Models\Setting::getWhatsappUrl('Hola Portátiles Perú, deseo cotizar una laptop de alta gama') }}" target="_blank" class="px-6 py-3.5 rounded-full bg-slate-800/80 hover:bg-slate-700 border border-slate-700 text-white font-bold text-xs sm:text-sm transition-all flex items-center gap-2">
                                <span>Cotizar por WhatsApp</span>
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-5 flex justify-center z-10">
                        <div class="relative group">
                            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-violet-600 rounded-3xl blur opacity-30 group-hover:opacity-60 transition duration-500"></div>
                            @php($customHeroImg = \App\Models\Setting::getValue('home_hero_image'))
                            @if(!empty($customHeroImg))
                                <img src="{{ $customHeroImg }}" alt="Laptop Principal" class="relative w-80 sm:w-96 rounded-2xl object-contain drop-shadow-2xl">
                            @elseif($featuredProducts->first())
                                <img src="{{ $featuredProducts->first()->primary_image_url }}" alt="Laptop Principal" class="relative w-80 sm:w-96 rounded-2xl object-contain drop-shadow-2xl">
                            @else
                                <div class="w-80 h-64 bg-slate-800/80 rounded-2xl flex items-center justify-center text-4xl">💻</div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Ultrabooks & Diseño -->
                <div class="hero-slide hidden w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-center p-8 sm:p-12 lg:p-16 transition-opacity duration-500">
                    <div class="lg:col-span-7 space-y-6 z-10">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-violet-500/20 border border-violet-400/40 text-violet-300 text-xs font-bold uppercase tracking-wider">
                            <span>{{ \App\Models\Setting::getValue('home_slide2_badge', 'Especial Creadores & Diseño') }}</span>
                        </div>
                        <h2 class="text-4xl sm:text-6xl font-black heading-font tracking-tight text-white leading-tight">
                            {{ \App\Models\Setting::getValue('home_slide2_title', 'Ultra Ligeras. Máxima Autonomía.') }}
                        </h2>
                        <p class="text-slate-300 text-sm sm:text-base max-w-xl leading-relaxed">
                            {{ \App\Models\Setting::getValue('home_slide2_subtitle', 'Pantallas OLED de alta fidelidad de color, chasis ultraligeros de aluminio aeroespacial y baterías de hasta 18 horas para que lleves tu creatividad a cualquier lugar.') }}
                        </p>
                        <div class="flex flex-wrap items-center gap-4 pt-2">
                            <a href="{{ route('catalogo') }}" class="px-8 py-3.5 rounded-full bg-violet-600 hover:bg-violet-500 text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-violet-600/30 transition-all hover:scale-105">
                                <span>Ver Ultrabooks</span>
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-5 flex justify-center z-10">
                        @if($featuredProducts->skip(1)->first())
                            <img src="{{ $featuredProducts->skip(1)->first()->primary_image_url }}" alt="Laptop Creadores" class="relative w-80 sm:w-96 rounded-2xl object-contain drop-shadow-2xl">
                        @else
                            <div class="w-80 h-64 bg-slate-800/80 rounded-2xl flex items-center justify-center text-4xl">⚡</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Controles del Carrusel (Flechas y Paginación como en la imagen) -->
            <div class="absolute bottom-6 left-8 sm:left-12 flex items-center gap-4 z-20">
                <button type="button" onclick="prevSlide()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center text-xs transition-colors">
                    ‹
                </button>
                <div class="flex items-center gap-2 text-xs font-mono font-bold text-slate-400">
                    <span id="slideIndicatorCurrent" class="text-white">01</span>
                    <div class="w-12 h-1 bg-slate-800 rounded-full overflow-hidden">
                        <div id="slideProgressBar" class="w-1/2 h-full bg-indigo-500 transition-all duration-300"></div>
                    </div>
                    <span>02</span>
                </div>
                <button type="button" onclick="nextSlide()" class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white flex items-center justify-center text-xs transition-colors">
                    ›
                </button>
            </div>

            <!-- Trust Badges inferiores dentro del Banner Hero -->
            <div class="border-t border-slate-800/80 bg-slate-950/40 px-8 sm:px-12 py-3.5 hidden sm:grid grid-cols-3 gap-4 text-xs font-medium text-slate-300">
                <div class="flex items-center gap-2.5">
                    <span class="text-indigo-400 font-bold">✓</span>
                    <span>100% Productos Originales con Garantía</span>
                </div>
                <div class="flex items-center gap-2.5 justify-center">
                    <span class="text-indigo-400 font-bold">🚚</span>
                    <span>Envíos Seguros a Todo el Perú</span>
                </div>
                <div class="flex items-center gap-2.5 justify-end">
                    <span class="text-emerald-400 font-bold">●</span>
                    <span>Atención y Venta Directa por WhatsApp</span>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Strip de Categorías en Cápsula (Estilo Iconos Redondeados de Referencia) -->
    <section class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3 sm:gap-4">
            @forelse($categories as $category)
            <a href="{{ route('catalogo', ['categoria' => $category->slug]) }}" class="group p-4 rounded-3xl bg-white border border-slate-200/80 hover:border-indigo-400 shadow-sm hover:shadow-md transition-all text-center flex flex-col items-center justify-center gap-2.5">
                <div class="w-12 h-12 rounded-2xl bg-slate-50 group-hover:bg-indigo-50 border border-slate-100 flex items-center justify-center text-xl transition-colors">
                    @if(str_contains(strtolower($category->name), 'gamer')) 🎮
                    @elseif(str_contains(strtolower($category->name), 'cread') || str_contains(strtolower($category->name), 'diseñ')) 🎨
                    @elseif(str_contains(strtolower($category->name), 'ultra')) ⚡
                    @elseif(str_contains(strtolower($category->name), 'corpo') || str_contains(strtolower($category->name), 'traba')) 💼
                    @else 💻
                    @endif
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $category->name }}</h3>
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Explorar</span>
                </div>
            </a>
            @empty
            <div class="col-span-full p-4 rounded-2xl bg-white text-center text-xs text-slate-400">Categorías no disponibles</div>
            @endforelse
            <a href="{{ route('catalogo') }}" class="group p-4 rounded-3xl bg-slate-900 text-white shadow-sm hover:bg-slate-800 transition-all text-center flex flex-col items-center justify-center gap-2.5">
                <div class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center text-xl">
                    📁
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white">Ver Todo</h3>
                    <span class="text-[10px] text-indigo-300 mt-0.5 block">Catálogo Completo</span>
                </div>
            </a>
        </div>
    </section>

    <!-- 3. Bento Grid Promocional (Ofertas Destacadas como en la Imagen) -->
    <section class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
            <!-- Bento 1: MacBook / Alta Gama (Grande 5 cols) -->
            <div class="md:col-span-5 rounded-[2rem] bg-gradient-to-br from-slate-900 to-[#111827] text-white p-7 sm:p-8 flex flex-col justify-between relative overflow-hidden shadow-lg border border-slate-800">
                <div class="space-y-3 z-10">
                    <span class="px-3 py-1 rounded-full bg-white/10 text-[10px] font-extrabold uppercase tracking-wider text-slate-300">Oferta Limitada</span>
                    <h3 class="text-2xl font-black heading-font text-white leading-tight">MacBook Pro & Air con Apple Silicon</h3>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-xs">Rendimiento extremo con eficiencia energética para desarrolladores y creativos.</p>
                </div>
                <div class="pt-6 z-10">
                    <a href="{{ route('catalogo', ['marca' => 'apple']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-slate-900 font-bold text-xs hover:bg-slate-100 transition-colors">
                        <span>Ver Modelos</span> →
                    </a>
                </div>
                <div class="absolute -right-8 -bottom-8 w-48 h-48 rounded-full bg-indigo-600/20 blur-2xl"></div>
            </div>

            <!-- Bento 2: Mega Deal Gamer (Púrpura 4 cols) -->
            <div class="md:col-span-4 rounded-[2rem] bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-700 text-white p-7 sm:p-8 flex flex-col justify-between relative overflow-hidden shadow-lg">
                <div class="space-y-3 z-10">
                    <span class="px-3 py-1 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider text-white">Mega Deal</span>
                    <h3 class="text-2xl font-black heading-font text-white leading-tight">Laptops Gamer hasta 25% OFF</h3>
                    <p class="text-xs text-indigo-100 leading-relaxed">Lenovo Legion, ASUS ROG y TUF con tarjetas gráficas RTX Serie 40.</p>
                </div>
                <div class="pt-6 z-10">
                    <a href="{{ route('catalogo') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-slate-900 text-white font-bold text-xs hover:bg-black transition-colors">
                        <span>Ver Ofertas Gamer</span> →
                    </a>
                </div>
                <div class="absolute -right-6 -bottom-6 w-40 h-40 rounded-full bg-white/10 blur-xl"></div>
            </div>

            <!-- Bento 3: Workstations & Trabajo (3 cols) -->
            <div class="md:col-span-3 rounded-[2rem] bg-gradient-to-br from-emerald-900 via-slate-900 to-slate-900 text-white p-7 sm:p-8 flex flex-col justify-between relative overflow-hidden shadow-lg border border-slate-800">
                <div class="space-y-3 z-10">
                    <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[10px] font-black uppercase tracking-wider">Empresas</span>
                    <h3 class="text-xl font-black heading-font text-white leading-tight">
                        {{ \App\Models\Setting::getValue('home_bento_corp_title', 'Equipos Corporativos') }}
                    </h3>
                    <p class="text-xs text-slate-400">
                        {{ \App\Models\Setting::getValue('home_bento_corp_desc', 'ThinkPad, Dell Latitude y HP ProBook con Windows 11 Pro.') }}
                    </p>
                </div>
                <div class="pt-6 z-10">
                    <a href="{{ route('catalogo') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors">
                        <span>Ver Equipos</span> →
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Flash Deals / Ofertas Destacadas con Contador Regresivo -->
    <section class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200">
            <div class="flex items-center gap-4">
                <h2 class="text-2xl font-black heading-font text-slate-900 tracking-tight">Flash Deals</h2>
                <!-- Contador Regresivo -->
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-mono font-bold">
                    <span>⚡ Termina en:</span>
                    <span id="dealCountdownHours">08</span>:<span id="dealCountdownMinutes">45</span>:<span id="dealCountdownSeconds">20</span>
                </div>
            </div>
            <a href="{{ route('catalogo') }}" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                <span>Ver todas las ofertas</span> →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($featuredProducts as $product)
            <div class="group rounded-3xl bg-white p-5 border border-slate-200/90 hover:border-indigo-400 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <!-- Miniatura + Badges -->
                    <div class="relative aspect-video rounded-2xl bg-slate-50 overflow-hidden flex items-center justify-center p-3 mb-4">
                        <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300">
                        <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-rose-600 text-white text-[10px] font-black uppercase">
                            Oferta
                        </span>
                        @if($product->brand)
                        <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-md bg-slate-900/80 backdrop-blur text-white text-[9px] font-bold">
                            {{ $product->brand->name }}
                        </span>
                        @endif
                    </div>

                    <!-- Datos del Producto -->
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-mono text-slate-400 font-bold block">{{ $product->sku }}</span>
                        <h3 class="text-sm font-extrabold text-slate-900 leading-snug line-clamp-2 group-hover:text-indigo-600 transition-colors">
                            <a href="{{ route('catalogo.detalle', $product->slug) }}">{{ $product->name }}</a>
                        </h3>
                        <p class="text-[11px] text-slate-500 line-clamp-1">
                            {{ $product->short_description ?: 'Laptop de alta gama con garantía oficial.' }}
                        </p>
                    </div>
                </div>

                <!-- Precios y Acciones Rápidas -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <div>
                        <span class="text-[10px] text-slate-400 block font-medium">Precio oferta:</span>
                        <span class="text-lg font-black text-slate-900">{{ $product->formatted_price }}</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ \App\Models\Setting::getWhatsappUrl("Hola Portátiles Perú, deseo comprar la laptop {$product->name} (SKU: {$product->sku}) por {$product->formatted_price}") }}" target="_blank" class="p-2.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-600 hover:text-white transition-colors" title="Comprar por WhatsApp">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        </a>
                        <a href="{{ route('catalogo.detalle', $product->slug) }}" class="p-2.5 rounded-xl bg-slate-100 hover:bg-indigo-600 hover:text-white text-slate-700 transition-colors" title="Ver Ficha Técnica">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full p-8 rounded-3xl bg-white text-center text-sm text-slate-500">
                Pronto tendremos nuevos productos destacados disponibles.
            </div>
            @endforelse
        </div>
    </section>

    <!-- 5. Marcas Autorizadas con Logotipos e Interacción -->
    <section class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-white border border-slate-200/90 p-8 shadow-sm">
            <div class="text-center max-w-xl mx-auto mb-6">
                <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-widest">Alianzas Oficiales</span>
                <h3 class="text-xl font-black heading-font text-slate-900 mt-1">Marcas Líderes en Computación</h3>
                <p class="text-xs text-slate-500 mt-1">Garantía oficial y soporte técnico directo en cada equipo.</p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-10">
                @foreach(['Lenovo', 'ASUS', 'Dell', 'HP', 'Apple', 'Acer', 'MSI'] as $brandName)
                <a href="{{ route('catalogo', ['marca' => strtolower($brandName)]) }}" class="px-6 py-3 rounded-2xl bg-slate-50 hover:bg-indigo-50 border border-slate-100 hover:border-indigo-200 text-slate-800 hover:text-indigo-600 font-black heading-font text-lg uppercase tracking-wider transition-all hover:scale-105">
                    {{ $brandName }}
                </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 6. Banner de Ciberseguridad & Garantías (Normativas de Seguridad) -->
    <section class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8">
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-8 sm:p-10 border border-slate-800">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center md:text-left">
                <div class="space-y-1">
                    <span class="text-2xl">🔒</span>
                    <h4 class="text-sm font-bold text-white">Transacciones 100% Seguras</h4>
                    <p class="text-xs text-slate-400">Cifrado SSL/TLS 1.3 y protección estricta de datos personales.</p>
                </div>
                <div class="space-y-1">
                    <span class="text-2xl">🛡️</span>
                    <h4 class="text-sm font-bold text-white">Garantía Oficial Perú</h4>
                    <p class="text-xs text-slate-400">12 meses de garantía directa con centros de servicio técnico autorizados.</p>
                </div>
                <div class="space-y-1">
                    <span class="text-2xl">📦</span>
                    <h4 class="text-sm font-bold text-white">Stock Físico Verificado</h4>
                    <p class="text-xs text-slate-400">Protección contra sobreventa y disponibilidad garantizada en Lima.</p>
                </div>
                <div class="space-y-1">
                    <span class="text-2xl">💬</span>
                    <h4 class="text-sm font-bold text-white">Canal WhatsApp Oficial</h4>
                    <p class="text-xs text-slate-400">Ficha técnica PDF directa y seguimiento de envío con asesores reales.</p>
                </div>
            </div>
        </div>
    </section>

</div>

<!-- Scripts de Interacción del Carrusel y Contador Flash Deals -->
<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    const totalSlides = slides.length;

    function showSlide(index) {
        if (totalSlides === 0) return;
        slides.forEach((s, i) => {
            if (i === index) {
                s.classList.remove('hidden');
            } else {
                s.classList.add('hidden');
            }
        });
        currentSlide = index;
        const currentLabel = document.getElementById('slideIndicatorCurrent');
        const progressBar = document.getElementById('slideProgressBar');
        if (currentLabel) currentLabel.textContent = `0${currentSlide + 1}`;
        if (progressBar) progressBar.style.width = `${((currentSlide + 1) / totalSlides) * 100}%`;
    }

    function nextSlide() {
        showSlide((currentSlide + 1) % totalSlides);
    }

    function prevSlide() {
        showSlide((currentSlide - 1 + totalSlides) % totalSlides);
    }

    // Auto-avance del carrusel cada 6 segundos
    setInterval(() => {
        nextSlide();
    }, 6000);

    // Contador Regresivo Flash Deals
    let totalSeconds = 8 * 3600 + 45 * 60 + 20;
    setInterval(() => {
        if (totalSeconds > 0) {
            totalSeconds--;
            const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
            const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
            const s = String(totalSeconds % 60).padStart(2, '0');
            const elH = document.getElementById('dealCountdownHours');
            const elM = document.getElementById('dealCountdownMinutes');
            const elS = document.getElementById('dealCountdownSeconds');
            if (elH) elH.textContent = h;
            if (elM) elM.textContent = m;
            if (elS) elS.textContent = s;
        }
    }, 1000);
</script>
@endsection
