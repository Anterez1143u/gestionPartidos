<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'No autenticado.');
        }

        // obtener rol del usuario (soporta varias implementaciones)
        $userRole = null;

        if (isset($user->rol)) {
            $userRole = $user->rol;
        } elseif (isset($user->role) && is_string($user->role)) {
            $userRole = $user->role;
        } elseif (isset($user->role) && is_object($user->role) && isset($user->role->name)) {
            $userRole = $user->role->name;
        }

        // si el modelo de usuario tiene métodos de roles (p. ej. hasRole)
        foreach ($roles as $r) {
            if (!$userRole && method_exists($user, 'hasRole')) {
                if ($user->hasRole($r)) {
                    return $next($request);
                }
            } elseif ($userRole && strcasecmp($userRole, $r) === 0) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}