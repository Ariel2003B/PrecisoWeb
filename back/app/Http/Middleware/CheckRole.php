<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle($request, Closure $next, ...$permission)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }
        $user = Auth::user();
        $permissions = $user->permisos->pluck('DESCRIPCION')->toArray();
        $confirmAcces = false;
        $valorBuscar = null;
        foreach ($permission as $valor) {
            $valorBuscar=$valor;
        }

        foreach ($permissions as $permiso) {
            if ($permiso !== $valorBuscar) {
                $confirmAcces = false;
            } else {
                $confirmAcces = true;
                break;
            }
        }
        if (!$confirmAcces){
            abort(403, 'No tienes permiso para acceder a esta página.');
        }
        return $next($request);
    }
}
