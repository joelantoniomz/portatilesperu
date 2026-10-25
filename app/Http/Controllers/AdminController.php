<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Pantalla de inicio del panel administrativo (Fase 1: Layout base, roles y sesión activa).
     */
    public function index(): View
    {
        $user = auth()->user()->load('roles.permissions');
        $recentAudit = AuditLog::with('user')
            ->latest('id')
            ->limit(8)
            ->get();

        $stats = [
            'total_users' => User::count(),
            'active_roles' => Role::count(),
            'recent_logs' => AuditLog::count(),
            'current_role' => $user->roles->first()?->display_name ?? 'Sin rol asignado',
        ];

        return view('admin.dashboard', compact('user', 'recentAudit', 'stats'));
    }

    /**
     * Muestra la lista de auditoría del sistema (Protegido por permiso audit.view).
     */
    public function auditLogs(Request $request): View
    {
        $query = AuditLog::with('user')->latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->paginate(15);

        return view('admin.audit', compact('logs'));
    }

    /**
     * Muestra la lista de usuarios y roles del sistema (Protegido por roles superadmin y admin).
     */
    public function users(): View
    {
        $users = User::with('roles')->latest('id')->paginate(10);
        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    /**
     * Vista de perfil del usuario autenticado.
     */
    public function profile(): View
    {
        $user = auth()->user()->load('roles.permissions');
        return view('admin.profile', compact('user'));
    }
}
