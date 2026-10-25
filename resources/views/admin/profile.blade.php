@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('content')
<div class="space-y-6">

    <!-- Encabezado de Perfil -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $user->name }}</h2>
                <p class="text-slate-500 text-xs font-mono mt-0.5">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-2">
                    @foreach($user->roles as $role)
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                        {{ $role->display_name }}
                    </span>
                    @endforeach
                    <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        Estado: {{ $user->status === 'active' ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
        </div>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2.5 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Cerrar Sesión Activa</span>
            </button>
        </form>
    </div>

    <!-- Lista de Permisos Efectivos en Servidor -->
    <div class="card-surface p-6 border border-slate-100 space-y-4">
        <div>
            <h3 class="text-lg font-extrabold text-slate-900">Permisos Efectivos Autorizados</h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Los siguientes permisos han sido evaluados y confirmados en el servidor para su cuenta:
            </p>
        </div>

        @if($user->isSuperAdmin())
        <div class="p-4 rounded-2xl bg-purple-50 border border-purple-200 text-purple-900 text-sm font-semibold flex items-center gap-3">
            <svg class="w-6 h-6 text-purple-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <div>
                <p class="font-bold">Privilegios Irrestrictos de Superadministrador</p>
                <p class="text-xs text-purple-700 mt-0.5">Posee acceso sin restricciones a todos los módulos, configuraciones e integraciones del sistema.</p>
            </div>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($user->roles->flatMap->permissions->unique('id') as $permission)
            <div class="p-3.5 rounded-2xl border border-slate-200 bg-slate-50/50 flex items-start gap-3">
                <div class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-800">{{ $permission->display_name }}</span>
                    <span class="block text-[10px] font-mono text-emerald-700 mt-0.5">{{ $permission->name }}</span>
                    <span class="inline-block px-1.5 py-0.2 mt-1 rounded text-[9px] font-semibold uppercase bg-slate-200 text-slate-600">{{ $permission->module }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-6 text-slate-400 text-xs">
                No tiene permisos específicos asignados.
            </div>
            @endforelse
        </div>
        @endif
    </div>

</div>
@endsection
