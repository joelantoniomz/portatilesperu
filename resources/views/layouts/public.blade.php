<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portátiles Perú - Tienda Especializada en Laptops de Alta Gama')</title>
    <meta name="description" content="@yield('meta_description', 'Venta de laptops gamer, ultrabooks y workstation en Lima, Perú. Marcas autorizadas, garantía oficial de 12 meses y compra protegida por WhatsApp.')">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Outfit"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        brand: {
                            purple: '#6366F1',
                            violet: '#7C3AED',
                            accent: '#4F46E5',
                            emerald: '#059669',
                            dark: '#0F172A',
                            surface: '#F8FAFC',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #F8FAFC;
            color: #0F172A;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .heading-font {
            font-family: 'Outfit', sans-serif;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid #E2E8F0;
        }

        .badge-discount {
            background: #EF4444;
            color: #ffffff;
            font-weight: 800;
        }

        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 9999px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-600 selection:text-white">

    <!-- 1. Top Bar Superior (Estilo del Diseño de Referencia) -->
    <div class="bg-[#0A0D14] text-white py-2 px-4 sm:px-6 text-xs border-b border-slate-800">
        <div class="mx-auto max-w-[1440px] flex flex-wrap items-center justify-between gap-3 font-medium">
            <div class="flex items-center gap-4 text-slate-300">
                <span class="flex items-center gap-1.5 font-bold text-white">
                    🇵🇪 Perú (PEN S/)
                </span>
                <span class="hidden sm:inline text-slate-500">|</span>
                <span class="hidden sm:flex items-center gap-1.5 text-slate-300">
                    🚚 <strong>Envíos Inmediatos</strong> a todo el Perú en laptops seleccionadas
                </span>
            </div>

            <div class="flex items-center gap-5 text-slate-300 text-[11px]">
                <a href="{{ route('catalogo') }}" class="hover:text-indigo-400 transition-colors">Catálogo de Laptops</a>
                <a href="{{ route('contacto') }}" class="hover:text-indigo-400 transition-colors">Soporte Oficial</a>
                <span class="text-slate-700">|</span>
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="text-indigo-400 font-bold hover:underline flex items-center gap-1">
                        <span>Panel Admin</span> →
                    </a>
                @else
                    <a href="{{ route('login') }}" class="text-slate-300 hover:text-white flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                        <span>Iniciar Sesión</span>
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- 2. Navbar Principal -->
    <header class="glass-header sticky top-0 z-50 transition-all">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between gap-6">

            <!-- Logo de la Tienda -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 flex-shrink-0 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xl font-black heading-font tracking-tight text-slate-900 leading-none">
                        PORTÁTILES <span class="text-indigo-600">PERÚ</span>
                    </span>
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        High-End Laptops & Workstations
                    </span>
                </div>
            </a>

            <!-- Navegación Central -->
            <nav class="hidden lg:flex items-center gap-7 text-xs font-bold text-slate-700">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-indigo-600 font-extrabold' : 'hover:text-indigo-600' }} transition-colors">
                    Inicio
                </a>
                <a href="{{ route('catalogo') }}" class="{{ request()->routeIs('catalogo') ? 'text-indigo-600 font-extrabold' : 'hover:text-indigo-600' }} transition-colors">
                    Catálogo Completo
                </a>
                <a href="{{ route('catalogo', ['marca' => 'lenovo']) }}" class="hover:text-indigo-600 transition-colors">
                    Lenovo
                </a>
                <a href="{{ route('catalogo', ['marca' => 'asus']) }}" class="hover:text-indigo-600 transition-colors">
                    ASUS Gamer
                </a>
                <a href="{{ route('catalogo', ['marca' => 'apple']) }}" class="hover:text-indigo-600 transition-colors">
                    Apple Mac
                </a>
                <a href="{{ route('nosotros') }}" class="hover:text-indigo-600 transition-colors">
                    Nosotros
                </a>
                <a href="{{ route('contacto') }}" class="hover:text-indigo-600 transition-colors">
                    Contacto
                </a>
            </nav>

            <!-- Buscador + Acciones -->
            <div class="flex items-center gap-3">
                <form action="{{ route('catalogo') }}" method="GET" class="relative hidden sm:block w-56 xl:w-72">
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ request('q') }}" 
                        placeholder="Buscar laptops, i7, RTX 4060..." 
                        class="w-full bg-slate-100/80 hover:bg-slate-100 focus:bg-white text-xs text-slate-800 placeholder-slate-400 pl-9 pr-4 py-2.5 rounded-full border border-slate-200 outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                </form>

                <!-- Botón Carrito de Cotización -->
                <a href="{{ route('carrito') }}" class="relative p-2.5 rounded-2xl bg-white border border-slate-200 hover:border-indigo-400 text-slate-800 hover:text-indigo-600 shadow-xs transition-all flex items-center justify-center" title="Ver carrito">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    @if(count(session('cart', [])) > 0)
                        <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-indigo-600 text-white font-black text-[10px] flex items-center justify-center shadow-md animate-pulse">
                            {{ count(session('cart', [])) }}
                        </span>
                    @endif
                </a>

                <!-- CTA WhatsApp Rápido -->
                <a href="{{ \App\Models\Setting::getWhatsappUrl('Hola Portátiles Perú, deseo cotizar una laptop') }}" target="_blank" class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all hover:scale-[1.02]">
                    <span>WhatsApp</span>
                </a>
            </div>

        </div>
    </header>

    <!-- Contenido Dinámico -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer Moderno en Tonos Oscuros con Métodos de Pago -->
    <footer class="bg-[#0A0D14] text-white border-t border-slate-800/80 pt-14 pb-8">
        <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8">
                <!-- Columna Empresa -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-bold">
                            💻
                        </div>
                        <span class="text-lg font-black heading-font tracking-tight text-white">
                            PORTÁTILES <span class="text-indigo-400">PERÚ</span>
                        </span>
                    </div>
                    <p class="text-slate-400 text-xs leading-relaxed max-w-sm">
                        Especialistas en importación, distribución y venta de laptops de alta gama, equipos gamer, ultrabooks y estaciones de trabajo con garantía oficial en Perú.
                    </p>
                    <div class="pt-2 flex items-center gap-4 text-xs text-slate-400">
                        <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Atención Activa</span>
                        <span>Av. Garcilaso de la Vega, Lima</span>
                    </div>
                </div>

                <!-- Categorías -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Laptops</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="{{ route('catalogo') }}" class="hover:text-white transition-colors">Laptops Gamer</a></li>
                        <li><a href="{{ route('catalogo') }}" class="hover:text-white transition-colors">Ultrabooks & Ligeras</a></li>
                        <li><a href="{{ route('catalogo') }}" class="hover:text-white transition-colors">Creadores y Diseño</a></li>
                        <li><a href="{{ route('catalogo') }}" class="hover:text-white transition-colors">Empresariales / Trabajo</a></li>
                    </ul>
                </div>

                <!-- Marcas -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Marcas Oficiales</h4>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li><a href="{{ route('catalogo', ['marca' => 'lenovo']) }}" class="hover:text-white transition-colors">Lenovo Legion & ThinkPad</a></li>
                        <li><a href="{{ route('catalogo', ['marca' => 'asus']) }}" class="hover:text-white transition-colors">ASUS ROG & TUF Gaming</a></li>
                        <li><a href="{{ route('catalogo', ['marca' => 'dell']) }}" class="hover:text-white transition-colors">Dell XPS & Inspiron</a></li>
                        <li><a href="{{ route('catalogo', ['marca' => 'apple']) }}" class="hover:text-white transition-colors">Apple MacBook Pro & Air</a></li>
                    </ul>
                </div>

                <!-- Contacto & WhatsApp -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Atención y Ventas</h4>
                    <p class="text-xs text-slate-400">Consulte disponibilidad y precios especiales por WhatsApp oficial:</p>
                    <a href="{{ \App\Models\Setting::getWhatsappUrl('Hola Portátiles Perú, solicito asesoría comercial') }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors">
                        <span>Chatear con un Asesor</span>
                    </a>
                </div>
            </div>

            <!-- Métodos de Pago y Seguridad -->
            <div class="pt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} Portátiles Perú E.I.R.L. Todos los derechos reservados.</p>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] text-slate-500 font-bold uppercase">Medios de Pago:</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono text-[10px]">VISA</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono text-[10px]">Mastercard</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-emerald-400 font-bold text-[10px]">YAPE</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-cyan-400 font-bold text-[10px]">PLIN</span>
                    <span class="px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-bold text-[10px]">Transferencia BCP/BBVA</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
