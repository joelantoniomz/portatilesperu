@extends('layouts.admin')

@section('title', 'Registrar Salida de Stock')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Encabezado con Volver -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.inventory.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Registrar Salida de Stock</h2>
                <p class="text-slate-500 text-xs">Descuenta stock físico y disponible. Valida stock suficiente en tiempo real (SELECT FOR UPDATE).</p>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
        <p class="font-bold mb-1">Por favor revise los errores en el formulario:</p>
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card-surface p-6 sm:p-8 border border-slate-100 shadow-sm">
        <form method="POST" action="{{ route('admin.inventory.exit.store') }}" class="space-y-5">
            @csrf

            <!-- Producto -->
            <div>
                <label for="product_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Producto / Laptop *
                </label>
                <input id="productSearch" type="search" placeholder="Buscar por nombre o SKU..." class="mb-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" oninput="filterInventoryProducts(this, 'product_id')">
                <select name="product_id" id="product_id" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">Seleccione un producto...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id', request('product_id')) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} (SKU: {{ $p->sku }})
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Almacén y Tipo de Salida -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="warehouse_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Almacén de Origen *
                    </label>
                    <select name="warehouse_id" id="warehouse_id" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="">Seleccione almacén...</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('warehouse_id', request('warehouse_id')) == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} ({{ $wh->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tipo de Operación *
                    </label>
                    <select name="type" id="type" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <option value="exit" {{ old('type') == 'exit' ? 'selected' : '' }}>Venta Directa / Despacho Manual</option>
                        <option value="transfer_out" {{ old('type') == 'transfer_out' ? 'selected' : '' }}>Traslado saliente a otro almacén</option>
                    </select>
                </div>
            </div>

            <!-- Cantidad y Costo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Cantidad a Egresar (Unidades) *
                    </label>
                    <input type="number" name="quantity" id="quantity" min="1" step="1" value="{{ old('quantity', 1) }}" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label for="unit_cost" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Costo de Salida (Opcional)
                    </label>
                    <input type="number" name="unit_cost" id="unit_cost" min="0" step="0.01" value="{{ old('unit_cost') }}" placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>
            </div>

            <!-- Documento y Referencia -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="document_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        N° Boleta / Factura / Guía de Remisión
                    </label>
                    <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}" placeholder="Ej: B001-0001234" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>

                <div>
                    <label for="reference" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Referencia del Pedido / Destinatario
                    </label>
                    <input type="text" name="reference" id="reference" value="{{ old('reference') }}" placeholder="Ej: PED-2026-0042 / Juan Pérez" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500">
                </div>
            </div>

            <!-- Motivo u Observaciones -->
            <div>
                <label for="reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Motivo / Justificación de la Salida
                </label>
                <textarea name="reason" id="reason" rows="2" placeholder="Motivo de la entrega, garantía, merma o traslado..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500">{{ old('reason') }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.inventory.index') }}" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/20 transition-all">
                    Registrar Salida de Stock
                </button>
            </div>
        </form>
    </div>

</div>
<script>function filterInventoryProducts(input, selectId) { const term = input.value.toLowerCase(); document.querySelectorAll('#' + selectId + ' option').forEach(option => { option.hidden = option.value && !option.text.toLowerCase().includes(term); }); }</script>
@endsection
