<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckFirstLogin
{
    /**
     * Maneja la petición. Si el usuario no ha actualizado su perfil (primer ingreso),
     * lo manda a la vista de cambio de contraseña.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si el usuario está logueado Y su fecha de creación es igual a la de actualización
        if ($user && $user->created_at->eq($user->updated_at)) {
            // Solo redirigir si no está ya en la página de primer ingreso para evitar bucles infinitos
            if (!$request->is('primer-ingreso*')) {
                return redirect()->route('primer.ingreso');
            }
        }

        return $next($request);
    }
}