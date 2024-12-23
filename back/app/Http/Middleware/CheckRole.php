<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next, ...$roles)
    {
        if (Auth::check() && in_array(Auth::user()->p_e_r_f_i_l->DESCRIPCION, $roles)) {
            return $next($request);
        }

        abort(403, 'No tienes permiso para acceder a esta página.');
    }
}
