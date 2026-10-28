@extends('layouts.admin')

@section('title', 'Dashboard Administrativo')

@section('content')
<div class="space-y-5">

    <!-- Tarjeta Superior: Bienvenida y Estado del Sistema (Blanco y Verde) -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200/80 px-3 py-1 text-xs font-bold text-emerald-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Fase 5: Dashboard en Tiempo Real con Datos Verificables</span>
            </div>
            <h2 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                Panel de Control &bull; Portátiles Perú
            </h2>
            <p class="max-w-2xl text-sm leading-relaxed text-slate-600">
                Bienvenido, <strong class="text-slate-900">{{ $user->name }}</strong>. Su rol activo es 
                <span class="inline-block rounded-full border border-emerald-200 bg-emerald-100 px-2.5 py-0.5 text-xs font-black uppercase text-emerald-800">
                    {{ $stats['current_role'] }}
                </span>.
                Todas las métricas se calculan directamente desde la base de datos de producción.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('home') }}" target="_blank" class="flex items-center gap-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 transition-all hover:bg-slate-100 hover:text-slate-900 shadow-xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                <span>Visitar Tienda Pública</span>
            </a>

            @if(auth()->user()->hasPermission('inventory.view'))
            <a href="{{ route('admin.inventory.scanner') }}" class="flex items-center gap-2 rounded-2xl bg-[#0c3823] px-4 py-2.5 text-xs font-bold text-white shadow-md shadow-emerald-950/20 transition-all hover:bg-emerald-800">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Escanear Código / QR</span>
            </a>
            @endif
        </div>
    </div>

    <!-- 4 Métricas Clave de Negocio (Consolidadas en tiempo real) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- 1. Catálogo de Laptops -->
        <div class="card-surface p-5 border border-slate-100 shadow-sm flex items-start justify-between group hover:border-emerald-300 transition-all">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Catálogo de Laptops</span>
                <p class="text-3xl font-black text-slate-900">{{ $stats['total_products'] }}</p>
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 pt-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ $stats['published_products'] }} publicadas en web</span>
                </div>
                <div class="text-[10px] text-slate-400 font-mono">
                    {{ $stats['draft_products'] }} borradores &bull; {{ $stats['archived_products'] }} archivadas
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
        </div>

        <!-- 2. Stock Físico y Disponible -->
        <div class="card-surface p-5 border border-slate-100 shadow-sm flex items-start justify-between group hover:border-emerald-300 transition-all">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Stock Físico Total</span>
                <p class="text-3xl font-black text-slate-900">{{ $stats['total_stock_physical'] }} <span class="text-xs font-normal text-slate-400">unid.</span></p>
                <div class="flex items-center gap-2 text-xs font-semibold text-emerald-600 pt-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ $stats['total_stock_available'] }} disponibles venta</span>
                </div>
                <div class="text-[10px] text-amber-600 font-semibold">
                    {{ $stats['total_stock_reserved'] }} unidades reservadas ({{ $stats['active_reservations'] }} pedidos)
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
            </div>
        </div>

        <!-- 3. Valorización de Inventario -->
        <div class="card-surface p-5 border border-slate-100 shadow-sm flex items-start justify-between group hover:border-emerald-300 transition-all">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Valorización Inventario</span>
                <p class="text-2xl font-black text-slate-900">
                    S/ {{ number_format($stats['inventory_valuation'], 2) }}
                </p>
                <span class="text-[11px] font-semibold text-emerald-600 pt-1 block">
                    Distribuido en {{ $stats['warehouses_count'] }} almacenes activos
                </span>
                <span class="text-[10px] text-slate-400 font-mono">
                    {{ $stats['total_movements'] }} movimientos registrados en Kardex
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- 4. Alertas de Inventario -->
        <div class="card-surface p-5 border border-slate-100 shadow-sm flex items-start justify-between group hover:border-amber-300 transition-all">
            <div class="space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Alertas de Stock</span>
                <p class="text-3xl font-black {{ $stats['low_stock_count'] > 0 || $stats['out_of_stock_count'] > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                    {{ $stats['low_stock_count'] + $stats['out_of_stock_count'] }}
                </p>
                <div class="text-xs font-semibold text-amber-700 pt-1">
                    {{ $stats['low_stock_count'] }} por debajo de alerta mínima
                </div>
                <div class="text-[10px] text-rose-600 font-bold">
                    {{ $stats['out_of_stock_count'] }} modelos agotados
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl {{ $stats['low_stock_count'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>

    </div>

    <!-- Accesos Directos Funcionales (Permisos en Servidor) -->
    <div class="card-surface p-6 border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span>Acciones Rápidas del Módulo</span>
            </h3>
            <span class="text-xs text-slate-400">Filtrado por permisos de tu usuario</span>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

            @if(auth()->user()->hasPermission('catalog.manage'))
            <a href="{{ route('admin.products.create') }}" class="p-3.5 rounded-2xl border border-slate-200/80 hover:border-emerald-500 hover:bg-emerald-50/40 text-center group transition-all">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 block">Nueva Laptop</span>
                <span class="text-[10px] text-slate-400">Crear ficha técnica</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('inventory.manage'))
            <a href="{{ route('admin.inventory.entry') }}" class="p-3.5 rounded-2xl border border-slate-200/80 hover:border-emerald-500 hover:bg-emerald-50/40 text-center group transition-all">
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 block">Entrada Stock</span>
                <span class="text-[10px] text-slate-400">Recepción de lote</span>
            </a>

            <a href="{{ route('admin.inventory.adjustment') }}" class="p-3.5 rounded-2xl border border-slate-200/80 hover:border-amber-500 hover:bg-amber-50/40 text-center group transition-all">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 block">Ajuste Físico</span>
                <span class="text-[10px] text-slate-400">Conteo trimestral</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('inventory.view'))
            <a href="{{ route('admin.inventory.scanner') }}" class="p-3.5 rounded-2xl border border-slate-200/80 hover:border-slate-800 hover:bg-slate-100 text-center group transition-all">
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 block">Escáner QR</span>
                <span class="text-[10px] text-slate-400">Lector o cámara</span>
            </a>

            <a href="{{ route('admin.inventory.movements') }}" class="p-3.5 rounded-2xl border border-slate-200/80 hover:border-teal-500 hover:bg-teal-50/40 text-center group transition-all">
                <div class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 block">Kardex General</span>
                <span class="text-[10px] text-slate-400">Historial inmutable</span>
            </a>
            @endif

            @if(auth()->user()->hasRole(['superadmin', 'admin']))
            <a href="{{ route('admin.users') }}" class="p-3.5 rounded-2xl border border-slate-200/80 hover:border-blue-500 hover:bg-blue-50/40 text-center group transition-all">
                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mx-auto mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <span class="text-xs font-bold text-slate-800 block">Usuarios & Roles</span>
                <span class="text-[10px] text-slate-400">Personal tienda</span>
            </a>
            @endif

        </div>
    </div>

    <!-- Fila Gráficos: Distribución de Stock por Almacén + Categorías -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Gráfico 1: Capacidad y Stock por Almacén -->
        <div class="lg:col-span-7 card-surface p-6 border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Distribución de Stock por Sede</h3>
                    <p class="text-xs text-slate-400">Disponibilidad en almacenes y tiendas físicas de Lima</p>
                </div>
                <a href="{{ route('admin.warehouses.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Ver Almacenes &rarr;
                </a>
            </div>

            <div class="space-y-4">
                @php $maxWh = $warehousesStock->max('total_physical') ?: 1; @endphp
                @forelse($warehousesStock as $wh)
                @php
                    $pct = round(($wh->total_physical / $maxWh) * 100);
                @endphp
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ $wh->code }}</span>
                            <span class="font-bold text-slate-800">{{ $wh->name }}</span>
                        </div>
                        <div class="text-right font-mono">
                            <strong class="text-slate-900">{{ (int) $wh->total_physical }}</strong> unid. 
                            <span class="text-emerald-600 font-semibold text-[11px]">({{ (int) $wh->total_available }} disp.)</span>
                        </div>
                    </div>
                    <!-- Barra de Progreso Dinámica -->
                    <div class="w-full h-3 rounded-full bg-slate-100 overflow-hidden flex">
                        <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    No se registran almacenes con existencias actualmente.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Gráfico 2: Laptops por Categoría -->
        <div class="lg:col-span-5 card-surface p-6 border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Laptops por Categoría</h3>
                    <p class="text-xs text-slate-400">Proporción de modelos activos en catálogo</p>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Ver Taxonomías &rarr;
                </a>
            </div>

            <div class="space-y-3">
                @php $totalCatProds = $categoryDistribution->sum('products_count') ?: 1; @endphp
                @forelse($categoryDistribution as $cat)
                @php
                    $catPct = round(($cat->products_count / $totalCatProds) * 100);
                @endphp
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-bold text-slate-800 block">{{ $cat->name }}</span>
                        <span class="text-[11px] text-slate-400">{{ $cat->products_count }} modelos ({{ $catPct }}%)</span>
                    </div>
                    <div class="w-20 h-2 rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-full bg-teal-500 rounded-full" style="width: {{ $catPct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    No hay categorías con productos activos.
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Fila Alertas de Stock y Actividad Reciente -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Lista de Alertas de Stock Bajo (Prioridad Inmediata) -->
        <div class="lg:col-span-6 card-surface p-6 border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Alertas de Reposición</h3>
                </div>
                @if(auth()->user()->hasPermission('inventory.view'))
                <a href="{{ route('admin.inventory.index', ['stock_filter' => 'low']) }}" class="text-xs font-bold text-amber-600 hover:underline">
                    Ver todos los bajos &rarr;
                </a>
                @endif
            </div>

            <div class="space-y-2">
                @forelse($lowStockBalances as $balance)
                <div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-200/80 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <a href="{{ route('admin.inventory.product', $balance->product_id) }}" class="font-bold text-slate-900 hover:text-emerald-700 transition-colors">
                            {{ $balance->product->name }}
                        </a>
                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                            <span class="font-mono">{{ $balance->product->sku }}</span>
                            <span>&bull;</span>
                            <span>{{ $balance->warehouse->name }}</span>
                        </div>
                    </div>

                    <div class="text-right flex items-center gap-3">
                        <div>
                            <span class="text-amber-700 font-black text-sm block">{{ $balance->qty_available }} disp.</span>
                            <span class="text-[10px] text-slate-400 font-mono">Mín: {{ $balance->min_alert ?? 0 }}</span>
                        </div>

                        @if(auth()->user()->hasPermission('inventory.manage'))
                        <a href="{{ route('admin.inventory.entry', ['product_id' => $balance->product_id, 'warehouse_id' => $balance->warehouse_id]) }}" class="px-2.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-[11px] transition-all shadow-sm">
                            + Reponer
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-8 text-center space-y-2">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-xs font-bold text-slate-700">Inventario 100% abastecido</p>
                    <p class="text-[11px] text-slate-400">No hay productos en nivel de alerta crítica en ningún almacén.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Últimos Movimientos de Kardex (Trazabilidad en Vivo) -->
        <div class="lg:col-span-6 card-surface p-6 border border-slate-100 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Últimos Movimientos de Stock</h3>
                </div>
                @if(auth()->user()->hasPermission('inventory.view'))
                <a href="{{ route('admin.inventory.movements') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Ver Kardex Completo &rarr;
                </a>
                @endif
            </div>

            <div class="space-y-2">
                @forelse($recentMovements as $mov)
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                    <div class="space-y-0.5">
                        <div class="flex items-center gap-2">
                            @php
                                $badgeStyle = match($mov->type) {
                                    'entry', 'transfer_in' => 'bg-emerald-100 text-emerald-800',
                                    'exit', 'transfer_out' => 'bg-rose-100 text-rose-800',
                                    'adjustment'           => 'bg-amber-100 text-amber-800',
                                    default                => 'bg-slate-200 text-slate-800',
                                };
                            @endphp
                            <span class="px-2 py-0.2 rounded-full font-black text-[9px] uppercase {{ $badgeStyle }}">
                                {{ ucfirst($mov->type) }}
                            </span>
                            <a href="{{ route('admin.inventory.product', $mov->product_id) }}" class="font-bold text-slate-900 truncate max-w-xs hover:text-emerald-700">
                                {{ $mov->product->name }}
                            </a>
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono">
                            {{ $mov->warehouse->name }} &bull; {{ $mov->user?->name ?? 'Sistema' }} &bull; {{ $mov->moved_at ? $mov->moved_at->diffForHumans() : $mov->created_at->diffForHumans() }}
                        </div>
                    </div>

                    <div class="text-right font-mono font-bold text-sm">
                        @if(in_array($mov->type, ['entry', 'transfer_in']))
                            <span class="text-emerald-600">+{{ $mov->quantity }}</span>
                        @elseif(in_array($mov->type, ['exit', 'transfer_out']))
                            <span class="text-rose-600">-{{ $mov->quantity }}</span>
                        @else
                            <span class="text-slate-800">{{ $mov->quantity }}</span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-400 text-xs">
                    No se registran movimientos de stock recientes.
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Bitácora Reciente de Auditoría del Sistema -->
    <div class="card-surface p-6 border border-slate-100 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">Auditoría del Sistema en Vivo</h3>
                <p class="text-xs text-slate-400">Trazabilidad de accesos, mutaciones e intentos de autenticación</p>
            </div>
            @if(auth()->user()->hasPermission('audit.view'))
            <a href="{{ route('admin.audit') }}" class="text-xs font-bold text-emerald-600 hover:underline">
                Bitácora Completa &rarr;
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-[10px] font-extrabold text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Evento / Acción</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">IP Origen</th>
                        <th class="px-4 py-3">Detalle</th>
                        <th class="px-4 py-3 rounded-r-xl">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAudit as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold 
                                @if($log->action === 'login') bg-emerald-100 text-emerald-800
                                @elseif($log->action === 'logout') bg-slate-100 text-slate-700
                                @elseif(str_starts_with($log->action, 'failed')) bg-rose-100 text-rose-800
                                @else bg-teal-100 text-teal-800 @endif">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-bold text-slate-800">
                            {{ $log->user?->name ?? 'Invitado / Sistema' }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 font-mono text-[11px]">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="px-4 py-3 text-slate-500 max-w-xs truncate">
                            {{ $log->new_values ? json_encode($log->new_values) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-[11px]">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-400 text-xs">
                            No se registran eventos de auditoría aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
