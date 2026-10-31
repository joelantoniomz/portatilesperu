@extends('layouts.admin')

@section('title', 'Gestión de Productos')

@section('content')
<div class="space-y-6">

    <!-- Encabezado y Acción Principal -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Fase 2: Catálogo de Productos & Taxonomías</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Catálogo de Laptops y Computadoras</h2>
            <p class="text-slate-500 text-sm mt-1">
                Gestione las especificaciones técnicas, precios, SKU únicos, marcas y galería de imágenes del catálogo.
            </p>
        </div>

        @if(auth()->user()->hasPermission('catalog.manage'))
        <div class="flex flex-wrap items-center gap-2">
            <!-- Botón Imprimir QR por Categoría -->
            <button type="button" onclick="document.getElementById('modalQrCategory').classList.remove('hidden')" class="px-4 py-3 rounded-2xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-xs font-bold text-emerald-900 transition-colors flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span>Descargar/Imprimir QR por Categoría</span>
            </button>
            <a href="{{ route('admin.products.template-guide') }}" class="px-4 py-3 rounded-2xl border border-slate-200 bg-white text-xs font-bold text-slate-700 transition-colors hover:bg-slate-50 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Plantilla Excel (CSV)</span>
            </a>
            <a href="{{ route('admin.products.create') }}" class="px-5 py-3 rounded-2xl bg-[#0c3823] hover:bg-emerald-800 text-white font-bold text-xs shadow-md shadow-emerald-950/20 transition-all flex items-center gap-2 hover:scale-[1.02]">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>Agregar Laptop</span>
            </a>
        </div>
        @endif
    </div>

    @if(auth()->user()->hasPermission('catalog.manage'))
    <details class="card-surface border border-slate-100 p-5">
        <summary class="cursor-pointer text-sm font-black text-slate-900">Carga masiva de productos</summary>
        <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <p class="text-xs text-slate-500">Revise la guía interactiva de columnas, complete una fila por producto y use los nombres exactos de las categorías y marcas ya creadas. Puede agregar URLs de imágenes por fila.</p>
                <div class="mt-2 flex items-center gap-3">
                    <a href="{{ route('admin.products.template-guide') }}" class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 hover:underline">
                        <span>Ver instructivo y plantilla completa</span> &rarr;
                    </a>
                    <span class="text-slate-300">|</span>
                    <a href="{{ route('admin.products.import-template') }}" class="inline-flex text-xs font-bold text-slate-600 hover:text-slate-900">
                        Descargar archivo .CSV directo
                    </a>
                </div>
            </div>
            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="file" name="products_file" accept=".csv,text/csv" required class="max-w-xs text-xs text-slate-500 file:mr-3 file:rounded-full file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-xs file:font-bold file:text-slate-700">
                <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-500">Importar productos</button>
            </form>
        </div>
    </details>
    @endif

    <!-- Indicadores de Catálogo -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Productos</span>
            <p class="text-xl font-black text-slate-900 mt-0.5">{{ $stats['total'] }}</p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[11px] font-bold text-emerald-600 uppercase">Publicados en Catálogo</span>
            <p class="text-xl font-black text-emerald-600 mt-0.5">{{ $stats['published'] }}</p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[11px] font-bold text-amber-600 uppercase">Borradores (No visibles)</span>
            <p class="text-xl font-black text-amber-600 mt-0.5">{{ $stats['draft'] }}</p>
        </div>
        <div class="card-surface p-4 border border-slate-100">
            <span class="text-[11px] font-bold text-slate-500 uppercase">Archivados (Inactivos)</span>
            <p class="text-xl font-black text-slate-600 mt-0.5">{{ $stats['archived'] }}</p>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros de Servidor -->
    <div class="card-surface p-5 border border-slate-100">
        <form method="GET" action="{{ route('admin.products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por SKU, nombre, barcode..." class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <select name="category_id" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="brand_id" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todas las marcas</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <select name="status" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Todos los estados</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publicado</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archivado</option>
                </select>
                <button type="submit" class="px-4 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-colors">
                    Filtrar
                </button>
                @if(request()->anyFilled(['search', 'category_id', 'brand_id', 'status']))
                <a href="{{ route('admin.products.index') }}" class="p-2 text-xs text-rose-500 font-bold hover:underline" title="Limpiar filtros">✕</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Listado de Productos -->
    <div class="card-surface p-6 border border-slate-100 space-y-4">

        <!-- Banner de Selección de Todo el Catálogo -->
        <div id="selectAllCatalogBanner" class="hidden p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between">
            <div>
                <span>Has seleccionado los <strong id="pageSelectedCount">0</strong> productos de esta página.</span>
                <button type="button" id="btnSelectEntireCatalog" onclick="selectEntireCatalog()" class="font-extrabold underline text-emerald-800 ml-2 hover:text-emerald-950">
                    Seleccionar todos los {{ $products->total() }} productos del catálogo completo
                </button>
            </div>
            <button type="button" onclick="clearSelection()" class="text-xs text-emerald-700 hover:text-emerald-900 font-bold underline">
                Deseleccionar
            </button>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-[10px] font-extrabold text-slate-400">
                    <tr>
                        @if(auth()->user()->hasPermission('catalog.manage'))
                        <th class="px-3 py-3 rounded-l-xl w-10 text-center">
                            <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllPage(this)" title="Seleccionar todos los de esta página" class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                        </th>
                        <th class="px-4 py-3">Producto</th>
                        @else
                        <th class="px-4 py-3 rounded-l-xl">Producto</th>
                        @endif
                        <th class="px-4 py-3">SKU / Códigos</th>
                        <th class="px-4 py-3">Categoría & Marca</th>
                        <th class="px-4 py-3">Precio</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right rounded-r-xl">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/60 transition-colors" id="row-product-{{ $product->id }}">
                        @if(auth()->user()->hasPermission('catalog.manage'))
                        <td class="px-3 py-3.5 text-center">
                            <input type="checkbox" value="{{ $product->id }}" onchange="handleItemCheck()" class="product-item-checkbox w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300 cursor-pointer">
                        </td>
                        @endif
                        <!-- Producto con Miniatura -->
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="w-12 h-12 object-cover rounded-xl border border-slate-200 bg-slate-100 flex-shrink-0">
                                <div>
                                    <a href="{{ route('admin.products.show', $product) }}" class="font-bold text-slate-900 hover:text-emerald-600 transition-colors line-clamp-1">
                                        {{ $product->name }}
                                    </a>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($product->is_featured)
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800">Destacado</span>
                                        @endif
                                        @if($product->is_new)
                                        <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">Novedad</span>
                                        @endif
                                        <span class="text-[10px] text-slate-400">{{ $product->images->count() }} imágenes</span>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- SKU y Códigos -->
                        <td class="px-4 py-3.5">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold font-mono bg-slate-100 text-slate-800 border border-slate-200">
                                {{ $product->sku }}
                            </span>
                            @if($product->barcode)
                            <span class="block text-[10px] text-slate-400 font-mono mt-0.5">EAN: {{ $product->barcode }}</span>
                            @endif
                        </td>

                        <!-- Categoría y Marca -->
                        <td class="px-4 py-3.5">
                            <span class="block font-semibold text-slate-800">{{ $product->category->name }}</span>
                            <span class="text-[11px] text-slate-500">{{ $product->brand->name }} @if($product->model) • {{ $product->model->name }} @endif</span>
                        </td>

                        <!-- Precio -->
                        <td class="px-4 py-3.5">
                            <span class="text-sm font-black text-slate-900">{{ $product->formatted_price }}</span>
                            <span class="block text-[10px] text-slate-400">{{ $product->currency }}</span>
                        </td>

                        <!-- Estado -->
                        <td class="px-4 py-3.5">
                            @if($product->status === 'published')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Publicado</span>
                                </span>
                            @elseif($product->status === 'draft')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    <span>Borrador</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    <span>Archivado</span>
                                </span>
                            @endif
                        </td>

                        <!-- Acciones -->
                        <td class="px-4 py-3.5 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.products.show', $product) }}" title="Ver ficha técnica" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                <a href="{{ route('admin.qr.label', $product) }}" target="_blank" title="Imprimir Etiqueta QR / Barra para Caja" class="p-2 rounded-xl bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                </a>

                                <!-- Compartir por WhatsApp con cliente -->
                                @php
                                    $shareText = rawurlencode("¡Hola! Te comparto los detalles y disponibilidad de esta laptop en Portátiles Perú:\n\n💻 *{$product->name}*\n🏷️ SKU: {$product->sku}\n💰 Precio: {$product->formatted_price}\n🔗 Ver ficha técnica: " . route('catalogo.detalle', $product->slug));
                                @endphp
                                <a href="https://wa.me/?text={{ $shareText }}" target="_blank" title="Compartir este producto por WhatsApp" class="p-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition-colors">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86.174.086.275.072.376-.043.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.203c.043.072.043.419-.101.824z"/>
                                    </svg>
                                </a>

                                @if(auth()->user()->hasPermission('catalog.manage'))
                                <a href="{{ route('admin.products.edit', $product) }}" title="Editar" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline" onsubmit="return confirm('¿Desea archivar el producto {{ $product->sku }}? Dejará de ser visible públicamente.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Archivar" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            No se encontraron productos que coincidan con los criterios de búsqueda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal para Descargar/Imprimir Etiquetas QR por Categoría -->
    <div id="modalQrCategory" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="card-surface p-6 sm:p-8 max-w-md w-full border border-slate-100 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-[#0c3823] text-white flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="font-extrabold text-slate-900 text-sm uppercase">Imprimir Etiquetas QR</h3>
                </div>
                <button type="button" onclick="document.getElementById('modalQrCategory').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
            </div>

            <p class="text-xs text-slate-500">
                Seleccione la categoría de laptops para generar la cuadrícula de etiquetas adhesivas listas para imprimir y pegar en las cajas (con SKU, precio, código de barra y QR escaneable).
            </p>

            <form action="{{ route('admin.qr.batch') }}" method="GET" target="_blank" class="space-y-4 pt-2">
                <div>
                    <label for="modal_cat_id" class="block text-xs font-bold text-slate-700 uppercase mb-1">Categoría</label>
                    <select id="modal_cat_id" name="category_id" required class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Seleccione una categoría...</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modalQrCategory').classList.add('hidden')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                        Cancelar
                    </button>
                    <button type="submit" onclick="document.getElementById('modalQrCategory').classList.add('hidden')" class="px-5 py-2 rounded-xl bg-[#0c3823] hover:bg-emerald-800 text-white font-bold text-xs shadow-md transition-all">
                        Generar e Imprimir
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Barra Flotante de Acciones Masivas -->
    <div id="bulkActionBar" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900/95 backdrop-blur-md text-white px-6 py-3.5 rounded-2xl shadow-2xl border border-slate-700/80 flex items-center gap-4 transition-all">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="text-xs font-bold text-white"><span id="selectedCountText">0</span> seleccionados</span>
        </div>
        <div class="h-4 w-px bg-slate-700"></div>
        <button type="button" onclick="openBulkConfirmModal('delete')" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition-colors flex items-center gap-1.5 shadow-md">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span>Eliminar definitivamente</span>
        </button>
        <button type="button" onclick="openBulkConfirmModal('archive')" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition-colors">
            <span>Archivar</span>
        </button>
        <button type="button" onclick="clearSelection()" class="text-xs text-slate-400 hover:text-white underline ml-1">
            Cancelar
        </button>
    </div>

    <!-- Modal de Confirmación de Acción Masiva -->
    <div id="modalBulkConfirm" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="card-surface p-6 sm:p-8 max-w-md w-full border border-slate-100 shadow-2xl space-y-4">
            <div class="flex items-center gap-3">
                <div id="bulkModalIcon" class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <div>
                    <h3 id="bulkModalTitle" class="font-extrabold text-slate-900 text-base">Eliminar productos seleccionados</h3>
                    <p id="bulkModalSubtitle" class="text-xs text-slate-500 mt-0.5"></p>
                </div>
            </div>

            <div id="bulkDeleteWarning" class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs leading-relaxed">
                <strong>⚠️ Advertencia:</strong> Esta acción eliminará los productos, sus imágenes y balances de stock. Esta operación no se puede deshacer.
            </div>

            <form id="bulkActionForm" action="{{ route('admin.products.bulk-action') }}" method="POST">
                @csrf
                <input type="hidden" name="action" id="bulkActionInput" value="delete">
                <input type="hidden" name="select_all_pages" id="bulkSelectAllPagesInput" value="0">
                <div id="bulkSelectedInputsContainer"></div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeBulkConfirmModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                        Cancelar
                    </button>
                    <button type="submit" id="bulkSubmitBtn" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all">
                        Confirmar y Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    let isEntireCatalogSelected = false;
    const totalCatalogCount = {{ $products->total() }};

    function toggleSelectAllPage(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.product-item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
            highlightRow(cb);
        });
        isEntireCatalogSelected = false;
        updateBulkUI();
    }

    function handleItemCheck() {
        const checkboxes = document.querySelectorAll('.product-item-checkbox');
        const checkedCount = document.querySelectorAll('.product-item-checkbox:checked').length;
        const master = document.getElementById('selectAllCheckbox');
        
        if (master) {
            master.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
        }
        
        checkboxes.forEach(cb => highlightRow(cb));
        isEntireCatalogSelected = false;
        updateBulkUI();
    }

    function highlightRow(checkbox) {
        const row = document.getElementById(`row-product-${checkbox.value}`);
        if (!row) return;
        if (checkbox.checked) {
            row.classList.add('bg-emerald-50/50');
        } else {
            row.classList.remove('bg-emerald-50/50');
        }
    }

    function updateBulkUI() {
        const checkedItems = document.querySelectorAll('.product-item-checkbox:checked');
        const count = checkedItems.length;
        const bar = document.getElementById('bulkActionBar');
        const banner = document.getElementById('selectAllCatalogBanner');
        const counterText = document.getElementById('selectedCountText');
        const pageCountText = document.getElementById('pageSelectedCount');

        if (count > 0) {
            bar.classList.remove('hidden');
            counterText.textContent = isEntireCatalogSelected ? `Todos (${totalCatalogCount})` : count;

            if (count === document.querySelectorAll('.product-item-checkbox').length && totalCatalogCount > count) {
                banner.classList.remove('hidden');
                pageCountText.textContent = count;
            } else {
                banner.classList.add('hidden');
            }
        } else {
            bar.classList.add('hidden');
            banner.classList.add('hidden');
            const master = document.getElementById('selectAllCheckbox');
            if (master) master.checked = false;
        }
    }

    function selectEntireCatalog() {
        isEntireCatalogSelected = true;
        document.getElementById('selectedCountText').textContent = `Todos (${totalCatalogCount})`;
        document.getElementById('selectAllCatalogBanner').innerHTML = `
            <div class="text-xs font-bold text-emerald-950">
                ✓ Se han seleccionado los <strong>${totalCatalogCount}</strong> productos del catálogo completo.
            </div>
            <button type="button" onclick="clearSelection()" class="text-xs text-emerald-700 hover:text-emerald-900 font-bold underline">
                Deseleccionar
            </button>
        `;
    }

    function clearSelection() {
        isEntireCatalogSelected = false;
        const checkboxes = document.querySelectorAll('.product-item-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = false;
            highlightRow(cb);
        });
        const master = document.getElementById('selectAllCheckbox');
        if (master) master.checked = false;
        updateBulkUI();
    }

    function openBulkConfirmModal(action) {
        const checkedItems = document.querySelectorAll('.product-item-checkbox:checked');
        const count = isEntireCatalogSelected ? totalCatalogCount : checkedItems.length;
        
        if (count === 0) {
            alert('Por favor seleccione al menos un producto.');
            return;
        }

        const modal = document.getElementById('modalBulkConfirm');
        const title = document.getElementById('bulkModalTitle');
        const subtitle = document.getElementById('bulkModalSubtitle');
        const warning = document.getElementById('bulkDeleteWarning');
        const submitBtn = document.getElementById('bulkSubmitBtn');
        const actionInput = document.getElementById('bulkActionInput');
        const selectAllInput = document.getElementById('bulkSelectAllPagesInput');
        const container = document.getElementById('bulkSelectedInputsContainer');

        actionInput.value = action;
        selectAllInput.value = isEntireCatalogSelected ? '1' : '0';
        container.innerHTML = '';

        if (!isEntireCatalogSelected) {
            checkedItems.forEach(cb => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'product_ids[]';
                hidden.value = cb.value;
                container.appendChild(hidden);
            });
        }

        if (action === 'delete') {
            title.textContent = isEntireCatalogSelected ? '¿Eliminar TODO el catálogo de productos?' : `¿Eliminar ${count} productos seleccionados?`;
            subtitle.textContent = isEntireCatalogSelected ? `Se eliminarán permanentemente los ${totalCatalogCount} productos registrados.` : `Se borrarán los ${count} productos seleccionados de la base de datos.`;
            warning.classList.remove('hidden');
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all';
            submitBtn.textContent = 'Sí, Eliminar Definitivamente';
        } else {
            title.textContent = isEntireCatalogSelected ? '¿Archivar todos los productos?' : `¿Archivar ${count} productos?`;
            subtitle.textContent = 'Los productos archivados ya no aparecerán en el catálogo público.';
            warning.classList.add('hidden');
            submitBtn.className = 'px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs shadow-md transition-all';
            submitBtn.textContent = 'Archivar Productos';
        }

        modal.classList.remove('hidden');
    }

    function closeBulkConfirmModal() {
        document.getElementById('modalBulkConfirm').classList.add('hidden');
    }
</script>
@endsection
