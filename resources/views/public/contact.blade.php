@extends('layouts.public')

@section('title', 'Contacto y Sedes en Lima - Portátiles Perú')
@section('meta_description', 'Comunícate con Portátiles Perú. Atención en tienda física en Miraflores y Centro de Lima. Cotizaciones por WhatsApp y envíos a todo el Perú.')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">

    <!-- Encabezado -->
    <div class="text-center space-y-4 max-w-2xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
            <span>Canales Oficiales de Atención</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
            Estamos Listos para Atenderte
        </h1>
        <p class="text-slate-300 text-sm leading-relaxed">
            Visítanos en nuestras tiendas en Lima o contáctanos por WhatsApp para cotizaciones personalizadas y reservas inmediatas.
        </p>
    </div>

    <!-- Canales Rápidos -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ \App\Models\Setting::getWhatsappUrl('Hola Portatiles Peru, deseo comunicarme con un asesor') }}" target="_blank" class="glass-card rounded-3xl p-6 border border-emerald-500/20 hover:border-emerald-500/50 transition-all text-center space-y-3 group">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto group-hover:scale-110 transition-transform">
                <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
            </div>
            <h3 class="font-bold text-white text-base">WhatsApp de Ventas</h3>
            <p class="text-xs text-slate-400">Atención rápida y cotizaciones en tiempo real.</p>
            <span class="inline-block font-mono text-emerald-400 font-bold text-sm">+51 987 654 321</span>
        </a>

        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
            </div>
            <h3 class="font-bold text-white text-base">Central Telefónica</h3>
            <p class="text-xs text-slate-400">Consultas de pedidos y soporte técnico.</p>
            <span class="inline-block font-mono text-white font-bold text-sm">(01) 480-1234</span>
        </div>

        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="font-bold text-white text-base">Correo Corporativo</h3>
            <p class="text-xs text-slate-400">Cotizaciones para empresas e instituciones.</p>
            <span class="inline-block font-mono text-emerald-400 font-bold text-xs">ventas@portatilesperu.pe</span>
        </div>
    </div>

    <!-- Sedes Físicas en Lima -->
    <div class="glass-card rounded-3xl p-8 border border-emerald-500/20 space-y-6">
        <h3 class="text-xl font-black text-white tracking-tight">Nuestras Tiendas y Sedes en Lima</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-5 rounded-2xl bg-forest-900 border border-emerald-500/15 space-y-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300">Sede Principal & Tienda</span>
                <h4 class="text-base font-bold text-white">Tienda Miraflores</h4>
                <p class="text-xs text-slate-400">
                    📍 Av. Petit Thouars 5356, Miraflores, Lima.<br>
                    ⏰ Lunes a Sábado: 10:00 am – 7:30 pm.<br>
                    🚗 Estacionamiento para clientes disponible.
                </p>
            </div>

            <div class="p-5 rounded-2xl bg-forest-900 border border-emerald-500/15 space-y-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300">Almacén de Distribución</span>
                <h4 class="text-base font-bold text-white">Almacén Central Lima</h4>
                <p class="text-xs text-slate-400">
                    📍 Jr. Camaná 1120, Cercado de Lima.<br>
                    ⏰ Lunes a Viernes: 9:00 am – 6:00 pm.<br>
                    📦 Despachos mayoristas y envíos a provincia.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
