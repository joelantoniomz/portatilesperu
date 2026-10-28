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
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-[10px] font-extrabold text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Producto</th>
                        <th class="px-4 py-3">SKU / Códigos</th>
                        <th class="px-4 py-3">Categoría & Marca</th>
                        <th class="px-4 py-3">Precio</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right rounded-r-xl">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/60 transition-colors">
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

</div>
@endsection
