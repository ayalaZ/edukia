<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolPermitido
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next,string ...$roles): Response
    {
        abort_unless(in_array($request->user()?->rol, $roles, true), 403, 'No tienes permiso para entrar aquí.');
        return $next($request);
    }
}
