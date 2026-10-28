@extends('layouts.admin')

@section('title', 'Plantilla y Guía de Carga Masiva - Portátiles Perú')

@section('content')
<div class="space-y-6">

    <!-- Encabezado con Migas de Pan -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('admin.products.index') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 transition-colors">
                    &larr; Volver al Catálogo
                </a>
                <span class="text-slate-300">/</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                    <span>Guía de Importación CSV / Excel</span>
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Plantilla Oficial para Carga Masiva de Laptops</h1>
            <p class="text-slate-500 text-sm mt-1">
                Visualice el formato exacto de las columnas, descargue el archivo compatible con Microsoft Excel y revise las instrucciones de llenado antes de subir su catálogo.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.products.import-template') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 transition-all hover:scale-[1.01]">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Descargar Plantilla CSV (Excel)</span>
            </a>
            <a href="{{ route('admin.products.index') }}" class="px-4 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                Ir a Productos
            </a>
        </div>
    </div>

    <!-- 3 Pasos Rápidos para Cargar -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card-surface p-5 border border-slate-100 rounded-2xl space-y-2">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-emerald-500 text-white font-black text-xs flex items-center justify-center">1</span>
                <h3 class="text-sm font-bold text-slate-900">Descargar y Abrir</h3>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                Descargue el archivo CSV con codificación <strong>UTF-8 con BOM</strong>. Ábralo directamente en Microsoft Excel, LibreOffice Calc o Google Sheets sin problemas de tildes ni caracteres especiales.
            </p>
        </div>

        <div class="card-surface p-5 border border-slate-100 rounded-2xl space-y-2">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-emerald-500 text-white font-black text-xs flex items-center justify-center">2</span>
                <h3 class="text-sm font-bold text-slate-900">Completar Datos</h3>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                Complete una fila por cada laptop. Asegúrese de que el <strong>SKU sea único</strong> y que los nombres de <strong>Categoría</strong> y <strong>Marca</strong> coincidan con los registrados en la plataforma.
            </p>
        </div>

        <div class="card-surface p-5 border border-slate-100 rounded-2xl space-y-2">
            <div class="flex items-center gap-2.5">
                <span class="w-7 h-7 rounded-xl bg-emerald-500 text-white font-black text-xs flex items-center justify-center">3</span>
                <h3 class="text-sm font-bold text-slate-900">Subir y Validar</h3>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                Guarde como CSV y suba el archivo mediante el módulo inferior. El servidor validará la unicidad de SKU, códigos EAN y registrará las imágenes de inmediato.
            </p>
        </div>
    </div>

    <!-- Módulo de Carga Directa (Opcional en esta pantalla) -->
    <div class="card-surface p-6 border border-emerald-100 bg-gradient-to-r from-emerald-50/50 via-white to-slate-50 rounded-3xl">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="space-y-1">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">¿Ya tiene lista su plantilla?</span>
                <h2 class="text-lg font-black text-slate-900">Subir archivo CSV completado ahora mismo</h2>
                <p class="text-xs text-slate-500">Seleccione el archivo CSV guardado para procesar la importación masiva en lote.</p>
            </div>

            <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                @csrf
                <input type="file" name="products_file" accept=".csv,text/csv" required class="text-xs text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer border border-slate-200 rounded-2xl p-1.5 bg-white">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md transition-colors whitespace-nowrap">
                    Iniciar Importación
                </button>
            </form>
        </div>
    </div>

    <!-- Previsualización de Datos de Ejemplo (Spreadsheet View) -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs">A</span>
                    <span>Previsualización del Archivo CSV / Excel (Ejemplo con 3 Laptops)</span>
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Así se estructuran las filas en la hoja de cálculo. Deslice horizontalmente para ver todas las especificaciones e imágenes.</p>
            </div>
            <a href="{{ route('admin.products.import-template') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                <span>Descargar archivo .csv limpio</span>
            </a>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-inner">
            <table class="w-full text-left text-xs whitespace-nowrap border-collapse">
                <thead class="bg-slate-900 text-white font-mono text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="px-3.5 py-3 border-r border-slate-800 bg-emerald-900 text-emerald-200">#</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">sku *</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">name *</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">price *</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">currency *</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">category *</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">brand *</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">status *</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">short_description</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">barcode</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">procesador</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">ram</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">almacenamiento</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">pantalla</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">grafica</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">sistema_operativo</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">conectividad</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">garantia</th>
                        <th class="px-3.5 py-3 border-r border-slate-800">image_url_1</th>
                        <th class="px-3.5 py-3">image_url_2</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white font-mono text-[11px] text-slate-700">
                    @foreach($sampleRows as $index => $row)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-3.5 py-2.5 font-bold bg-slate-100 text-slate-600 border-r border-slate-200">{{ $index + 1 }}</td>
                        <td class="px-3.5 py-2.5 font-bold text-slate-900 border-r border-slate-100">{{ $row['sku'] }}</td>
                        <td class="px-3.5 py-2.5 font-sans font-semibold text-slate-900 border-r border-slate-100 max-w-xs truncate">{{ $row['name'] }}</td>
                        <td class="px-3.5 py-2.5 font-bold text-emerald-700 border-r border-slate-100">{{ $row['price'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['currency'] }}</td>
                        <td class="px-3.5 py-2.5 font-sans border-r border-slate-100"><span class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-800 text-[10px] font-bold">{{ $row['category'] }}</span></td>
                        <td class="px-3.5 py-2.5 font-sans border-r border-slate-100"><span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 text-[10px] font-bold">{{ $row['brand'] }}</span></td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100"><span class="px-2 py-0.5 rounded {{ $row['status'] === 'published' ? 'bg-sky-50 text-sky-700' : 'bg-amber-50 text-amber-700' }} text-[10px] font-bold">{{ $row['status'] }}</span></td>
                        <td class="px-3.5 py-2.5 font-sans max-w-xs truncate border-r border-slate-100">{{ $row['short_description'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['barcode'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['procesador'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['ram'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['almacenamiento'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['pantalla'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['grafica'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['sistema_operativo'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['conectividad'] }}</td>
                        <td class="px-3.5 py-2.5 border-r border-slate-100">{{ $row['garantia'] }}</td>
                        <td class="px-3.5 py-2.5 max-w-xs truncate text-sky-600 border-r border-slate-100">{{ $row['image_url_1'] }}</td>
                        <td class="px-3.5 py-2.5 max-w-xs truncate text-sky-600">{{ $row['image_url_2'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Diccionario Completo de Columnas y Reglas -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 space-y-4">
        <div>
            <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center text-xs">B</span>
                <span>Diccionario Detallado de Columnas</span>
            </h3>
            <p class="text-xs text-slate-400 mt-0.5">Revise qué datos acepta cada campo y los requerimientos de validación del sistema.</p>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3">Nombre Columna</th>
                        <th class="px-4 py-3">Etiqueta</th>
                        <th class="px-4 py-3">Obligatorio</th>
                        <th class="px-4 py-3">Valores Aceptados / Reglas</th>
                        <th class="px-4 py-3">Ejemplo Práctico</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($columns as $col)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3 font-mono font-bold text-slate-900">{{ $col['key'] }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $col['label'] }}</td>
                        <td class="px-4 py-3">
                            @if($col['required'])
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Requerido *</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">Opcional</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600 leading-relaxed">{{ $col['desc'] }}</td>
                        <td class="px-4 py-3 font-mono text-[11px] text-emerald-800 bg-emerald-50/40 rounded">{{ $col['example'] ?: '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Valores Válidos de Categorías y Marcas Registradas en Sistema -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card-surface p-6 border border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Categorías Activas en Sistema ({{ $categories->count() }})</h4>
                <a href="{{ route('admin.categories.index') }}" class="text-xs text-emerald-600 font-bold hover:underline">Gestionar &rarr;</a>
            </div>
            <p class="text-xs text-slate-500">En la columna <code>category</code> debe escribir el nombre exacto de una de estas categorías:</p>
            <div class="flex flex-wrap gap-1.5 pt-1">
                @foreach($categories as $category)
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800">
                    {{ $category->name }}
                </span>
                @endforeach
            </div>
        </div>

        <div class="card-surface p-6 border border-slate-100 space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-bold text-slate-900">Marcas Activas en Sistema ({{ $brands->count() }})</h4>
                <a href="{{ route('admin.brands.index') }}" class="text-xs text-emerald-600 font-bold hover:underline">Gestionar &rarr;</a>
            </div>
            <p class="text-xs text-slate-500">En la columna <code>brand</code> debe escribir el nombre exacto de una de estas marcas:</p>
            <div class="flex flex-wrap gap-1.5 pt-1">
                @foreach($brands as $brand)
                <span class="px-2.5 py-1 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-800">
                    {{ $brand->name }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection

