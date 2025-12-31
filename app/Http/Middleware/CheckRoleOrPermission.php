<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roleOrPermission): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        // Verificar si es un rol o un permiso
        if (str_contains($roleOrPermission, '|')) {
            // Múltiples roles separados por |
            $roles = explode('|', $roleOrPermission);
            if ($user->hasAnyRole($roles)) {
                return $next($request);
            }
        } elseif ($user->hasRole($roleOrPermission)) {
            // Es un rol
            return $next($request);
        } elseif ($user->hasPermissionTo($roleOrPermission)) {
            // Es un permiso
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
