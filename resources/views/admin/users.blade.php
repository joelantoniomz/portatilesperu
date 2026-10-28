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

        <div class="px-4 py-2.5 rounded-2xl bg-[#0c3823] text-white text-xs font-semibold flex items-center gap-2 shadow-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Total: {{ $users->total() }} Usuarios Registrados</span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('admin.users.store') }}" class="card-surface space-y-4 border border-slate-100 p-6">
            @csrf
            <div><h3 class="font-black text-slate-900">Crear usuario</h3><p class="text-xs text-slate-500">Asigne un rol y una contraseña inicial segura.</p></div>
            <div class="grid gap-3 sm:grid-cols-2">
                <input name="name" value="{{ old('name') }}" required placeholder="Nombre completo" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                <input name="email" value="{{ old('email') }}" required type="email" placeholder="Correo electrónico" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                <input name="phone" value="{{ old('phone') }}" placeholder="Teléfono (opcional)" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                <select name="role_id" required class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm"><option value="">Seleccione rol</option>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->display_name }}</option>@endforeach</select>
                <input name="password" required type="password" placeholder="Contraseña inicial" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                <input name="password_confirmation" required type="password" placeholder="Confirmar contraseña" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
            </div>
            <button class="rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white hover:bg-emerald-500">Crear usuario</button>
        </form>

        <div class="card-surface border border-slate-100 p-6"><h3 class="font-black text-slate-900">Restablecer contraseña</h3><p class="mb-4 text-xs text-slate-500">Seleccione un usuario de la tabla y actualice su acceso.</p><p class="text-xs text-slate-400">Use el botón “Restablecer” en cada fila.</p></div>
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
                        <th class="px-4 py-3">Fecha Registro</th><th class="px-4 py-3 rounded-r-xl">Acciones</th>
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
                        <td class="px-4 py-3.5"><div class="flex gap-1"><details class="relative"><summary class="cursor-pointer rounded-lg border border-slate-200 px-2 py-1.5 text-[10px] font-bold text-slate-700">Editar</summary><form method="POST" action="{{ route('admin.users.update', $u) }}" class="absolute right-0 z-20 mt-2 w-72 space-y-2 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">@csrf @method('PUT')<input required name="name" value="{{ $u->name }}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs"><input required name="email" type="email" value="{{ $u->email }}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs"><input name="phone" value="{{ $u->phone }}" placeholder="Teléfono" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs"><select name="role_id" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs">@foreach($roles as $role)<option value="{{ $role->id }}" @selected($u->roles->contains($role->id))>{{ $role->display_name }}</option>@endforeach</select><select name="status" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs"><option value="active" @selected($u->status === 'active')>Activo</option><option value="inactive" @selected($u->status !== 'active')>Inactivo</option></select><button class="w-full rounded-lg bg-emerald-600 px-2 py-1.5 text-xs font-bold text-white">Guardar cambios</button></form></details><details class="relative"><summary class="cursor-pointer rounded-lg border border-slate-200 px-2 py-1.5 text-[10px] font-bold text-slate-700">Clave</summary><form method="POST" action="{{ route('admin.users.password', $u) }}" class="absolute right-0 z-10 mt-2 w-64 space-y-2 rounded-xl border border-slate-200 bg-white p-3 shadow-xl">@csrf @method('PUT')<input required name="password" type="password" placeholder="Nueva contraseña" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs"><input required name="password_confirmation" type="password" placeholder="Confirmar contraseña" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-xs"><button class="w-full rounded-lg bg-slate-900 px-2 py-1.5 text-xs font-bold text-white">Guardar contraseña</button></form></details><form method="POST" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm('¿Eliminar este usuario?')">@csrf @method('DELETE')<button class="rounded-lg border border-rose-200 px-2 py-1.5 text-[10px] font-bold text-rose-700">Eliminar</button></form></div></td>
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
