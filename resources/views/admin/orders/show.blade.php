@extends('layouts.admin')

@section('title', 'Pedido ' . $order->order_number . ' - Portátiles Perú')

@section('content')
<div class="space-y-6">

    <!-- Migas de Pan -->
    <nav class="flex items-center gap-2 text-xs text-emerald-100/70 font-medium">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.orders.index') }}" class="hover:text-white transition-colors">Pedidos</a>
        <span>/</span>
        <span class="text-white font-mono font-bold">{{ $order->order_number }}</span>
    </nav>

    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-white tracking-tight font-mono">
                    {{ $order->order_number }}
                </h1>
                @php
                    $statusStyles = [
                        'pending'        => 'bg-amber-100 text-amber-800 border-amber-300',
                        'confirmed'      => 'bg-sky-100 text-sky-800 border-sky-300',
                        'in_preparation' => 'bg-purple-100 text-purple-800 border-purple-300',
                        'dispatched'     => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                        'completed'      => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'cancelled'      => 'bg-rose-100 text-rose-800 border-rose-300',
                    ];
                    $statusLabels = [
                        'pending'        => 'Pendiente de Pago',
                        'confirmed'      => 'Pago Confirmado',
                        'in_preparation' => 'En Preparación / Embalaje',
                        'dispatched'     => 'Despachado / En Ruta',
                        'completed'      => 'Completado / Entregado',
                        'cancelled'      => 'Cancelado (Stock Liberado)',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-black uppercase border shadow-sm {{ $statusStyles[$order->status] ?? 'bg-slate-100 text-slate-700 border-slate-300' }}">
                    {{ $statusLabels[$order->status] ?? $order->status }}
                </span>
            </div>
            <p class="text-xs text-emerald-100/70 mt-1">
                Registrado el {{ $order->created_at->format('d/m/Y \a \l\a\s H:i') }} &bull; Almacén: <strong class="text-white">{{ $order->warehouse?->name ?? 'Central' }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2.5">
            @if($order->status === 'pending')
            <form action="{{ route('admin.orders.confirmPayment', $order) }}" method="POST" class="inline" onsubmit="return confirm('¿Confirmar que el pago de {{ $order->formatted_total }} ha sido verificado con éxito?');">
                @csrf
                <button
                    type="submit"
                    class="px-4 py-2.5 rounded-2xl bg-sky-400 hover:bg-sky-300 text-slate-950 font-black text-xs shadow-md hover:shadow transition-all flex items-center gap-1.5 hover:scale-[1.02]"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>✓ Confirmar Pago</span>
                </button>
            </form>
            @endif

            <a
                href="{{ $order->whatsapp_url }}"
                target="_blank"
                class="px-4 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-md hover:shadow transition-all flex items-center gap-2 hover:scale-[1.02]"
            >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                </svg>
                <span>Abrir Chat WhatsApp</span>
            </a>
            <a
                href="{{ route('admin.orders.index') }}"
                class="px-4 py-2.5 rounded-2xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200 shadow-sm transition-colors"
            >
                Volver a la lista
            </a>
        </div>
    </div>

    <!-- Mensajes Flash -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-white border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3 shadow-sm">
        <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-semibold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Columna Izquierda: Items del Pedido y Reservas (7 Columnas) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- Laptops Solicitadas (Tarjeta Blanca) -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span>Laptops y Productos Solicitados</span>
                </h2>

                <div class="divide-y divide-slate-100">
                    @foreach($order->items as $item)
                    <div class="py-4 space-y-2">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <span class="font-mono text-[10px] text-emerald-700 font-bold block">{{ $item->product_sku }}</span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900">{{ $item->product_name }}</h3>
                                <p class="text-[11px] text-slate-500">
                                    {{ $item->quantity }} unidad(es) &times; S/ {{ number_format($item->unit_price, 2) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">Subtotal</span>
                                <span class="font-mono text-base font-black text-slate-900">
                                    S/ {{ number_format($item->subtotal, 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Estado de Reserva de Stock asociada -->
                        @if($item->reservation)
                        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px]">
                            <span class="text-slate-600">
                                Reserva N° <strong class="text-slate-900 font-mono">#{{ $item->reservation->id }}</strong> ({{ $item->reservation->quantity }} unid.)
                            </span>
                            @if($item->reservation->status === 'active')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    Reserva Activa (Expira: {{ $item->reservation->expires_at?->format('d/m H:i') ?? '24h' }})
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    Reserva {{ ucfirst($item->reservation->status) }}
                                </span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <!-- Desglose de Totales -->
                <div class="pt-4 border-t border-slate-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Subtotal:</span>
                        <span class="font-mono font-bold text-slate-800">S/ {{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->shipping_cost > 0)
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Costo de Envío:</span>
                        <span class="font-mono font-bold text-slate-800">S/ {{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                    @endif
                    @if($order->discount > 0)
                    <div class="flex items-center justify-between text-slate-500">
                        <span>Descuento:</span>
                        <span class="font-mono font-bold text-rose-600">- S/ {{ number_format($order->discount, 2) }}</span>
                    </div>
                    @endif
                    <div class="pt-3 border-t border-slate-100 flex items-baseline justify-between">
                        <span class="text-sm font-bold text-slate-900 uppercase tracking-wider">Total a Cobrar:</span>
                        <span class="text-2xl font-black text-emerald-700 font-mono">
                            {{ $order->formatted_total }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Historial de Estados y Auditoría (Tarjeta Blanca) -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Historial de Estados y Trazabilidad</span>
                </h2>

                <div class="space-y-3">
                    @forelse($order->statusHistory as $hist)
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-800">
                                {{ $hist->status_from ? ucfirst($hist->status_from) . ' → ' : '' }}
                                <span class="text-emerald-700 font-bold">{{ ucfirst($hist->status_to) }}</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $hist->created_at->format('d/m/Y H:i:s') }}</span>
                        </div>
                        @if($hist->notes)
                            <p class="text-slate-600 text-[11px] italic">{{ $hist->notes }}</p>
                        @endif
                        <span class="text-[10px] text-slate-400 block">
                            Usuario: {{ $hist->user?->name ?? 'Sistema Automático' }}
                        </span>
                    </div>
                    @empty
                    <p class="text-slate-400 text-xs italic">No hay historial registrado.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Cambio de Estado y Datos del Cliente (5 Columnas) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- Formulario de Cambio de Estado (Tarjeta Blanca) -->
            @if(auth()->user()->hasPermission('orders.manage'))
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>Actualizar Estado del Pedido</span>
                </h2>

                <form action="{{ route('admin.orders.updateStatus', $order) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="space-y-1">
                        <label for="new_status" class="text-xs font-bold text-slate-700">Nuevo Estado:</label>
                        <select
                            id="new_status"
                            name="status"
                            onchange="checkStatusWarning(this.value)"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all"
                        >
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pendiente (Reserva Activa)</option>
                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado (Pago OK)</option>
                            <option value="in_preparation" {{ $order->status === 'in_preparation' ? 'selected' : '' }}>En Preparación / Embalaje</option>
                            <option value="dispatched" {{ $order->status === 'dispatched' ? 'selected' : '' }}>Despachado (Salida de Stock)</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completado (Salida de Stock)</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado (Libera Stock)</option>
                        </select>
                    </div>

                    <!-- Avisos Dinámicos de Inventario -->
                    <div id="cancelWarning" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] space-y-1">
                        <strong>⚠️ Liberación de Stock:</strong> Al marcar como cancelado, las reservas de stock asociadas se anularán inmediatamente y las unidades volverán a estar disponibles en el catálogo.
                    </div>

                    <div id="dispatchWarning" class="hidden p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] space-y-1">
                        <strong>📦 Salida Definitiva:</strong> Al marcar como despachado o completado, se registrará una salida definitiva en el Kardex de inventario.
                    </div>

                    <div class="space-y-1">
                        <label for="status_notes" class="text-xs font-bold text-slate-700">Notas / Motivo del Cambio:</label>
                        <textarea
                            id="status_notes"
                            name="notes"
                            rows="2"
                            placeholder="Ej. Pago verificado por BCP operación #123456 o cliente desistió..."
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:bg-white transition-all"
                        ></textarea>
                    </div>

                    <button
                        type="submit"
                        class="w-full py-3 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs shadow-sm hover:shadow transition-all flex items-center justify-center gap-2"
                    >
                        <span>Guardar Cambio de Estado</span>
                    </button>
                </form>
            </div>
            @endif

            <!-- Datos del Cliente (Tarjeta Blanca) -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
                <h2 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Información del Cliente</span>
                </h2>

                <div class="text-xs space-y-2.5 text-slate-600">
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Nombre:</span>
                        <span class="text-slate-900 font-bold text-right">{{ $order->customer_name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Teléfono:</span>
                        <span class="font-mono text-slate-900 font-bold text-right">{{ $order->customer_phone }}</span>
                    </div>
                    @if($order->customer_email)
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Correo:</span>
                        <span class="text-slate-800 text-right">{{ $order->customer_email }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Ciudad:</span>
                        <span class="text-slate-800 text-right">{{ $order->customer_city }}</span>
                    </div>
                    @if($order->customer_address)
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Dirección:</span>
                        <span class="text-slate-800 text-right">{{ $order->customer_address }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-400 font-medium">Comprobante:</span>
                        <span class="text-slate-900 font-bold uppercase text-right">
                            {{ $order->invoice_type }} ({{ $order->document_type }}: {{ $order->document_number ?? 'S/N' }})
                        </span>
                    </div>
                    @if($order->notes)
                    <div class="pt-1">
                        <span class="text-slate-400 font-medium block mb-1">Notas del Cliente:</span>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-[11px]">
                            {{ $order->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Mensaje Oficial de WhatsApp (Tarjeta Blanca) -->
            <div class="card-surface rounded-3xl p-6 border border-slate-100 shadow-sm space-y-3">
                <span class="text-xs font-bold text-slate-700 uppercase tracking-wider block">
                    Mensaje WhatsApp Enviado / Generado:
                </span>
                <pre class="p-3.5 rounded-2xl bg-slate-50 font-mono text-[11px] text-slate-700 whitespace-pre-wrap leading-relaxed border border-slate-200">{{ $order->whatsapp_message }}</pre>
            </div>

        </div>

    </div>

</div>

<script>
function checkStatusWarning(status) {
    const cancelWarn = document.getElementById('cancelWarning');
    const dispatchWarn = document.getElementById('dispatchWarning');

    if (cancelWarn && dispatchWarn) {
        cancelWarn.classList.toggle('hidden', status !== 'cancelled');
        dispatchWarn.classList.toggle('hidden', status !== 'dispatched' && status !== 'completed');
    }
}
</script>
@endsection
