<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->status !== 'active') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Su cuenta se encuentra desactivada.',
            ]);
        }

        if (! $user->hasPermission($permission)) {
            abort(403, 'Acceso denegado: No posee el permiso requerido para ejecutar esta acción ('.$permission.').');
        }

        return $next($request);
    }
}
