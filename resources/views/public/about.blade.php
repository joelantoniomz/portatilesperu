@extends('layouts.public')

@section('title', 'Sobre Nosotros - Portátiles Perú')
@section('meta_description', 'Conoce más sobre Portátiles Perú: especialistas en laptops de alto rendimiento, garantías oficiales, soporte técnico y cobertura a nivel nacional.')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">

    <!-- Encabezado -->
    <div class="text-center space-y-4 max-w-2xl mx-auto">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
            <span>Especialistas en Cómputo Portátil</span>
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
            Potenciando el Talento Profesional y Gamer en el Perú
        </h1>
        <p class="text-slate-300 text-sm leading-relaxed">
            En <strong>Portátiles Perú</strong> creemos que la computadora adecuada transforma la productividad de un ingeniero, la creatividad de un diseñador y el rendimiento de un gamer.
        </p>
    </div>

    <!-- 3 Tarjetas de Propuesta de Valor -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-black text-lg">
                01
            </div>
            <h3 class="font-bold text-white text-base">Importación Directa</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Traemos los últimos lanzamientos de procesadores AMD Ryzen Serie 7000/8000, Intel Core Ultra y tarjetas NVIDIA GeForce RTX 40 Series apenas salen al mercado global.
            </p>
        </div>

        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-black text-lg">
                02
            </div>
            <h3 class="font-bold text-white text-base">Garantía Real de 1 a 2 Años</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                Todos nuestros equipos son 100% nuevos en caja sellada con soporte técnico local en Lima, stock de repuestos originales y cambio inmediato ante fallas de fábrica.
            </p>
        </div>

        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-black text-lg">
                03
            </div>
            <h3 class="font-bold text-white text-base">Asesoría de Ingeniero a Cliente</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
                No somos solo vendedores. Analizamos los requerimientos exactos de software que utilizarás (AutoCAD, SolidWorks, Blender, DaVinci Resolve) para recomendarte la configuración óptima.
            </p>
        </div>
    </div>

    <!-- Datos Corporativos y Legalidad -->
    <div class="glass-card rounded-3xl p-8 border border-emerald-500/20 space-y-4">
        <h3 class="text-lg font-black text-white">Transparencia y Legalidad</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-forest-900/80 border border-emerald-500/15">
                <span class="text-slate-400 block font-semibold">Razón Social:</span>
                <span class="text-white font-bold">PORTÁTILES PERÚ S.A.C.</span>
            </div>
            <div class="p-4 rounded-2xl bg-forest-900/80 border border-emerald-500/15">
                <span class="text-slate-400 block font-semibold">RUC:</span>
                <span class="text-emerald-400 font-mono font-bold">20608976541</span>
            </div>
            <div class="p-4 rounded-2xl bg-forest-900/80 border border-emerald-500/15">
                <span class="text-slate-400 block font-semibold">Sede Principal:</span>
                <span class="text-white font-bold">Miraflores, Lima</span>
            </div>
            <div class="p-4 rounded-2xl bg-forest-900/80 border border-emerald-500/15">
                <span class="text-slate-400 block font-semibold">Atención:</span>
                <span class="text-white font-bold">Lunes a Sábado 9am - 7pm</span>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="text-center pt-4">
        <a href="{{ route('catalogo') }}" class="px-8 py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-forest-950 font-black text-sm shadow-xl shadow-emerald-500/25 transition-all inline-flex items-center gap-2">
            <span>Ver Catálogo de Laptops</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    </div>

</div>
@endsection
