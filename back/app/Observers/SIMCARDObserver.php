<?php

namespace App\Observers;

use App\Models\SIMCARD;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SIMCARDObserver
{
    /**
     * Se ejecuta cuando se crea un registro en SIMCARD
     */
    public function created(SIMCARD $simcard)
    {
        DB::table('SIMCARD_HISTORY')->insert([
            'ID_SIM' => $simcard->ID_SIM,
            'RUC' => $simcard->RUC,
            'PROPIETARIO' => $simcard->PROPIETARIO,
            'CUENTA' => $simcard->CUENTA,
            'NUMEROTELEFONO' => $simcard->NUMEROTELEFONO,
            'TIPOPLAN' => $simcard->TIPOPLAN,
            'PLAN' => $simcard->PLAN,
            'ICC' => $simcard->ICC,
            'ESTADO' => $simcard->ESTADO,
            'GRUPO' => $simcard->GRUPO,
            'ASIGNACION' => $simcard->ASIGNACION,
            'EQUIPO' => $simcard->EQUIPO,
            'VEH_ID' => $simcard->VEH_ID,
            'IMEI' => $simcard->IMEI,
            'ACCION' => 'INSERT',
            'USUARIO' => Auth::check() ? Auth::user()->NOMBRE.' '.Auth::user()->APELLIDO : 'SYSTEM',
            'FECHA' => now()
        ]);
    }

    /**
     * Se ejecuta cuando se actualiza un registro en SIMCARD
     */
    public function updated(SIMCARD $simcard)
    {
        try {
            $oldData = $simcard->getOriginal(); // Obtener valores anteriores (OLD)
            $newData = $simcard->getAttributes(); // Obtener valores nuevos (NEW)

            DB::table('SIMCARD_HISTORY')->insert([
                'ID_SIM' => $simcard->ID_SIM,
                'RUC' => json_encode([
                    'OLD' => $oldData['RUC'],
                    'NEW' => $newData['RUC']
                ]),
                'PROPIETARIO' => json_encode([
                    'OLD' => $oldData['PROPIETARIO'],
                    'NEW' => $newData['PROPIETARIO']
                ]),
                'CUENTA' => json_encode([
                    'OLD' => $oldData['CUENTA'],
                    'NEW' => $newData['CUENTA']
                ]),
                'NUMEROTELEFONO' => json_encode([
                    'OLD' => $oldData['NUMEROTELEFONO'],
                    'NEW' => $newData['NUMEROTELEFONO']
                ]),
                'TIPOPLAN' => json_encode([
                    'OLD' => $oldData['TIPOPLAN'],
                    'NEW' => $newData['TIPOPLAN']
                ]),
                'PLAN' => json_encode([
                    'OLD' => $oldData['PLAN'],
                    'NEW' => $newData['PLAN']
                ]),
                'ICC' => json_encode([
                    'OLD' => $oldData['ICC'],
                    'NEW' => $newData['ICC']
                ]),
                'ESTADO' => json_encode([
                    'OLD' => $oldData['ESTADO'],
                    'NEW' => $newData['ESTADO']
                ]),
                'GRUPO' => json_encode([
                    'OLD' => $oldData['GRUPO'],
                    'NEW' => $newData['GRUPO']
                ]),
                'ASIGNACION' => json_encode([
                    'OLD' => $oldData['ASIGNACION'],
                    'NEW' => $newData['ASIGNACION']
                ]),
                'EQUIPO' => json_encode([
                    'OLD' => $oldData['EQUIPO'],
                    'NEW' => $newData['EQUIPO']
                ]),
                'IMEI' => json_encode([
                    'OLD' => $oldData['IMEI'],
                    'NEW' => $newData['IMEI']
                ]),
                'ACCION' => 'UPDATE',
                'USUARIO' => Auth::check() ? Auth::user()->NOMBRE.' '.Auth::user()->APELLIDO : 'SYSTEM',
                'FECHA' => now()
            ]);
        } catch (\Throwable $th) {
            // Manejar errores si algo falla
            \Log::error('Error al guardar el historial de actualización en SIMCARD_HISTORY: ' . $th->getMessage());
        }
    }

    /**
     * Se ejecuta cuando se elimina un registro en SIMCARD
     */
    public function deleted(SIMCARD $simcard)
    {
        DB::table('SIMCARD_HISTORY')->insert([
            'ID_SIM' => $simcard->ID_SIM,
            'RUC' => $simcard->RUC,
            'PROPIETARIO' => $simcard->PROPIETARIO,
            'CUENTA' => $simcard->CUENTA,
            'NUMEROTELEFONO' => $simcard->NUMEROTELEFONO,
            'TIPOPLAN' => $simcard->TIPOPLAN,
            'PLAN' => $simcard->PLAN,
            'ICC' => $simcard->ICC,
            'ESTADO' => $simcard->ESTADO,
            'GRUPO' => $simcard->GRUPO,
            'ASIGNACION' => $simcard->ASIGNACION,
            'EQUIPO' => $simcard->EQUIPO,
            'VEH_ID' => $simcard->VEH_ID,
            'IMEI' => $simcard->IMEI,
            'ACCION' => 'DELETE',
            'USUARIO' => Auth::check() ? Auth::user()->NOMBRE.' '.Auth::user()->APELLIDO : 'SYSTEM',
            'FECHA' => now()
        ]);
    }
}
