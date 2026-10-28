@extends('layouts.admin')

@section('title', 'Editar Almacén: ' . $warehouse->name)

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Editar Almacén</h2>
            <p class="text-slate-500 text-xs">Actualice los datos de contacto y estado operativo del almacén {{ $warehouse->code }}.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
        <p class="font-bold mb-1">Por favor revise los errores:</p>
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="card-surface p-6 sm:p-8 border border-slate-100 shadow-sm">
        <form method="POST" action="{{ route('admin.warehouses.update', $warehouse) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nombre del Almacén *
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $warehouse->name) }}" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="code" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Código Único (Acrónimo) *
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code', $warehouse->code) }}" required class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm font-mono font-bold text-slate-800 uppercase focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="city" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Ciudad / Distrito
                    </label>
                    <input type="text" name="city" id="city" value="{{ old('city', $warehouse->city) }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Teléfono de Contacto
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $warehouse->phone) }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Dirección Completa
                </label>
                <input type="text" name="address" id="address" value="{{ old('address', $warehouse->address) }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="responsible_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Responsable / Encargado de Sede
                </label>
                <input type="text" name="responsible_name" id="responsible_name" value="{{ old('responsible_name', $warehouse->responsible_name) }}" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label for="notes" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Notas / Instrucciones de Acceso
                </label>
                <textarea name="notes" id="notes" rows="2" class="w-full px-3.5 py-2.5 rounded-2xl border border-slate-200 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('notes', $warehouse->notes) }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $warehouse->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                <label for="is_active" class="text-xs font-bold text-slate-700 cursor-pointer">
                    Almacén Activo (Habilitado para recibir y despachar mercadería)
                </label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                <button type="button" onclick="if(confirm('¿Seguro que desea eliminar este almacén?')) document.getElementById('deleteWarehouseForm').submit();" class="px-4 py-2.5 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 transition-all">
                    Eliminar Almacén
                </button>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="px-5 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 transition-all">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>

        <form id="deleteWarehouseForm" action="{{ route('admin.warehouses.destroy', $warehouse) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

</div>
@endsection
