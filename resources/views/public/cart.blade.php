@extends('layouts.public')

@section('title', 'Carrito de Solicitud de Compra — Portátiles Perú')
@section('meta_description', 'Revisa tu solicitud de compra de laptops, reserva tu stock preventivamente y coordina tu entrega directamente por WhatsApp.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-slate-500 font-medium">
        <a href="{{ route('home') }}" class="hover:text-violet-600 transition-colors">Inicio</a>
        <span>/</span>
        <a href="{{ route('catalogo') }}" class="hover:text-violet-600 transition-colors">Catálogo</a>
        <span>/</span>
        <span class="text-slate-900 font-semibold">Carrito de Solicitud</span>
    </nav>

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3 font-display">
                <span class="p-2.5 rounded-2xl bg-violet-50 text-violet-600 border border-violet-100">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>
                <span>Tu Solicitud de Compra</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Al confirmar, se reservará el stock preventivo por 24 horas y se generará tu código de referencia antes de ir a WhatsApp.
            </p>
        </div>

        @if(!empty($items))
        <form action="{{ route('carrito.vaciar') }}" method="POST" onsubmit="return confirm('¿Está seguro de vaciar el carrito?');">
            @csrf
            <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-700 hover:underline flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <span>Vaciar Carrito</span>
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold flex items-center gap-3">
        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error') || $errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs space-y-1">
        <div class="flex items-center gap-2 font-bold text-rose-600">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <span>No se pudo procesar la solicitud:</span>
        </div>
        @if(session('error'))
            <p>{{ session('error') }}</p>
        @endif
        @foreach($errors->all() as $err)
            <p>&bull; {{ $err }}</p>
        @endforeach
    </div>
    @endif

    @if(empty($items))
    <!-- Estado de Carrito Vacío -->
    <div class="bg-white rounded-3xl p-12 text-center max-w-xl mx-auto space-y-6 border border-slate-200/80 shadow-sm">
        <div class="w-20 h-20 mx-auto rounded-3xl bg-violet-50 text-violet-600 flex items-center justify-center">
            <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </div>
        <div class="space-y-2">
            <h2 class="text-xl font-bold text-slate-900 font-display">Tu carrito está actualmente vacío</h2>
            <p class="text-xs text-slate-500 leading-relaxed">
                Aún no has seleccionado ninguna laptop de nuestro catálogo. Explora nuestras laptops gaming, ultrabooks y estaciones de trabajo con garantía oficial en Lima.
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('catalogo') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs shadow-md transition-all">
                <span>Explorar Catálogo de Laptops</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </div>
    @else

    <!-- Contenido del Carrito: Columnas Productos y Formulario -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        <!-- Lista de Productos en el Carrito (7 Columnas) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center justify-between border-b border-slate-100 pb-3">
                    <span>Laptops Seleccionadas ({{ count($items) }})</span>
                    <a href="{{ route('catalogo') }}" class="text-xs text-violet-600 hover:underline normal-case font-bold">
                        + Agregar más laptops
                    </a>
                </h2>

                <div class="divide-y divide-slate-100">
                    @foreach($items as $item)
                    @php $p = $item['product']; @endphp
                    <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4 flex-1">
                            <div class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-50 border border-slate-200/80 flex-shrink-0 flex items-center justify-center p-2">
                                <img src="{{ $p->primary_image_url }}" alt="{{ $p->name }}" class="w-full h-full object-contain">
                            </div>
                            <div class="space-y-1">
                                <span class="font-mono text-[10px] text-violet-600 font-bold">{{ $p->sku }}</span>
                                <h3 class="text-xs font-bold text-slate-900 hover:text-violet-600 transition-colors">
                                    <a href="{{ route('catalogo.detalle', $p->slug) }}" target="_blank">{{ $p->name }}</a>
                                </h3>
                                <p class="text-[11px] text-slate-500">
                                    Precio unitario: <span class="font-bold text-slate-800">{{ $p->formatted_price }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Selector de Cantidad y Subtotal -->
                        <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto pt-2 sm:pt-0 border-t sm:border-0 border-slate-100">
                            <!-- Formulario de Actualizar Cantidad -->
                            <form action="{{ route('carrito.actualizar') }}" method="POST" class="flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                <label for="qty_{{ $p->id }}" class="text-[10px] text-slate-500 font-bold">Cant:</label>
                                <select
                                    id="qty_{{ $p->id }}"
                                    name="quantity"
                                    onchange="this.form.submit()"
                                    class="bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1 text-xs text-slate-900 font-bold focus:outline-none focus:ring-1 focus:ring-violet-500 cursor-pointer"
                                >
                                    @for($q = 1; $q <= 10; $q++)
                                        <option value="{{ $q }}" {{ $item['quantity'] == $q ? 'selected' : '' }}>{{ $q }}</option>
                                    @endfor
                                </select>
                            </form>

                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Subtotal</span>
                                <span class="font-mono text-sm font-black text-slate-900 font-display">
                                    S/ {{ number_format($item['subtotal'], 2) }}
                                </span>
                            </div>

                            <!-- Botón Eliminar -->
                            <form action="{{ route('carrito.eliminar') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $p->id }}">
                                <button type="submit" title="Eliminar producto" class="p-2 rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Resumen Rápido de Stock Preventivo -->
                <div class="pt-4 border-t border-slate-100 flex items-center gap-3 text-xs text-slate-600 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>
                        <strong class="text-slate-900">Reserva oficial:</strong> Los productos seleccionados se bloquearán temporalmente para ti en almacén tan pronto confirmes el formulario.
                    </span>
                </div>
            </div>
        </div>

        <!-- Formulario de Checkout y Datos de Cliente (5 Columnas) -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-6 sticky top-24">
                <div>
                    <h2 class="text-base font-black text-slate-900 tracking-tight flex items-center gap-2 font-display">
                        <span>Datos de Coordinación & Entrega</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Ingresa tus datos para registrar el pedido y preparar tu mensaje de WhatsApp con referencia.
                    </p>
                </div>

                <form action="{{ route('pedido.checkout') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Nombre Completo -->
                    <div class="space-y-1">
                        <label for="customer_name" class="text-xs font-bold text-slate-700">
                            Nombre y Apellidos / Razón Social <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            value="{{ old('customer_name') }}"
                            required
                            placeholder="Ej. Juan Pérez Quispe"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                        >
                    </div>

                    <!-- Teléfono WhatsApp y Correo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="customer_phone" class="text-xs font-bold text-slate-700">
                                Teléfono WhatsApp <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="tel"
                                id="customer_phone"
                                name="customer_phone"
                                value="{{ old('customer_phone') }}"
                                required
                                placeholder="987 654 321"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                            >
                        </div>
                        <div class="space-y-1">
                            <label for="customer_email" class="text-xs font-bold text-slate-700">
                                Correo Electrónico <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input
                                type="email"
                                id="customer_email"
                                name="customer_email"
                                value="{{ old('customer_email') }}"
                                placeholder="juan@gmail.com"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                            >
                        </div>
                    </div>

                    <!-- Ciudad y Sede -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label for="customer_city" class="text-xs font-bold text-slate-700">
                                Ciudad / Distrito <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="customer_city"
                                name="customer_city"
                                value="{{ old('customer_city', 'Lima') }}"
                                required
                                placeholder="Lima / Miraflores"
                                class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                            >
                        </div>
                        <div class="space-y-1">
                            <label for="warehouse_id" class="text-xs font-bold text-slate-700">
                                Sede de Despacho
                            </label>
                            <select
                                id="warehouse_id"
                                name="warehouse_id"
                                class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500"
                            >
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }} ({{ $wh->city }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label for="customer_address" class="text-xs font-bold text-slate-700">
                            Dirección de Entrega / Agencia de Envíos <span class="text-slate-400">(Opcional)</span>
                        </label>
                        <input
                            type="text"
                            id="customer_address"
                            name="customer_address"
                            value="{{ old('customer_address') }}"
                            placeholder="Av. Principal 123 o Agencia Shalom / Olva"
                            class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                        >
                    </div>

                    <!-- Tipo de Comprobante -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider block">Tipo de Comprobante:</span>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 text-xs cursor-pointer hover:border-violet-500">
                                <input type="radio" name="invoice_type" value="boleta" {{ old('invoice_type', 'boleta') === 'boleta' ? 'checked' : '' }} class="text-violet-600 focus:ring-0">
                                <span class="font-bold text-slate-800">Boleta</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 text-xs cursor-pointer hover:border-violet-500">
                                <input type="radio" name="invoice_type" value="factura" {{ old('invoice_type') === 'factura' ? 'checked' : '' }} class="text-violet-600 focus:ring-0">
                                <span class="font-bold text-slate-800">Factura</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 text-xs cursor-pointer hover:border-violet-500">
                                <input type="radio" name="invoice_type" value="nota_venta" {{ old('invoice_type') === 'nota_venta' ? 'checked' : '' }} class="text-violet-600 focus:ring-0">
                                <span class="font-bold text-slate-800">Cotización</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notas adicionales -->
                    <div class="space-y-1">
                        <label for="notes" class="text-xs font-bold text-slate-700">
                            Notas o Instrucciones para el Asesor <span class="text-slate-400">(Opcional)</span>
                        </label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="2"
                            placeholder="Ej. Requiero instalación de Office o envío por Shalom..."
                            class="w-full px-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white"
                        >{{ old('notes') }}</textarea>
                    </div>

                    @php($cartTotal = $total ?? $subtotal ?? 0)
                    <!-- Resumen Económico -->
                    <div class="pt-4 border-t border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>Subtotal Estimado:</span>
                            <span class="font-mono text-slate-800 font-bold">S/ {{ number_format($cartTotal, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span>IGV (18% Incluido):</span>
                            <span class="font-mono text-slate-800 font-bold">S/ {{ number_format($cartTotal - ($cartTotal / 1.18), 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-base font-black text-slate-900 pt-2 border-t border-slate-200">
                            <span>Total a Coordinar:</span>
                            <span class="font-mono text-xl font-black text-slate-900 font-display">
                                S/ {{ number_format($cartTotal, 2) }}
                            </span>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-4 px-6 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-sm shadow-xl shadow-emerald-600/20 transition-all flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span>Reservar Stock & Coordinar por WhatsApp</span>
                    </button>
                </form>
            </div>
        </div>

    </div>
    @endif

</div>
@endsection
