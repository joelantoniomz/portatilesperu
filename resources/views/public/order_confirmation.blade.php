@extends('layouts.public')

@section('title', 'Solicitud ' . $order->order_number . ' Confirmada - Portátiles Perú')
@section('meta_description', 'Tu pedido ' . $order->order_number . ' ha sido registrado exitosamente en Portátiles Perú. Abre WhatsApp para coordinar pago y entrega.')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">

    <!-- Tarjeta Principal de Éxito -->
    <div class="glass-card rounded-3xl p-6 sm:p-10 border border-emerald-500/30 text-center relative overflow-hidden shadow-2xl">
        <!-- Glow ambiental -->
        <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-4">
            <!-- Icono Éxito Animado -->
            <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-emerald-600 to-emerald-400 flex items-center justify-center text-forest-950 shadow-xl shadow-emerald-500/30 animate-bounce">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 text-xs font-black uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>¡Pedido Registrado con Éxito!</span>
            </span>

            <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                Código: <span class="text-emerald-400 font-mono">{{ $order->order_number }}</span>
            </h1>

            <p class="text-xs sm:text-sm text-slate-300 max-w-lg mx-auto leading-relaxed">
                Hola <strong class="text-white">{{ $order->customer_name }}</strong>, tu solicitud está registrada en nuestro sistema y el stock ha sido 
                <strong class="text-emerald-400">reservado preventivamente por 24 horas</strong>.
            </p>

            <!-- BOTÓN PRINCIPAL WHATSAPP (Aceptación estricta de Fase 6) -->
            <div class="pt-4 max-w-md mx-auto">
                <a
                    href="{{ $order->whatsapp_url }}"
                    target="_blank"
                    id="btnOpenWhatsApp"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-500 via-emerald-400 to-teal-400 hover:from-emerald-400 hover:to-teal-300 text-forest-950 font-black text-base shadow-2xl shadow-emerald-500/40 hover:scale-[1.03] active:scale-[0.98] transition-all flex items-center justify-center gap-3 group"
                >
                    <svg class="w-6 h-6 fill-current group-hover:rotate-12 transition-transform" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    <span>Abrir WhatsApp para Coordinar</span>
                </a>
                <p class="text-[11px] text-slate-400 mt-2">
                    ⚡ Se abrirá tu chat con el mensaje oficial estructurado y tu referencia <strong>{{ $order->order_number }}</strong>.
                </p>
            </div>
        </div>
    </div>

    <!-- Alerta de Reserva Preventiva -->
    <div class="glass-card rounded-2xl p-4 border border-amber-500/30 bg-amber-500/10 flex items-start gap-3.5">
        <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div class="text-xs space-y-1">
            <h3 class="font-bold text-amber-300">Reserva de Stock Temporal Activa (24 Horas)</h3>
            <p class="text-slate-300 leading-relaxed">
                Las unidades solicitadas han sido deducidas del inventario disponible temporalmente para que nadie más las compre mientras coordinas tu pago. Si no se coordina el pago en 24 horas, la reserva se libera automáticamente.
            </p>
        </div>
    </div>

    <!-- Detalle de Laptops del Pedido -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-emerald-500/20 space-y-6">
        <div class="flex items-center justify-between pb-4 border-b border-emerald-950">
            <h2 class="text-sm font-black text-white uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span>Resumen de la Orden</span>
            </h2>
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                Estado: {{ ucfirst($order->status) }}
            </span>
        </div>

        <!-- Tabla / Lista de Items -->
        <div class="divide-y divide-emerald-950">
            @foreach($order->items as $item)
            <div class="py-3.5 flex items-center justify-between gap-4">
                <div class="space-y-1">
                    <span class="font-mono text-[10px] text-emerald-400 font-bold block">{{ $item->product_sku }}</span>
                    <h3 class="text-xs sm:text-sm font-bold text-white">{{ $item->product_name }}</h3>
                    <p class="text-[11px] text-slate-400">
                        {{ $item->quantity }} x S/ {{ number_format($item->unit_price, 2) }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="font-mono text-sm sm:text-base font-black text-emerald-400">
                        S/ {{ number_format($item->subtotal, 2) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Totales -->
        <div class="pt-4 border-t border-emerald-950 space-y-2 text-xs">
            <div class="flex items-center justify-between text-slate-400">
                <span>Subtotal:</span>
                <span class="font-mono font-bold text-slate-200">S/ {{ number_format($order->subtotal, 2) }}</span>
            </div>
            @if($order->shipping_cost > 0)
            <div class="flex items-center justify-between text-slate-400">
                <span>Costo de Envío:</span>
                <span class="font-mono font-bold text-slate-200">S/ {{ number_format($order->shipping_cost, 2) }}</span>
            </div>
            @endif
            @if($order->discount > 0)
            <div class="flex items-center justify-between text-slate-400">
                <span>Descuento:</span>
                <span class="font-mono font-bold text-rose-400">- S/ {{ number_format($order->discount, 2) }}</span>
            </div>
            @endif
            <div class="pt-3 border-t border-emerald-950 flex items-baseline justify-between">
                <span class="text-sm font-bold text-white uppercase tracking-wider">Total a Pagar:</span>
                <span class="text-2xl sm:text-3xl font-black text-emerald-400 font-mono tracking-tight">
                    {{ $order->formatted_total }}
                </span>
            </div>
        </div>
    </div>

    <!-- Datos del Cliente y Despacho -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-3">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider text-emerald-400">Datos del Cliente</h3>
            <div class="text-xs space-y-2 text-slate-300">
                <p><strong class="text-white">Nombre:</strong> {{ $order->customer_name }}</p>
                <p><strong class="text-white">Teléfono:</strong> {{ $order->customer_phone }}</p>
                @if($order->customer_email)
                    <p><strong class="text-white">Email:</strong> {{ $order->customer_email }}</p>
                @endif
                <p><strong class="text-white">Comprobante:</strong> {{ ucfirst($order->invoice_type) }} ({{ strtoupper($order->document_type) }}: {{ $order->document_number ?? 'No especificado' }})</p>
            </div>
        </div>

        <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-3">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider text-emerald-400">Entrega y Sede</h3>
            <div class="text-xs space-y-2 text-slate-300">
                <p><strong class="text-white">Ciudad / Distrito:</strong> {{ $order->customer_city }}</p>
                @if($order->customer_address)
                    <p><strong class="text-white">Dirección / Agencia:</strong> {{ $order->customer_address }}</p>
                @endif
                <p><strong class="text-white">Almacén de Despacho:</strong> {{ $order->warehouse?->name ?? 'Sede Central' }}</p>
                @if($order->notes)
                    <p><strong class="text-white">Notas:</strong> {{ $order->notes }}</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Mensaje Copiable de WhatsApp en caso de que el enlace no abra -->
    <div class="glass-card rounded-3xl p-6 border border-emerald-500/20 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-300 uppercase tracking-wider">
                Mensaje generado para WhatsApp:
            </h3>
            <button
                type="button"
                onclick="copyWhatsAppMessage()"
                class="px-3 py-1 rounded-xl bg-forest-900 hover:bg-forest-800 text-emerald-400 border border-emerald-500/25 text-xs font-bold flex items-center gap-1.5 transition-colors"
            >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span id="copyBtnText">Copiar Mensaje</span>
            </button>
        </div>
        <pre id="waMessageBlock" class="p-4 rounded-2xl bg-forest-950 font-mono text-[11px] text-slate-300 whitespace-pre-wrap leading-relaxed border border-emerald-950">{{ $order->whatsapp_message }}</pre>
    </div>

    <!-- Enlaces de Retorno -->
    <div class="text-center pt-4">
        <a href="{{ route('catalogo') }}" class="text-xs font-bold text-emerald-400 hover:underline">
            &larr; Volver al Catálogo de Laptops
        </a>
    </div>

</div>

<script>
function copyWhatsAppMessage() {
    const text = document.getElementById('waMessageBlock').innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyBtnText');
        btn.innerText = '¡Copiado!';
        setTimeout(() => { btn.innerText = 'Copiar Mensaje'; }, 2000);
    });
}
</script>
@endsection
