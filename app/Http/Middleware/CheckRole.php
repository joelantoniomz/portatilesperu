<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Si el usuario está inactivo, cerrar sesión y rechazar
        if ($user->status !== 'active') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Su cuenta se encuentra desactivada. Contacte al administrador.',
            ]);
        }

        // Si es superadministrador, tiene acceso irrestricto
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Comprobar si el usuario posee alguno de los roles permitidos
        if (!empty($roles) && !$user->hasRole($roles)) {
            abort(403, 'Acceso denegado: Su rol no cuenta con autorización para acceder a esta sección.');
        }

        return $next($request);
    }
}
