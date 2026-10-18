<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForzarCambioPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->cambiar_password
            && ! $request->routeIs('password.forzar', 'password.forzar.update', 'logout')) {
            return redirect()->route('password.forzar');
        }

        return $next($request);
    }
}
