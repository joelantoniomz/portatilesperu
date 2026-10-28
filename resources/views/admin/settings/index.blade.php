@extends('layouts.admin')

@section('title', 'Configuración del sistema')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <!-- Encabezado -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Administración del Sistema</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Configuración de Tienda y Apariencia</h2>
            <p class="text-slate-500 text-sm mt-1">
                Datos generales de la empresa, canal oficial de WhatsApp, logotipo corporativo y colores del sistema.
            </p>
        </div>
    </div>

    <!-- Formulario de Configuración Blanco y Verde -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 shadow-sm">
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
                        Canal Oficial de WhatsApp
                    </label>
                    <input 
                        type="text" 
                        id="whatsapp_number" 
                        name="whatsapp_number" 
                        value="{{ old('whatsapp_number', $settings['whatsapp_number'] ?? '') }}" 
                        placeholder="Ej: 51987654321" 
                        class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all font-mono"
                    >
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
                <button type="submit" class="px-6 py-3 rounded-2xl bg-[#0c3823] hover:bg-emerald-800 text-white font-bold text-xs shadow-md shadow-emerald-950/20 transition-all flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Guardar Configuración</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
