<?php

namespace App\Http\Controllers;

use App\Models\Visita;
use Illuminate\Http\Request;

class VisitasController extends Controller
{
    public function incrementarVisitas(Request $request)
    {
        try {
            if (!$request->cookie('visitado')) {
                $visita = Visita::first();
                if (!$visita) {
                    $visita = Visita::create(['contador' => 1]);
                } else {
                    $visita->increment('contador');
                }

                return response()->json(['mensaje' => 'Visita registrada', 'contador' => $visita->contador])
                    ->cookie('visitado', true, 1440);
            }

            return response()->json(['mensaje' => 'Ya visitaste esta página recientemente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function obtenerVisitas()
    {
        try {
            $contador = Visita::first()->contador ?? 0;
            return response()->json(['contador' => $contador]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
        
    }
}
