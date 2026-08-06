<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EsStaffOAdmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->esAdmin() && ! $request->user()?->esStaff()) {
            return response()->json(['message' => 'No tienes permisos para acceder a esta sección.'], 403);
        }

        return $next($request);
    }
}
