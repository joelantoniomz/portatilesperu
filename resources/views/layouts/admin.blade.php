<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Administrativo') — Portátiles Perú</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        forest: {
                            950: '#03140C',
                            900: '#051A11',
                            850: '#072418',
                            800: '#0B3322',
                            750: '#0E3E2A',
                            700: '#104930',
                            600: '#176644',
                        },
                        emerald: {
                            400: '#34D399',
                            500: '#10B981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065F46',
                            900: '#064E3B',
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
            background: radial-gradient(circle at 75% 20%, #0d462c 0%, #082d1c 45%, #051b11 100%);
            min-height: 100vh;
            color: #1E293B;
            background-attachment: fixed;
        }
        .app-shell {
            width: 100%;
            max-width: 1840px;
            margin: 0 auto;
            min-height: 100vh;
        }
        .card-surface {
            background: #FFFFFF;
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.25);
            border-radius: 9999px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.5);
        }

        /* Barra Lateral / Dock Expandible y Compacto */
        .admin-dock {
            width: 17rem;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), padding 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-x: hidden;
            overflow-y: auto;
            max-height: calc(100vh - 7rem);
            position: sticky;
            top: 1rem;
        }
        .admin-dock.is-compact {
            width: 4.75rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .admin-dock.is-compact .dock-label,
        .admin-dock.is-compact .dock-badge,
        .admin-dock.is-compact .dock-heading,
        .admin-dock.is-compact .dock-title,
        .admin-dock.is-compact .dock-footer-text {
            display: none !important;
            opacity: 0 !important;
            max-width: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }
        .admin-dock.is-compact .dock-header {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .admin-dock.is-compact .dock-link {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            width: 2.75rem !important;
            height: 2.75rem !important;
            margin-left: auto !important;
            margin-right: auto !important;
        }
        .admin-dock.is-compact .dock-link-inner {
            justify-content: center !important;
            gap: 0 !important;
        }
        .admin-dock.is-compact .dock-divider {
            margin-top: 0.5rem;
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .admin-dock {
                position: fixed !important;
                top: 0 !important;
                left: -100% !important;
                bottom: 0 !important;
                height: 100vh !important;
                max-height: 100vh !important;
                z-index: 50 !important;
                border-radius: 0 1.5rem 1.5rem 0 !important;
                transition: left 0.3s ease-in-out !important;
                width: 17rem !important;
            }
            .admin-dock.open-mobile {
                left: 0 !important;
            }
        }

        .theme-blue .bg-emerald-500, .theme-blue .bg-emerald-600 { background-color: #2563eb !important; }
        .theme-blue .text-emerald-400, .theme-blue .text-emerald-500, .theme-blue .text-emerald-600 { color: #2563eb !important; }
        .theme-violet .bg-emerald-500, .theme-violet .bg-emerald-600 { background-color: #7c3aed !important; }
        .theme-violet .text-emerald-400, .theme-violet .text-emerald-500, .theme-violet .text-emerald-600 { color: #7c3aed !important; }
    </style>
</head>
@php($adminTheme = \App\Models\Setting::getValue('theme_color', 'emerald'))
<body class="theme-{{ $adminTheme }} antialiased flex flex-col min-h-screen text-slate-800">

<div class="app-shell flex flex-col min-h-screen">

    <!-- Backdrop Móvil -->
    <div id="mobileDockBackdrop" onclick="closeMobileDock()" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 transition-opacity"></div>

    <!-- Barra de Navegación Superior Estilo diseño.png (Blanco y Verde) -->
    <header class="mx-3 sm:mx-5 mt-3 sm:mt-4 mb-3 px-4 sm:px-6 py-3.5 flex items-center justify-between bg-white rounded-3xl shadow-sm border border-slate-100/80">
        <!-- Izquierda: Toggle Móvil y Logo / Marca -->
        <div class="flex items-center gap-3 sm:gap-4">
            <button type="button" onclick="toggleMobileDock()" class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors" title="Abrir menú">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                @php($companyLogo = \App\Models\Setting::getValue('company_logo_url'))
                @php($companyName = \App\Models\Setting::getValue('company_name', 'PORTÁTILES PERÚ'))
                @if($companyLogo)
                <div class="h-10 px-2 rounded-2xl bg-white border border-slate-200/80 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform flex-shrink-0">
                    <img src="{{ $companyLogo }}" alt="{{ $companyName }}" class="max-h-8 max-w-[120px] object-contain">
                </div>
                @else
                <div class="w-10 h-10 rounded-2xl bg-[#0c3823] flex items-center justify-center text-white shadow-md shadow-emerald-950/20 group-hover:scale-105 transition-transform flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                @endif
                <div>
                    <h1 class="text-slate-900 font-extrabold text-sm sm:text-base leading-tight tracking-tight uppercase">{{ $companyName }}</h1>
                    <span class="text-emerald-700 text-[10px] font-bold uppercase tracking-wider block">Sistema de Catálogo e Inventario</span>
                </div>
            </a>
        </div>

        <!-- Buscador Global Pill (Inspirado en diseño.png) -->
        <div class="hidden md:flex items-center flex-1 max-w-md mx-6">
            <div class="w-full relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" placeholder="Buscar laptop, SKU, cliente o pedido..." class="w-full pl-10 pr-4 py-2 rounded-full bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all">
            </div>
        </div>

        <!-- Acciones Rápidas y Perfil del Usuario -->
        <div class="flex items-center gap-2 sm:gap-3">
            <!-- Botón Tienda Pública -->
            <a href="{{ route('home') }}" target="_blank" class="hidden lg:flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold border border-emerald-200/80 transition-all shadow-xs">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Ver Tienda</span>
            </a>

            <!-- Perfil de Usuario Pill -->
            <a href="{{ route('admin.profile') }}" class="flex items-center gap-2.5 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 rounded-full pl-3 pr-1.5 py-1 shadow-xs transition-colors">
                <div class="text-right hidden sm:block">
                    <p class="text-slate-800 text-xs font-bold leading-tight">{{ auth()->user()->name }}</p>
                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800">
                        {{ auth()->user()->primary_role?->display_name ?? 'Usuario' }}
                    </span>
                </div>
                <div class="w-8 h-8 rounded-full bg-[#0c3823] text-white flex items-center justify-center font-black text-xs shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </a>

            <!-- Botón Salir -->
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" title="Cerrar Sesión" class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 flex items-center justify-center transition-all hover:scale-105">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </header>

    <!-- Contenedor Principal: Barra Lateral Flotante + Área de Contenido -->
    <div class="flex-1 flex px-3 sm:px-5 pb-5 gap-4 sm:gap-5 w-full items-start">
        
        <!-- Dock Lateral Flotante Blanco y Verde (Inspirado en diseño.png) -->
        <aside id="adminDock" class="admin-dock card-surface p-3 sm:p-4 flex flex-col justify-between custom-scrollbar flex-shrink-0 z-30">
            <script>
                // Aplicar estado compacto guardado sin parpadeo visual
                if (localStorage.getItem('admin_sidebar_compact') === 'true') {
                    document.getElementById('adminDock').classList.add('is-compact');
                }
            </script>

            <div class="space-y-4">
                <!-- Encabezado del Dock: Título y Botón Minimizar/Expandir -->
                <div class="dock-header flex items-center justify-between pb-2.5 border-b border-slate-100">
                    <div class="dock-title flex items-center gap-2 pl-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <p class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Navegación</p>
                    </div>
                    <button type="button" id="dockToggleBtn" onclick="toggleAdminDock()" class="p-1.5 rounded-xl text-slate-400 hover:text-emerald-800 hover:bg-emerald-50 transition-colors flex items-center justify-center" title="Minimizar barra lateral">
                        <svg id="dockToggleIcon" class="w-4 h-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                        </svg>
                    </button>
                </div>

                <nav class="space-y-1">
                    <!-- 1. Dashboard -->
                    @php($isDashActive = request()->routeIs('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" 
                       data-tooltip="Dashboard" 
                       title="Dashboard" 
                       class="dock-link flex items-center gap-3 px-3 py-2.5 rounded-2xl text-xs font-semibold transition-all {{ $isDashActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isDashActive ? 'text-emerald-300' : 'text-slate-500 group-hover:text-emerald-700' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Dashboard</span>
                    </a>

                    <!-- SECCIÓN: ACCESOS Y SEGURIDAD -->
                    @if(auth()->user()->hasRole(['superadmin', 'admin']) || auth()->user()->hasPermission('audit.view'))
                    <div class="dock-divider border-t border-slate-100 my-2"></div>
                    <p class="dock-heading text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-1 pb-0.5">Gestión & Seguridad</p>
                    @endif

                    <!-- Usuarios y Roles -->
                    @if(auth()->user()->hasRole(['superadmin', 'admin']))
                    @php($isUsersActive = request()->routeIs('admin.users*'))
                    <a href="{{ route('admin.users') }}" 
                       data-tooltip="Usuarios y Roles" 
                       title="Usuarios y Roles" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isUsersActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isUsersActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <span class="dock-label truncate">Usuarios & Roles</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isUsersActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">Fase 1</span>
                    </a>

                    <!-- Configuración -->
                    @php($isConfigActive = request()->routeIs('admin.settings.*'))
                    <a href="{{ route('admin.settings.index') }}" 
                       data-tooltip="Configuración" 
                       title="Configuración" 
                       class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isConfigActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isConfigActive ? 'text-emerald-300' : 'text-slate-500' }}">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Configuración</span>
                    </a>
                    @endif

                    <!-- Auditoría y Seguridad -->
                    @if(auth()->user()->hasPermission('audit.view'))
                    @php($isAuditActive = request()->routeIs('admin.audit*'))
                    <a href="{{ route('admin.audit') }}" 
                       data-tooltip="Auditoría" 
                       title="Auditoría" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isAuditActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isAuditActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <span class="dock-label truncate">Auditoría</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isAuditActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">Fase 1</span>
                    </a>
                    @endif

                    <!-- SECCIÓN: CATÁLOGO -->
                    @if(auth()->user()->hasPermission('catalog.view'))
                    <div class="dock-divider border-t border-slate-100 my-2"></div>
                    <p class="dock-heading text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-1 pb-0.5">Catálogo</p>

                    <!-- Productos -->
                    @php($isProdActive = request()->routeIs('admin.products.*'))
                    <a href="{{ route('admin.products.index') }}" 
                       data-tooltip="Productos" 
                       title="Productos" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isProdActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isProdActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <span class="dock-label truncate">Productos</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isProdActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">Fase 2</span>
                    </a>

                    <!-- Categorías -->
                    @php($isCatActive = request()->routeIs('admin.categories.*'))
                    <a href="{{ route('admin.categories.index') }}" 
                       data-tooltip="Categorías" 
                       title="Categorías" 
                       class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isCatActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isCatActive ? 'text-emerald-300' : 'text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Categorías</span>
                    </a>

                    <!-- Marcas y Modelos -->
                    @php($isBrandActive = request()->routeIs('admin.brands.*'))
                    <a href="{{ route('admin.brands.index') }}" 
                       data-tooltip="Marcas y Modelos" 
                       title="Marcas y Modelos" 
                       class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isBrandActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isBrandActive ? 'text-emerald-300' : 'text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Marcas & Modelos</span>
                    </a>
                    @endif

                    <!-- SECCIÓN: INVENTARIO -->
                    @if(auth()->user()->hasPermission('inventory.view'))
                    <div class="dock-divider border-t border-slate-100 my-2"></div>
                    <p class="dock-heading text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-1 pb-0.5">Inventario & Stock</p>

                    <!-- Inventario & Stock -->
                    @php($isInvActive = request()->routeIs('admin.inventory.index') || request()->routeIs('admin.inventory.product'))
                    <a href="{{ route('admin.inventory.index') }}" 
                       data-tooltip="Inventario & Stock" 
                       title="Inventario & Stock" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isInvActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isInvActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                            </div>
                            <span class="dock-label truncate">Inventario</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isInvActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">Fase 3</span>
                    </a>

                    <!-- Kardex / Movimientos -->
                    @php($isKardexActive = request()->routeIs('admin.inventory.movements'))
                    <a href="{{ route('admin.inventory.movements') }}" 
                       data-tooltip="Kardex / Movimientos" 
                       title="Kardex / Movimientos" 
                       class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isKardexActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isKardexActive ? 'text-emerald-300' : 'text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Kardex Movimientos</span>
                    </a>

                    <!-- Almacenes -->
                    @php($isWhActive = request()->routeIs('admin.warehouses.*'))
                    <a href="{{ route('admin.warehouses.index') }}" 
                       data-tooltip="Almacenes" 
                       title="Almacenes" 
                       class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isWhActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isWhActive ? 'text-emerald-300' : 'text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Almacenes</span>
                    </a>

                    <!-- Escanear Código / QR -->
                    @php($isScanActive = request()->routeIs('admin.inventory.scanner'))
                    <a href="{{ route('admin.inventory.scanner') }}" 
                       data-tooltip="Escanear Código / QR" 
                       title="Escanear Código / QR" 
                       class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isScanActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isScanActive ? 'text-emerald-300' : 'text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Escanear QR</span>
                    </a>

                    <!-- Reservas Activas -->
                    @php($isResActive = request()->routeIs('admin.inventory.reservations'))
                    <a href="{{ route('admin.inventory.reservations') }}" 
                       data-tooltip="Reservas Activas" 
                       title="Reservas Activas" 
                       class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isResActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isResActive ? 'text-emerald-300' : 'text-slate-500' }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="dock-label truncate flex-1">Reservas Activas</span>
                    </a>
                    @endif

                    <!-- SECCIÓN: VENTAS Y CLIENTES -->
                    @if(auth()->user()->hasPermission('orders.view') || auth()->user()->hasPermission('customers.view') || auth()->user()->hasPermission('whatsapp.view') || auth()->user()->hasPermission('catalog.pdf'))
                    <div class="dock-divider border-t border-slate-100 my-2"></div>
                    <p class="dock-heading text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-1 pb-0.5">Comercial & Clientes</p>
                    @endif

                    <!-- Pedidos & WhatsApp -->
                    @if(auth()->user()->hasPermission('orders.view'))
                    @php($isOrderActive = request()->routeIs('admin.orders.*'))
                    <a href="{{ route('admin.orders.index') }}" 
                       data-tooltip="Pedidos & WhatsApp" 
                       title="Pedidos & WhatsApp" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isOrderActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isOrderActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <span class="dock-label truncate">Pedidos & WhatsApp</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isOrderActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">Fase 6</span>
                    </a>
                    @endif

                    <!-- Clientes -->
                    @if(auth()->user()->hasPermission('customers.view'))
                    @php($isCustActive = request()->routeIs('admin.customers.*'))
                    <a href="{{ route('admin.customers.index') }}" 
                       data-tooltip="Clientes" 
                       title="Clientes" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isCustActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isCustActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4m-5 6H3v-2a4 4 0 014-4h6a4 4 0 014 4v2zM10 10a4 4 0 100-8 4 4 0 000 8z" />
                                </svg>
                            </div>
                            <span class="dock-label truncate">Clientes</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isCustActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">Fase 7</span>
                    </a>
                    @endif

                    <!-- WhatsApp (OpenWA & Chat Integrado) -->
                    @if(auth()->user()->hasPermission('whatsapp.view'))
                    @php($isConvActive = request()->routeIs('admin.conversations.*'))
                    <a href="{{ route('admin.conversations.index') }}" 
                       data-tooltip="WhatsApp" 
                       title="WhatsApp" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isConvActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isConvActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                                </svg>
                            </div>
                            <span class="dock-label truncate">WhatsApp</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isConvActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">OpenWA</span>
                    </a>
                    @endif

                    <!-- Catálogos PDF -->
                    @if(auth()->user()->hasPermission('catalog.pdf'))
                    @php($isPdfActive = request()->routeIs('admin.catalog-pdf.*'))
                    <a href="{{ route('admin.catalog-pdf.index') }}" 
                       data-tooltip="Catálogos PDF" 
                       title="Catálogos PDF" 
                       class="dock-link flex items-center justify-between px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isPdfActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                        <div class="dock-link-inner flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isPdfActive ? 'text-emerald-300' : 'text-slate-500' }}">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <span class="dock-label truncate">Catálogos PDF</span>
                        </div>
                        <span class="dock-badge text-[10px] font-bold px-1.5 py-0.5 rounded {{ $isPdfActive ? 'bg-emerald-400/20 text-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">Fase 8</span>
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Footer del Dock: Perfil y Accesos Directos -->
            <div class="pt-3 border-t border-slate-100 space-y-1 mt-4">
                @php($isProfActive = request()->routeIs('admin.profile'))
                <a href="{{ route('admin.profile') }}" 
                   data-tooltip="Mi Perfil" 
                   title="Mi Perfil" 
                   class="dock-link flex items-center gap-3 px-3 py-2 rounded-2xl text-xs font-semibold transition-all {{ $isProfActive ? 'bg-[#0c3823] text-white shadow-md shadow-emerald-950/20' : 'text-slate-600 hover:text-emerald-900 hover:bg-emerald-50/70' }}">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isProfActive ? 'text-emerald-300' : 'text-emerald-600' }}">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span class="dock-label truncate flex-1">Mi Perfil & Permisos</span>
                </a>
            </div>
        </aside>

        <!-- Área de Contenido Principal -->
        <main class="flex-1 min-w-0 flex flex-col gap-5 overflow-hidden">
            <!-- Mensajes Flash de Éxito / Estado -->
            @if(session('status'))
            <div class="px-5 py-3.5 rounded-2xl bg-white border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-semibold flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
            @endif

            <!-- Mensajes Flash de Error -->
            @if($errors->any())
            <div class="px-5 py-3.5 rounded-2xl bg-white border border-rose-200 text-rose-900 text-xs sm:text-sm font-semibold flex items-center gap-3 shadow-sm">
                <div class="w-7 h-7 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Pie de Página -->
    <footer class="mx-3 sm:mx-5 mb-4 px-6 py-3.5 text-center text-xs text-white/70">
        Portátiles Perú &copy; {{ date('Y') }} — Catálogo Virtual, Inventario y Gestión Comercial con Validación en Servidor.
    </footer>
</div>

<!-- Scripts de Interacción del Dock (Expandir / Minimizar sin deformarse) -->
<script>
    function toggleAdminDock() {
        const dock = document.getElementById('adminDock');
        dock.classList.toggle('is-compact');
        const isCompact = dock.classList.contains('is-compact');
        localStorage.setItem('admin_sidebar_compact', isCompact ? 'true' : 'false');
        updateDockToggle(isCompact);
    }

    function updateDockToggle(isCompact) {
        const icon = document.getElementById('dockToggleIcon');
        const btn = document.getElementById('dockToggleBtn');
        if (icon) {
            if (isCompact) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 5l7 7-7 7M5 5l7 7-7 7" />';
                if (btn) btn.title = "Expandir menú lateral";
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />';
                if (btn) btn.title = "Minimizar menú lateral";
            }
        }
    }

    function toggleMobileDock() {
        const dock = document.getElementById('adminDock');
        const backdrop = document.getElementById('mobileDockBackdrop');
        dock.classList.toggle('open-mobile');
        backdrop.classList.toggle('hidden');
    }

    function closeMobileDock() {
        const dock = document.getElementById('adminDock');
        const backdrop = document.getElementById('mobileDockBackdrop');
        dock.classList.remove('open-mobile');
        backdrop.classList.add('hidden');
    }

    // Inicialización de estado y tooltips flotantes
    document.addEventListener('DOMContentLoaded', function() {
        const isCompact = localStorage.getItem('admin_sidebar_compact') === 'true';
        updateDockToggle(isCompact);

        // Tooltips flotantes inmunes al overflow cuando el dock está compacto
        let activeTooltipEl = null;
        const dock = document.getElementById('adminDock');
        const links = dock.querySelectorAll('.dock-link');
        
        links.forEach(link => {
            link.addEventListener('mouseenter', () => {
                if (!dock.classList.contains('is-compact')) return;
                const text = link.getAttribute('data-tooltip');
                if (!text) return;
                
                if (!activeTooltipEl) {
                    activeTooltipEl = document.createElement('div');
                    activeTooltipEl.className = 'fixed z-[99999] px-3 py-1.5 rounded-xl bg-[#0c3823] text-white text-xs font-bold shadow-xl border border-emerald-400/30 pointer-events-none transition-all duration-150 whitespace-nowrap';
                    document.body.appendChild(activeTooltipEl);
                }
                
                const rect = link.getBoundingClientRect();
                activeTooltipEl.textContent = text;
                activeTooltipEl.style.top = `${rect.top + (rect.height / 2) - 14}px`;
                activeTooltipEl.style.left = `${rect.right + 12}px`;
                activeTooltipEl.style.opacity = '1';
                activeTooltipEl.style.display = 'block';
            });
            
            link.addEventListener('mouseleave', () => {
                if (activeTooltipEl) {
                    activeTooltipEl.style.opacity = '0';
                    activeTooltipEl.style.display = 'none';
                }
            });
        });
    });
</script>
</body>
</html>
