<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Aplica cabeceras de ciberseguridad estrictas siguiendo estándares OWASP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Protección contra Clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Protección contra MIME Sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Control de información en cabecera Referer
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Control de características y sensores del navegador
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Forzar HTTPS estricto si está en producción
        if (app()->environment('production') || $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Prevenir caching de respuestas con datos sensibles en autenticación
        if ($request->is('admin*') || $request->is('login*')) {
            $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        }

        return $response;
    }
}
