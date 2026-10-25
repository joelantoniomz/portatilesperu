@extends('layouts.admin')

@section('title', 'Gestión de Usuarios y Roles')

@section('content')
<div class="space-y-6">

    <!-- Encabezado de Sección -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Ruta Protegida por Rol: superadmin & admin</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Usuarios y Asignación de Roles</h2>
            <p class="text-slate-500 text-sm mt-1">
                Visualización y control de accesos autorizados del sistema. Las credenciales y permisos se procesan en el servidor.
            </p>
        </div>

        <div class="px-4 py-2.5 rounded-2xl bg-forest-850 text-white text-xs font-semibold flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Total: {{ $users->total() }} Usuarios Registrados</span>
        </div>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card-surface p-6 border border-slate-100 space-y-4">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-[10px] font-extrabold text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Usuario / Nombre</th>
                        <th class="px-4 py-3">Correo Electrónico</th>
                        <th class="px-4 py-3">Teléfono</th>
                        <th class="px-4 py-3">Rol del Sistema</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 rounded-r-xl">Fecha Registro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-500 text-white font-black flex items-center justify-center text-xs shadow-sm">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-900">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 font-mono text-[11px] text-slate-600">
                            {{ $u->email }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-500">
                            {{ $u->phone ?? 'Sin teléfono' }}
                        </td>
                        <td class="px-4 py-3.5">
                            @foreach($u->roles as $role)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold 
                                @if($role->name === 'superadmin') bg-purple-100 text-purple-800 border border-purple-200
                                @elseif($role->name === 'admin') bg-blue-100 text-blue-800 border border-blue-200
                                @elseif($role->name === 'vendedor') bg-amber-100 text-amber-800 border border-amber-200
                                @elseif($role->name === 'almacen') bg-emerald-100 text-emerald-800 border border-emerald-200
                                @elseif($role->name === 'editor_catalogo') bg-teal-100 text-teal-800 border border-teal-200
                                @else bg-slate-100 text-slate-800 border border-slate-200 @endif">
                                {{ $role->display_name }}
                            </span>
                            @endforeach
                        </td>
                        <td class="px-4 py-3.5">
                            @if($u->status === 'active')
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    <span>Activo</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    <span>Inactivo</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-400">
                            {{ $u->created_at->format('d/m/Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
