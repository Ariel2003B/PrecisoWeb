<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unidad;

class ApiNimbusAppController extends Controller
{
    /**
     * Buscar unidad por placa(número_habilitación)
     */
    public function getUnidadByPlaca(Request $request)
    {
        // Recibir el formato ABC1234(15/2515)
        $input = $request->input('placa');

        if (!$input) {
            return response()->json(['error' => 'Debe enviar el campo placa'], 400);
        }

        // Regex: captura placa alfanumérica + número_habilitacion entre paréntesis
        if (!preg_match('/^([A-Z0-9]+)\((.+)\)$/i', $input, $matches)) {
            return response()->json(['error' => 'Formato inválido, debe ser ABC1234(15/2515)'], 400);
        }

        $placa = $matches[1];                // ABC1234
        $numeroHabilitacion = $matches[2];   // 15/2515

        // Buscar la unidad
        $unidad = Unidad::where('placa', $placa)
            ->where('numero_habilitacion', $numeroHabilitacion)
            ->with('usuario.empresa') // eager load usuario → empresa
            ->first();

        if (!$unidad) {
            return response()->json(['error' => 'Unidad no encontrada'], 404);
        }

        $empresa = $unidad->usuario?->empresa;

        return response()->json([
            'placa' => $unidad->placa .'('.$unidad->numero_habilitacion.')  ',
            'idWialon' => $unidad->idWialon,
            'token' => $empresa?->TOKEN,
            'depot' => $empresa?->DEPOT,

        ]);
    }
}

