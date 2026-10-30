@extends('layouts.admin')

@section('title', 'Configuración del sistema')

@section('content')
<div class="mx-auto max-w-4xl space-y-8">
    <!-- Encabezado -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Administración del Sistema</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Configuración, Respaldos y Restablecimiento</h2>
            <p class="text-slate-500 text-sm mt-1">
                Datos de la empresa, canal de WhatsApp, copias de seguridad de datos y restablecimiento de fábrica a cero.
            </p>
        </div>
    </div>

    <!-- 1. Formulario de Configuración General -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 shadow-sm">
        <div class="flex items-center gap-2.5 mb-6 pb-4 border-b border-slate-100">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm">
                ⚙️
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Datos Generales y Canal de Ventas</h3>
                <p class="text-xs text-slate-500">Información visible para los clientes en la tienda pública y catálogo PDF.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="company_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nombre de la Empresa *
                    </label>
                    <input 
                        type="text" 
                        id="company_name" 
                        name="company_name" 
                        required 
                        value="{{ old('company_name', $settings['company_name'] ?? 'Portátiles Perú') }}" 
                        placeholder="Ej: Portátiles Perú" 
                        class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-semibold"
                    >
                </div>

                <div>
                    <label for="whatsapp_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Canal Oficial de WhatsApp (Para compras directas)
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="whatsapp_number" 
                            name="whatsapp_number" 
                            value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '51987654321') }}" 
                            placeholder="Ej: 51987654321" 
                            class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono"
                        >
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Incluya el código de país sin espacios ni símbolos (Ej: 51987654321 para Perú).</span>
                </div>
            </div>

            <!-- Sección de Logotipo (Subir archivo o URL) -->
            <div class="p-5 rounded-3xl bg-slate-50 border border-slate-200/80 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Logotipo Corporativo</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Puedes subir un archivo de imagen (PNG, JPG, SVG, WebP) o ingresar un enlace URL directo.</p>
                    </div>
                    @if(!empty($settings['company_logo_url']))
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Logo actual:</span>
                        <div class="w-12 h-12 rounded-xl bg-white p-1 border border-slate-200 flex items-center justify-center shadow-xs">
                            <img src="{{ $settings['company_logo_url'] }}" alt="Logo Empresa" class="max-w-full max-h-full object-contain">
                        </div>
                    </div>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Subir Archivo de Imagen</label>
                        <input 
                            type="file" 
                            name="company_logo_file" 
                            accept="image/png,image/jpeg,image/svg+xml,image/webp" 
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#0c3823] file:text-white hover:file:bg-emerald-800 cursor-pointer"
                        >
                        <span class="text-[10px] text-slate-400 block mt-1">Recomendado: Fondo transparente PNG o SVG, máx 2MB.</span>
                    </div>

                    <div>
                        <label for="company_logo_url" class="block text-xs font-semibold text-slate-600 mb-1">O ingresar URL del Logo</label>
                        <input 
                            type="url" 
                            id="company_logo_url" 
                            name="company_logo_url" 
                            value="{{ old('company_logo_url', $settings['company_logo_url'] ?? '') }}" 
                            placeholder="https://ejemplo.com/logo.png" 
                            class="w-full px-4 py-2 rounded-2xl border border-slate-200 bg-white text-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        >
                    </div>
                </div>
            </div>

            <!-- Color Principal del Sistema -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                    Color Principal del Sistema
                </label>
                <div class="flex flex-wrap gap-3">
                    @foreach(['emerald' => ['name' => 'Verde Esmeralda', 'color' => 'bg-emerald-500', 'border' => 'peer-checked:border-emerald-500 peer-checked:bg-emerald-50'], 'blue' => ['name' => 'Azul Corporativo', 'color' => 'bg-blue-600', 'border' => 'peer-checked:border-blue-500 peer-checked:bg-blue-50'], 'violet' => ['name' => 'Violeta Moderno', 'color' => 'bg-violet-600', 'border' => 'peer-checked:border-violet-500 peer-checked:bg-violet-50']] as $value => $meta)
                    <label class="cursor-pointer">
                        <input type="radio" name="theme_color" value="{{ $value }}" {{ old('theme_color', $settings['theme_color'] ?? 'emerald') === $value ? 'checked' : '' }} class="peer sr-only">
                        <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 {{ $meta['border'] }} peer-checked:shadow-xs transition-all">
                            <span class="w-3.5 h-3.5 rounded-full {{ $meta['color'] }}"></span>
                            <span class="text-xs font-bold text-slate-700">{{ $meta['name'] }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-[#0c3823] hover:bg-emerald-800 text-white font-bold text-xs shadow-md shadow-emerald-950/20 transition-all flex items-center gap-2 hover:scale-[1.01]">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Guardar Configuración General</span>
                </button>
            </div>
        </form>
    </div>

    <!-- 2. Copias de Seguridad (Exportar / Importar Backup) -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 shadow-sm space-y-6">
        <div class="flex items-center gap-2.5 pb-4 border-b border-slate-100">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-sm">
                📦
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Copias de Seguridad (Backup)</h3>
                <p class="text-xs text-slate-500">Exporte una copia completa de su catálogo e inventario o restaure un archivo previo.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Exportar -->
            <div class="p-5 rounded-3xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div>
                    <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Exportar Copia de Seguridad</span>
                    </h4>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Descarga un archivo JSON con todos los productos, fichas técnicas, categorías, marcas, imágenes y balances de inventario actuales.
                    </p>
                    <div class="mt-3 flex items-center gap-2 text-[11px] text-slate-600 font-medium">
                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-bold">{{ $counts['products'] }} Laptops</span>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-bold">{{ $counts['categories'] }} Categorías</span>
                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-bold">{{ $counts['brands'] }} Marcas</span>
                    </div>
                </div>

                <a href="{{ route('admin.settings.backup.export') }}" class="w-full py-3 px-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Descargar Backup (JSON)</span>
                </a>
            </div>

            <!-- Importar / Restaurar -->
            <div class="p-5 rounded-3xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div>
                    <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Restaurar Copia de Seguridad</span>
                    </h4>
                    <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                        Suba un archivo de respaldo previo en formato JSON para reincorporar productos, taxonomías e inventario a la base de datos.
                    </p>
                </div>

                <form action="{{ route('admin.settings.backup.import') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input 
                        type="file" 
                        name="backup_file" 
                        accept=".json,application/json,text/plain" 
                        required 
                        class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer"
                    >
                    <button type="submit" onclick="return confirm('¿Confirma que desea restaurar los datos de este archivo? Los productos existentes se actualizarán y los faltantes se crearán.')" class="w-full py-2.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2">
                        <span>Restaurar Datos desde Archivo</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. Zona de Peligro: Restablecer Datos de Fábrica (Poner a CERO) -->
    <div class="card-surface p-6 sm:p-8 border-2 border-rose-200/80 shadow-md space-y-6">
        <div class="flex items-center gap-2.5 pb-4 border-b border-rose-100">
            <div class="w-9 h-9 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-base">
                ⚠️
            </div>
            <div>
                <h3 class="font-black text-rose-950 text-base">Restablecer Datos de Fábrica (Poner Todo a Cero)</h3>
                <p class="text-xs text-rose-700">Elimina el catálogo, inventario, pedidos o chats para empezar desde cero como una tienda nueva.</p>
            </div>
        </div>

        <!-- Indicadores Actuales -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Laptops registradas</span>
                <p class="text-lg font-black text-slate-900 mt-0.5">{{ $counts['products'] }}</p>
            </div>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Movimientos Stock</span>
                <p class="text-lg font-black text-slate-900 mt-0.5">{{ $counts['movements'] }}</p>
            </div>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Pedidos / Ventas</span>
                <p class="text-lg font-black text-slate-900 mt-0.5">{{ $counts['orders'] }}</p>
            </div>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-center">
                <span class="text-[10px] font-bold text-slate-400 uppercase">Chats WhatsApp</span>
                <p class="text-lg font-black text-slate-900 mt-0.5">{{ $counts['conversations'] }}</p>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed space-y-1">
            <strong class="font-bold flex items-center gap-1.5 text-amber-950">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                ¿Qué pasará al restablecer?
            </strong>
            <p>
                Los contadores del dashboard (Total productos, Stock disponible, Ventas y Pedidos) volverán a <strong>CERO (0)</strong> inmediatamente.
                Tus cuentas de usuario administrativo, contraseñas y permisos <strong>se conservarán intactas</strong> para que no pierdas acceso al sistema.
            </p>
        </div>

        <form action="{{ route('admin.settings.factory-reset') }}" method="POST" class="space-y-5" onsubmit="return validateFactoryReset(event)">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Seleccione los módulos que desea vaciar a cero:</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <label class="flex items-start gap-2.5 p-3 rounded-2xl bg-white border border-slate-200 cursor-pointer hover:border-rose-300">
                        <input type="checkbox" name="reset_catalog" value="1" checked class="mt-0.5 w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <div>
                            <span class="font-bold text-slate-900 block">Vaciar Catálogo e Inventario</span>
                            <span class="text-[11px] text-slate-500">Borra todos los productos, fotos, especificaciones y balances de stock.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-3 rounded-2xl bg-white border border-slate-200 cursor-pointer hover:border-rose-300">
                        <input type="checkbox" name="reset_orders" value="1" checked class="mt-0.5 w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <div>
                            <span class="font-bold text-slate-900 block">Vaciar Pedidos y Ventas</span>
                            <span class="text-[11px] text-slate-500">Borra el historial de órdenes, reservas y transacciones de venta.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-3 rounded-2xl bg-white border border-slate-200 cursor-pointer hover:border-rose-300">
                        <input type="checkbox" name="reset_chats" value="1" checked class="mt-0.5 w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <div>
                            <span class="font-bold text-slate-900 block">Vaciar Chats y Clientes</span>
                            <span class="text-[11px] text-slate-500">Borra las conversaciones registradas de WhatsApp y libreta de clientes.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-2.5 p-3 rounded-2xl bg-white border border-slate-200 cursor-pointer hover:border-rose-300">
                        <input type="checkbox" name="reset_taxonomies" value="1" class="mt-0.5 w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300">
                        <div>
                            <span class="font-bold text-slate-900 block">Vaciar Categorías y Marcas (Opcional)</span>
                            <span class="text-[11px] text-slate-500">Borra también los nombres de categorías y marcas para crearlas desde cero.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="p-4 rounded-2xl bg-rose-50/70 border border-rose-200 space-y-2">
                <label for="confirmationInput" class="block text-xs font-black text-rose-900 uppercase tracking-wider">
                    Para confirmar, escriba la palabra <span class="underline text-rose-700">RESTABLECER</span> en mayúsculas:
                </label>
                <input 
                    type="text" 
                    id="confirmationInput" 
                    name="confirmation" 
                    required 
                    placeholder="RESTABLECER" 
                    autocomplete="off"
                    class="w-full sm:max-w-xs px-4 py-2.5 rounded-xl border border-rose-300 bg-white text-rose-950 font-mono font-black text-sm focus:outline-none focus:ring-2 focus:ring-rose-500"
                >
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-lg shadow-rose-600/30 transition-all flex items-center gap-2 hover:scale-[1.01]">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span>Restablecer Todo de Fábrica a Cero</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function validateFactoryReset(e) {
        const input = document.getElementById('confirmationInput').value.trim();
        if (input !== 'RESTABLECER') {
            alert('Debe escribir exactamente "RESTABLECER" en mayúsculas para confirmar.');
            e.preventDefault();
            return false;
        }
        return confirm('¿ESTÁ TOTALMENTE SEGURO? Esta acción vaciará el catálogo y dejará los contadores en cero.');
    }
</script>
@endsection
