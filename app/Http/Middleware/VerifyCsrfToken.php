<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/reportes/compras/pdf',
        '/reportes/ventas/pdf',
        'users/*/reset-password',
        '/users/*/reset-password',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        // Si es una ruta de restablecimiento de contraseña, saltar completamente la verificación CSRF
        if ($request->is('users/*/reset-password')) {
            \Log::info('CSRF: Saltando verificación CSRF para restablecimiento de contraseña', [
                'url' => $request->url(),
                'path' => $request->path()
            ]);
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
