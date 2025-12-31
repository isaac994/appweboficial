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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $user = $request->user();
        
        // Recargar el usuario desde la BD si no es el admin por defecto (id = 0)
        if ($user->id && $user->id != 0) {
            try {
                $freshUser = \App\Models\User::find($user->id);
                if ($freshUser) {
                    $user = $freshUser;
                }
            } catch (\Exception $e) {
                // Si hay error, continuar con el usuario actual
            }
        }
        
        // Si el usuario tiene rol de Propietario, tiene todos los permisos
        // Verificar de múltiples formas para asegurar que funcione
        $userRol = $user->rol ?? $user->attributes['rol'] ?? null;
        if ($userRol === 'Propietario' || $user->hasRole('Propietario')) {
            return $next($request);
        }
        
        // Verificar el permiso específico para otros roles
        if (!$user->hasPermissionTo($permission)) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
