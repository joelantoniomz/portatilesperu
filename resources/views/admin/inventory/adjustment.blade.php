@extends('layouts.admin')

@section('title', 'Ajuste de Inventario Físico')

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
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Ajuste de Inventario Físico</h2>
                <p class="text-slate-500 text-xs">Ajusta el saldo real tras conteo cíclico. Registra un movimiento inmutable compensatorio en Kardex.</p>
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
        <div class="mb-5 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs leading-relaxed">
            <span class="font-bold">⚠️ Importante:</span> Ingrese la <strong>cantidad física real contada</strong> en el almacén. El sistema calculará automáticamente la diferencia con respecto al stock registrado y creará el registro de auditoría correspondiente.
        </div>

        <form method="POST" action="{{ route('admin.inventory.adjustment.store') }}" class="space-y-5">
            @csrf

            <!-- Producto -->
            <div>
                <label for="product_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Producto / Laptop a Ajustar *
                </label>
                <select name="product_id" id="product_id" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <option value="">Seleccione un producto...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id', request('product_id')) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} (SKU: {{ $p->sku }})
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Almacén y Cantidad Real -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="warehouse_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Almacén Físico *
                    </label>
                    <select name="warehouse_id" id="warehouse_id" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="">Seleccione almacén...</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('warehouse_id', request('warehouse_id')) == $wh->id ? 'selected' : '' }}>
                            {{ $wh->name }} ({{ $wh->code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="new_quantity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Cantidad Física Real Contada *
                    </label>
                    <input type="number" name="new_quantity" id="new_quantity" min="0" step="1" value="{{ old('new_quantity', 0) }}" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <span class="text-[11px] text-slate-400 mt-1 block">El stock físico pasará exactamente a este número.</span>
                </div>
            </div>

            <!-- Documento de Conteo -->
            <div>
                <label for="document_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    N° Acta de Conteo / Inventario Físico (Opcional)
                </label>
                <input type="text" name="document_number" id="document_number" value="{{ old('document_number') }}" placeholder="Ej: ACTA-INV-2026-10" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
            </div>

            <!-- Motivo OBLIGATORIO -->
            <div>
                <label for="reason" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Motivo / Justificación Obligatoria del Ajuste *
                </label>
                <textarea name="reason" id="reason" rows="3" required placeholder="Explique la causa de la discrepancia: rotura, diferencia en conteo trimestral, regularización de lote..." class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500">{{ old('reason') }}</textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.inventory.index') }}" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs shadow-lg shadow-amber-600/20 transition-all">
                    Registrar Ajuste en Kardex
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
