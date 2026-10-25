@extends('layouts.admin')

@section('title', 'Bitácora de Auditoría')

@section('content')
<div class="space-y-6">

    <!-- Encabezado de Sección -->
    <div class="card-surface p-6 sm:p-8 border border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold mb-2">
                <span>Ruta Protegida por Permiso: audit.view</span>
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Bitácora de Auditoría y Seguridad</h2>
            <p class="text-slate-500 text-sm mt-1">
                Registro inmutable de acciones sensibles, autenticación, cambios y trazabilidad del sistema.
            </p>
        </div>

        <!-- Filtro Rápido por Acción -->
        <form method="GET" action="{{ route('admin.audit') }}" class="flex items-center gap-2">
            <select name="action" onchange="this.form.submit()" class="px-3.5 py-2 rounded-2xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">Todas las acciones</option>
                <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Inicios de sesión (login)</option>
                <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Cierres de sesión (logout)</option>
                <option value="failed_login" {{ request('action') === 'failed_login' ? 'selected' : '' }}>Intentos fallidos (failed_login)</option>
            </select>
            @if(request('action'))
            <a href="{{ route('admin.audit') }}" class="p-2 text-xs text-rose-500 font-bold hover:underline">Limpiar</a>
            @endif
        </form>
    </div>

    <!-- Tabla de Auditoría -->
    <div class="card-surface p-6 border border-slate-100 space-y-4">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 uppercase text-[10px] font-extrabold text-slate-400">
                    <tr>
                        <th class="px-4 py-3 rounded-l-xl">ID</th>
                        <th class="px-4 py-3">Acción</th>
                        <th class="px-4 py-3">Usuario Responsable</th>
                        <th class="px-4 py-3">Entidad Afectada</th>
                        <th class="px-4 py-3">IP Origen</th>
                        <th class="px-4 py-3">Datos Registrados (Payload)</th>
                        <th class="px-4 py-3 rounded-r-xl">Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3.5 font-mono text-slate-400">
                            #{{ $log->id }}
                        </td>
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
                            {{ $log->user?->name ?? 'Sistema / Externo' }}
                            @if($log->user)
                                <span class="block text-[10px] font-normal text-slate-400">{{ $log->user->email }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 font-mono text-[11px] text-slate-600">
                            {{ $log->entity_type ? $log->entity_type . ' #' . $log->entity_id : '-' }}
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 font-mono text-[11px]">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="px-4 py-3.5 font-mono text-[10px] text-slate-500 max-w-sm">
                            @if($log->new_values)
                                <code class="bg-slate-100 px-2 py-1 rounded block truncate text-slate-700">{{ json_encode($log->new_values) }}</code>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-slate-500 whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400">
                            No se encontraron registros de auditoría con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pt-4">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
