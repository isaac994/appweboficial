<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleFileUploads
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si es una petición PUT/PATCH con archivos, convertir a POST
        if (in_array($request->method(), ['PUT', 'PATCH']) && $request->hasFile('imagen')) {
            $request->setMethod('POST');
            $request->merge(['_method' => 'PUT']);
        }

        return $next($request);
    }
}
