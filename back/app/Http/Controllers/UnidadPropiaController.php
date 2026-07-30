<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidad;

/**
 * Endpoint nuevo y aditivo para la App Flota de PrecisoBus (Fase 9).
 * No modifica UnidadController ni ningún otro controlador existente.
 *
 * Misma regla de filtrado que HojaTrabajoController@index (líneas 118-138):
 * Administrador ve toda la flota de su empresa, cualquier otro perfil ve
 * solo las unidades donde es el usuario dueño (usu_id).
 */
class UnidadPropiaController extends Controller
{
    public function mias(Request $request)
    {
        $user = $request->user();

        $esAdmin = in_array(optional($user->p_e_r_f_i_l)->DESCRIPCION, ['admin', 'Administrador'], true);

        $unidades = $esAdmin
            ? Unidad::whereHas('usuario', function ($q) use ($user) {
                $q->where('EMP_ID', $user->EMP_ID);
            })->get()
            : Unidad::where('usu_id', $user->USU_ID)->get();

        return response()->json([
            'usuario' => [
                'usuId' => $user->USU_ID,
                'empId' => $user->EMP_ID,
                'perfil' => optional($user->p_e_r_f_i_l)->DESCRIPCION,
                'nombre' => $user->NOMBRE,
            ],
            'unidades' => $unidades->map(function ($u) {
                return [
                    'placa' => $u->placa,
                    'idWialon' => $u->idWialon,
                    'numeroHabilitacion' => $u->numero_habilitacion,
                    'capacidadPasajeros' => $u->capacidad_pasajeros,
                ];
            }),
        ]);
    }
}
