<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Administrativo') — Portátiles Perú</title>
    <!-- Google Fonts: Plus Jakarta Sans / Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: {
                            900: '#04180F',
                            850: '#072418',
                            800: '#0B3322',
                            700: '#104930',
                            600: '#176644',
                        },
                        emerald: {
                            400: '#34D399',
                            500: '#10B981',
                            600: '#059669',
                            700: '#047857',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #051A11 0%, #082A1C 50%, #05180F 100%);
            min-height: 100vh;
            color: #1E293B;
        }
        .dock-blur {
            background: rgba(11, 46, 31, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .card-surface {
            background: #FFFFFF;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.12);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.3);
            border-radius: 9999px;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen text-slate-800 antialiased">

    <!-- Barra de Navegación Superior -->
    <header class="px-6 py-4 flex items-center justify-between border-b border-emerald-950/40">
        <!-- Logo / Marca -->
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-white font-bold text-lg leading-tight tracking-wide">PORTÁTILES PERÚ</h1>
                    <span class="text-emerald-400 text-xs font-semibold uppercase tracking-wider">Sistema de Catálogo e Inventario</span>
                </div>
            </a>
        </div>

        <!-- Buscador Global Pill -->
        <div class="hidden md:flex items-center flex-1 max-w-md mx-8">
            <div class="w-full relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-emerald-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" placeholder="Buscar por SKU, laptop, cliente o pedido..." class="w-full pl-10 pr-4 py-2 rounded-full bg-forest-850/80 border border-emerald-500/20 text-white placeholder-emerald-200/40 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
            </div>
        </div>

        <!-- Acciones Rápidas y Usuario -->
        <div class="flex items-center gap-4">
            <!-- Indicador de Fase 1 Activa -->
            <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Fase 1: Autenticación & Permisos</span>
            </div>

            <!-- Perfil de Usuario Pill -->
            <div class="flex items-center gap-3 bg-forest-850/90 border border-white/10 rounded-full pl-3 pr-2 py-1.5 shadow-md">
                <div class="text-right">
                    <p class="text-white text-xs font-bold leading-tight">{{ auth()->user()->name }}</p>
                    <span class="inline-block px-2 py-0.5 mt-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        {{ auth()->user()->primary_role?->display_name ?? 'Usuario' }}
                    </span>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white font-black text-sm shadow">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>

            <!-- Botón Salir -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Cerrar Sesión" class="w-9 h-9 rounded-full bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center justify-center transition-all hover:scale-105">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <!-- Contenedor Principal con Dock Lateral y Contenido -->
    <div class="flex-1 flex px-6 py-6 gap-6 max-w-[1700px] w-full mx-auto">
        <!-- Dock Lateral Flotante Inspirado en diseño.png -->
        <aside class="w-64 flex-shrink-0 flex flex-col justify-between dock-blur rounded-3xl p-4 shadow-2xl">
            <div class="space-y-6">
                <!-- Título Módulos -->
                <div class="px-3 pt-2">
                    <p class="text-[11px] font-bold text-emerald-300/70 uppercase tracking-widest">Navegación del Sistema</p>
                </div>

                <nav class="space-y-1">
                    <!-- 1. Dashboard -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <!-- 13. Usuarios y Roles (Protegido superadmin, admin) -->
                    @if(auth()->user()->hasRole(['superadmin', 'admin']))
                    <a href="{{ route('admin.users') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-semibold transition-all {{ request()->routeIs('admin.users') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Usuarios y Roles</span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-400/20 text-emerald-300">Fase 1</span>
                    </a>
                    @endif

                    <!-- 17. Auditoría y Seguridad -->
                    @if(auth()->user()->hasPermission('audit.view'))
                    <a href="{{ route('admin.audit') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-sm font-semibold transition-all {{ request()->routeIs('admin.audit') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/30' : 'text-slate-300 hover:bg-white/5 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Auditoría</span>
                        </div>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-400/20 text-emerald-300">Fase 1</span>
                    </a>
                    @endif

                    <!-- Separador de Módulos para siguientes fases según PLAN -->
                    <div class="pt-4 pb-1 px-3">
                        <p class="text-[10px] font-bold text-slate-400/60 uppercase tracking-widest">Próximas Fases</p>
                    </div>

                    <!-- 2. Productos (Fase 2) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span>Productos & Catálogo</span>
                        </div>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Fase 2</span>
                    </div>

                    <!-- 5. Inventario (Fase 3) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                            </svg>
                            <span>Inventario & QR</span>
                        </div>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Fase 3</span>
                    </div>

                    <!-- 7. Pedidos & WhatsApp (Fase 6) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Pedidos & WhatsApp</span>
                        </div>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Fase 6</span>
                    </div>

                    <!-- 10. Catálogos PDF (Fase 8) -->
                    <div class="flex items-center justify-between px-3.5 py-2 rounded-2xl text-xs font-medium text-slate-400 opacity-60 cursor-not-allowed">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Catálogos PDF (8)</span>
                        </div>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-400">Fase 8</span>
                    </div>
                </nav>
            </div>

            <!-- Footer del Dock -->
            <div class="pt-4 border-t border-white/5 space-y-2">
                <a href="{{ route('admin.profile') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-2xl text-xs font-semibold text-slate-300 hover:bg-white/5 hover:text-white transition-all">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Mi Perfil & Permisos</span>
                </a>
            </div>
        </aside>

        <!-- Área de Contenido Principal -->
        <main class="flex-1 flex flex-col gap-6 overflow-hidden">
            <!-- Mensajes Flash de Alerta -->
            @if(session('status'))
            <div class="px-5 py-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between shadow-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
            @endif

            @if($errors->any())
            <div class="px-5 py-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm flex items-center gap-3 shadow-lg">
                <svg class="w-5 h-5 text-rose-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Pie de Página -->
    <footer class="px-6 py-4 text-center text-xs text-emerald-400/50 border-t border-emerald-950/30">
        Portátiles Perú &copy; 2026 — Catálogo Virtual, Inventario y Gestión Comercial con Validación en Servidor.
    </footer>
</body>
</html>
