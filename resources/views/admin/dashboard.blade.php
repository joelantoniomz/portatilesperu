@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    <!-- Tarjeta Superior: Bienvenida y Estado de la Fase 1 -->
    <div class="card-surface p-6 sm:p-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 border border-slate-100">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                <span>Fase 1 Completada: Base Técnica, Autenticación y Permisos</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Hola, {{ $user->name }}
            </h2>
            <p class="text-slate-500 text-sm max-w-2xl leading-relaxed">
                Sesión iniciada correctamente con verificación de permisos en servidor. Su rol actual es 
                <strong class="text-emerald-700 font-bold">{{ $stats['current_role'] }}</strong>.
                Las rutas y acciones administrativas están protegidas mediante políticas y middlewares en el backend.
            </p>
        </div>

        <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
            <div class="w-12 h-12 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-emerald-500/20">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-semibold uppercase">Permisos Asignados</p>
                <p class="text-xl font-extrabold text-slate-900">{{ $user->isSuperAdmin() ? 'Acceso Total (Root)' : $user->roles->first()?->permissions->count() . ' permisos' }}</p>
            </div>
        </div>
    </div>

    <!-- Indicadores Vivos (Datos reales de la Base de Datos) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Tarjeta 1: Total Usuarios -->
        <div class="card-surface p-5 border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Usuarios Registrados</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_users'] }}</p>
                <span class="text-[11px] font-semibold text-emerald-600 flex items-center gap-1 mt-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>6 roles operativos</span>
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <!-- Tarjeta 2: Roles Activos -->
        <div class="card-surface p-5 border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Roles del Sistema</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['active_roles'] }}</p>
                <span class="text-[11px] font-semibold text-emerald-600 mt-1 block">Superadmin, Admin, Vendedor, Almacén, Editor, Auditor</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                </svg>
            </div>
        </div>

        <!-- Tarjeta 3: Eventos Auditoría -->
        <div class="card-surface p-5 border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Eventos de Auditoría</span>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['recent_logs'] }}</p>
                <span class="text-[11px] font-semibold text-emerald-600 mt-1 block">Trazabilidad de inicio de sesión activa</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Tarjeta 4: Estado del Servidor -->
        <div class="card-surface p-5 border border-slate-100 flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Motor & Seguridad</span>
                <p class="text-lg font-black text-slate-900 mt-1">PHP 8.3 & MySQL 8.4</p>
                <span class="text-[11px] font-semibold text-emerald-600 mt-1 block">Políticas activas en backend</span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Panel de Pruebas de Autorización en Servidor (Sandbox Interactivo) -->
    <div class="card-surface p-6 border border-slate-100 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-extrabold text-slate-900">Verificación de Autorización en Servidor (Fase 1)</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Haga clic en cualquiera de las siguientes rutas protegidas para comprobar cómo el servidor valida su rol y rechaza con 403 Forbidden a roles no autorizados:
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">Validado en backend</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <a href="{{ route('admin.test.almacen') }}" target="_blank" class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all group">
                <span class="block text-xs font-bold text-slate-700 group-hover:text-emerald-700">Ruta: /admin/almacen-test</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Permitido solo para <strong>Almacén</strong> o <strong>Superadmin</strong>.</span>
            </a>

            <a href="{{ route('admin.test.vendedor') }}" target="_blank" class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all group">
                <span class="block text-xs font-bold text-slate-700 group-hover:text-emerald-700">Ruta: /admin/vendedor-test</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Permitido solo para <strong>Vendedor</strong> o <strong>Superadmin</strong>.</span>
            </a>

            <a href="{{ route('admin.users') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all group">
                <span class="block text-xs font-bold text-slate-700 group-hover:text-emerald-700">Ruta: /admin/usuarios</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Permitido solo para <strong>Superadmin</strong> o <strong>Admin</strong>.</span>
            </a>

            <a href="{{ route('admin.audit') }}" class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-500 hover:bg-emerald-50/50 transition-all group">
                <span class="block text-xs font-bold text-slate-700 group-hover:text-emerald-700">Ruta: /admin/auditoria</span>
                <span class="text-[11px] text-slate-500 mt-1 block">Requiere permiso granular: <strong>audit.view</strong>.</span>
            </a>
        </div>
    </div>

    <!-- Bitácora Reciente de Auditoría -->
    <div class="card-surface p-6 border border-slate-100 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold text-slate-900">Actividad Reciente en el Sistema (Auditoría Viva)</h3>
            <a href="{{ route('admin.audit') }}" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 hover:underline">Ver registro completo &rarr;</a>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-[10px] font-extrabold text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">Evento / Acción</th>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">IP Origen</th>
                        <th class="px-4 py-3">Detalle</th>
                        <th class="px-4 py-3 rounded-r-xl">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAudit as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold 
                                @if($log->action === 'login') bg-emerald-100 text-emerald-800
                                @elseif($log->action === 'logout') bg-slate-100 text-slate-700
                                @elseif(str_starts_with($log->action, 'failed')) bg-rose-100 text-rose-800
                                @else bg-teal-100 text-teal-800 @endif">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 font-bold text-slate-800">
                            {{ $log->user?->name ?? 'Invitado / Sistema' }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px]">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 max-w-xs truncate">
                            @if($log->new_values)
                                {{ json_encode($log->new_values) }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-400">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                            No se registran eventos de auditoría aún.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
