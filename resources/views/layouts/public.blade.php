<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portátiles Perú - Tienda Especializada en Laptops de Alta Gama')</title>
    <meta name="description" content="@yield('meta_description', 'Venta de laptops gamer, ultrabooks y workstation en Lima, Perú. Garantía oficial, precios competitivos y atención personalizada por WhatsApp.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        forest: {
                            950: '#03100B',
                            900: '#051A11',
                            850: '#072418',
                            800: '#0B3322',
                            750: '#0E3E2A',
                            700: '#145339',
                            600: '#1B6E4C',
                        },
                        brand: {
                            emerald: '#10B981',
                            neon: '#34D399',
                            light: '#A7F3D0',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #ffffff;
            color: #111111;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-image: linear-gradient(180deg, #ffffff 0%, #f7f7f7 100%);
        }

        .glass-card {
            background: #ffffff;
            border: 1px solid #d1d5db;
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.06);
        }

        .glass-card:hover {
            border-color: #10b981;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid #e5e7eb;
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(52, 211, 153, 0.4);
        }

        .badge-stock-ok {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #6ee7b7;
        }

        .badge-stock-low {
            background: rgba(245, 158, 11, 0.15);
            color: #FBBF24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-stock-out {
            background: rgba(244, 63, 94, 0.15);
            color: #FB7185;
            border: 1px solid rgba(244, 63, 94, 0.3);
        }

        /* Scrollbar estilizado */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f3f4f6;
        }
        ::-webkit-scrollbar-thumb {
            background: #059669;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #10B981;
        }

        /* Paleta pública: blanco y negro como base, verde para acciones. */
        main .text-white,
        header .text-white {
            color: #111111;
        }

        main .text-slate-200,
        main .text-slate-300,
        main .text-slate-400 {
            color: #4b5563;
        }

        main .bg-black .text-white {
            color: #ffffff;
        }

        main .bg-black .text-slate-300 {
            color: #d1d5db;
        }

        main .bg-forest-950,
        main .bg-forest-900,
        main .bg-forest-900\/80,
        main .bg-forest-900\/90,
        main .bg-forest-850,
        main .bg-forest-800 {
            background-color: #f3f4f6;
        }

        main .border-emerald-950\/60,
        main .border-emerald-950\/80 {
            border-color: #d1d5db;
        }

        header .text-slate-300,
        header .text-slate-400 {
            color: #374151;
        }

        header .bg-forest-900,
        header .bg-forest-900\/90 {
            background-color: #ffffff;
        }

        header .text-forest-950,
        main .text-forest-950 {
            color: #111111;
        }

        .site-topbar {
            background: linear-gradient(90deg, #062217 0%, #03120d 100%);
        }

        .site-header {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 1px 12px rgba(15, 23, 42, 0.06);
        }

        .site-header a,
        .site-header input {
            color: #17201c;
        }

        .site-header input::placeholder {
            color: #94a3b8;
        }

        .home-hero .text-white {
            color: #ffffff !important;
        }

        .home-hero .text-slate-200 {
            color: #e2e8f0 !important;
        }

        .theme-blue .bg-emerald-500, .theme-blue .bg-emerald-600 { background-color: #2563eb !important; }
        .theme-blue .text-emerald-400, .theme-blue .text-emerald-500, .theme-blue .text-emerald-600 { color: #2563eb !important; }
        .theme-violet .bg-emerald-500, .theme-violet .bg-emerald-600 { background-color: #7c3aed !important; }
        .theme-violet .text-emerald-400, .theme-violet .text-emerald-500, .theme-violet .text-emerald-600 { color: #7c3aed !important; }
    </style>
</head>
<li class="hidden"></li>
@php($siteTheme = \App\Models\Setting::getValue('theme_color', 'emerald'))
<body class="theme-{{ $siteTheme }} min-h-screen flex flex-col antialiased selection:bg-emerald-500 selection:text-white">

    <!-- Barra de beneficios -->
    <div class="site-topbar px-4 py-3 text-[11px] font-bold text-white sm:px-6">
        <div class="mx-auto flex max-w-[1440px] flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-x-10 gap-y-2">
                <span class="flex items-center gap-2"><span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-500 text-[11px]">✓</span>Productos originales</span>
                <span class="flex items-center gap-2"><span class="text-lg text-emerald-400">♣</span>Envíos a todo el Perú</span>
                <span class="flex items-center gap-2"><span class="grid h-5 w-5 place-items-center rounded-full bg-emerald-500 text-[11px]">✓</span>Compra segura por WhatsApp</span>
            </div>
            <div class="flex items-center gap-6">
                <a href="{{ \App\Models\Setting::getWhatsappUrl('Hola Portatiles Peru') }}" target="_blank" class="hidden items-center gap-2 text-white transition hover:text-emerald-300 sm:flex"><span class="text-base text-emerald-400">●</span>Contáctanos por WhatsApp <span>›</span></a>
                <span class="hidden h-5 w-px bg-white/20 sm:block"></span>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white hover:text-emerald-300">♟ Mi cuenta⌄</a>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-2 text-white hover:text-emerald-300">♟ Mi cuenta⌄</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Navegación principal -->
    <header class="site-header sticky top-0 z-50">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-5 px-4 py-4 sm:px-6 lg:px-8">

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group flex-shrink-0">
                <div class="flex h-11 w-14 -skew-x-12 items-center justify-center rounded-xl bg-gradient-to-br from-[#003725] via-emerald-500 to-[#b6f542] shadow-sm transition-transform group-hover:scale-105">
                    <svg class="h-6 w-7 skew-x-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xl font-black leading-tight tracking-tight text-slate-950">
                        PORTÁTILES <span class="text-emerald-500">PERÚ</span>
                    </span>
                    <span class="block text-[10px] font-bold tracking-[0.16em] text-slate-600">
                        E.I.R.L. &nbsp;|&nbsp; MOYO TECH
                    </span>
                </div>
            </a>

            <!-- Menú de Navegación -->
            <nav class="hidden items-center gap-7 text-xs font-bold xl:flex">
                <a href="{{ route('home') }}" class="border-b-[3px] pb-3 pt-3 transition-colors {{ request()->routeIs('home') ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-800 hover:text-emerald-600' }}">
                    Inicio
                </a>
                <a href="{{ route('catalogo') }}" class="text-slate-800 hover:text-emerald-600">Productos y Servicios⌄</a>
                <a href="{{ route('nosotros') }}" class="text-slate-800 hover:text-emerald-600">
                    Nosotros
                </a>
                <a href="{{ route('catalogo') }}" class="text-slate-800 hover:text-emerald-600">Catálogo</a>
                <a href="{{ route('contacto') }}" class="text-slate-800 hover:text-emerald-600">
                    Contacto
                </a>
            </nav>

            <!-- Carrito y Botón CTA Cotizar WhatsApp -->
            <div class="flex items-center gap-3">
                <form action="{{ route('catalogo') }}" method="GET" class="relative hidden lg:block">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar productos, marcas..." class="w-56 rounded-full border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-4 text-xs outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <span class="absolute left-3 top-2.5 text-slate-400">⌕</span>
                </form>
                <!-- Carrito de Compras / Solicitud -->
                <a
                    href="{{ route('carrito') }}"
                    class="relative grid h-10 w-10 place-items-center rounded-full text-slate-900 transition hover:bg-emerald-50"
                    title="Ver mi carrito de solicitud"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    @if(count(session('cart', [])) > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 text-forest-950 font-black text-[10px] flex items-center justify-center shadow-md animate-pulse">
                            {{ count(session('cart', [])) }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('login') }}" class="hidden text-xl text-slate-900 md:block" title="Mi cuenta">♙</a>
            </div>

        </div>

        <!-- Buscador móvil secundario -->
        <div class="px-4 pb-3 lg:hidden">
            <form action="{{ route('catalogo') }}" method="GET" class="w-full relative">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar laptops, marcas o especificaciones..."
                    class="w-full pl-10 pr-4 py-2 rounded-full bg-forest-900 border border-emerald-500/25 text-white placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-400"
                >
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
            </form>
        </div>
    </header>

    <!-- Contenido Principal Dinámico -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-black border-t border-black pt-12 pb-8 text-slate-400 text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-12 border-b border-emerald-950/60">

                <!-- Columna 1: Marca y Propuesta -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center text-forest-950 font-black">
                            P
                        </div>
                        <span class="font-black text-base tracking-tight text-white">
                            PORTÁTILES <span class="text-emerald-400">PERÚ</span>
                        </span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed">
                        Líderes en importación y distribución de laptops de alto rendimiento: Gaming, Creación de Contenido, Ingeniería y Corporativas en Lima y todo el territorio peruano.
                    </p>
                    <div class="pt-2 text-emerald-400 text-xs font-semibold">
                        RUC: 20608976541 &bull; Portátiles Perú S.A.C.
                    </div>
                </div>

                <!-- Columna 2: Categorías -->
                <div class="space-y-3">
                    <h4 class="text-white font-bold text-sm tracking-wide">Categorías de Laptops</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('catalogo', ['categoria' => 'laptops-gaming']) }}" class="hover:text-emerald-400 transition-colors">Laptops Gaming (RTX 40 Series)</a></li>
                        <li><a href="{{ route('catalogo', ['categoria' => 'ultrabooks']) }}" class="hover:text-emerald-400 transition-colors">Ultrabooks Delgadas & Ligeras</a></li>
                        <li><a href="{{ route('catalogo', ['categoria' => 'workstations']) }}" class="hover:text-emerald-400 transition-colors">Workstations para Render & 3D</a></li>
                        <li><a href="{{ route('catalogo', ['categoria' => 'laptops-corporativas']) }}" class="hover:text-emerald-400 transition-colors">Laptops Corporativas & Oficina</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Enlaces de Ayuda y Garantía -->
                <div class="space-y-3">
                    <h4 class="text-white font-bold text-sm tracking-wide">Atención y Garantías</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('nosotros') }}" class="hover:text-emerald-400 transition-colors">Garantía Oficial de Marca</a></li>
                        <li><a href="{{ route('contacto') }}" class="hover:text-emerald-400 transition-colors">Sedes y Horarios de Atención</a></li>
                        <li><a href="{{ route('contacto') }}" class="hover:text-emerald-400 transition-colors">Políticas de Envío en Lima y Provincias</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-emerald-400 transition-colors">Acceso a Intranet / Personal</a></li>
                    </ul>
                </div>

                <!-- Columna 4: Medios de Pago y Sedes -->
                <div class="space-y-3">
                    <h4 class="text-white font-bold text-sm tracking-wide">Sede Principal & Pagos</h4>
                    <p class="text-xs text-slate-400">
                        📍 Av. Petit Thouars 5356, Miraflores, Lima.<br>
                        📞 Central: (01) 480-1234<br>
                        📱 WhatsApp: +51 987 654 321
                    </p>
                    <div class="pt-2">
                        <span class="text-[11px] font-bold text-slate-300 block mb-2">Medios de Pago Aceptados:</span>
                        <div class="flex flex-wrap gap-2 text-[10px] font-mono">
                            <span class="px-2 py-1 rounded bg-forest-900 border border-emerald-950 text-emerald-400 font-bold">Yape</span>
                            <span class="px-2 py-1 rounded bg-forest-900 border border-emerald-950 text-sky-400 font-bold">Plin</span>
                            <span class="px-2 py-1 rounded bg-forest-900 border border-emerald-950 text-orange-400 font-bold">BCP</span>
                            <span class="px-2 py-1 rounded bg-forest-900 border border-emerald-950 text-blue-400 font-bold">BBVA</span>
                            <span class="px-2 py-1 rounded bg-forest-900 border border-emerald-950 text-slate-200 font-bold">Visa / MC</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Copyright -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>&copy; {{ date('Y') }} Portátiles Perú S.A.C. Todos los derechos reservados.</p>
                <p class="text-emerald-500 font-semibold">Sistema de Catálogo Virtual &bull; Stock e Inventario Trazable</p>
            </div>
        </div>
    </footer>

    <!-- Botón Flotante de WhatsApp para Consulta Rápida -->
    <aside aria-label="Contacto WhatsApp" class="fixed bottom-6 right-6 z-50">
        <a
            href="{{ \App\Models\Setting::getWhatsappUrl('Hola Portatiles Peru, deseo consultar por una laptop') }}"
            target="_blank"
            class="group flex items-center gap-3 px-4 py-3 rounded-full bg-emerald-500 hover:bg-emerald-400 text-forest-950 font-black text-xs shadow-2xl shadow-emerald-500/40 hover:scale-105 transition-all"
        >
            <svg class="w-5 h-5 text-forest-950 fill-current" viewBox="0 0 24 24">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
            </svg>
            <span class="hidden sm:inline">¿Dudas? Chatea con un asesor</span>
            <span class="sm:hidden">WhatsApp</span>
        </a>
    </aside>

</body>
</html>
