<?php

namespace App\Http\Controllers;

use App\Models\HojaTrabajo;
use App\Models\Unidad;
use App\Models\USUARIO;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class UnidadController extends Controller
{
    public function index()
    {
        $unidades = Unidad::with('usuario.empresa')->get();
        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        $usuarios = USUARIO::where('ESTADO', 'A')->get(); // Opcional si quieres listar solo activos
        return view('unidades.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_habilitacion' => 'required|string|max:255',
            'placa' => 'required|string|max:255',
            'propietario' => 'nullable|string|max:255',
            'anio_fabricacion' => 'nullable|integer',
            'chasis' => 'nullable|string|max:255',
            'carroceria' => 'nullable|string|max:255',
            'tipo_especial' => 'nullable|string|max:255',
            'capacidad_pasajeros' => 'nullable|integer',
            'puertas_ingreso' => 'nullable|integer',
            'puertas_izquierdas' => 'nullable|integer',
            'usu_id' => 'nullable|exists:USUARIO,USU_ID'
        ]);


        Unidad::create($request->all());

        return redirect()->route('unidades.index')->with('success', 'Unidad creada exitosamente.');
    }

    public function edit($id)
    {
        $unidad = Unidad::findOrFail($id);
        $usuarios = USUARIO::where('ESTADO', 'A')->get();
        return view('unidades.edit', compact('unidad', 'usuarios'));
    }

    public function placaPorId($id)
    {
        $unidad = Unidad::find($id);

        if (!$unidad) {
            return response()->json(['message' => 'Unidad no encontrada'], 404);
        }

        return response()->json([
            'id_unidad' => $unidad->id_unidad,
            'placa'     => $unidad->placa,
        ]);
    }

    public function resumenVueltas($id)
    {
        $unidad = Unidad::find($id);
        if (!$unidad) {
            return response()->json(['message' => 'Unidad no encontrada'], 404);
        }

        $placa = $unidad->placa;

        // Traer vueltas programadas del día desde el servicio externo
        try {
            $extResp = Http::timeout(10)->post('http://precisobus.precisogps.com:3000/app/mis-vueltas', [
                'placa' => $placa,
            ]);
            $vueltasProgramadas = $extResp->successful() ? $extResp->json() : [];
        } catch (\Throwable $e) {
            $vueltasProgramadas = [];
        }

        // Traer producciones ya guardadas en la hoja del día
        $fecha = Carbon::now('America/Guayaquil')->format('Y-m-d');
        $hoja  = HojaTrabajo::with('producciones')
            ->where('id_unidad', $id)
            ->where('fecha', $fecha)
            ->first();

        $vueltasEnviadas = [];
        if ($hoja) {
            $vueltasEnviadas = $hoja->producciones->pluck('nro_vuelta')->toArray();
        }

        // Hora actual en Ecuador para comparar
        $ahora = Carbon::now('America/Guayaquil');

        $vueltas = [];
        foreach ($vueltasProgramadas as $index => $v) {
            $nroVuelta  = $index + 1;
            $horaFinStr = $v['horaFin'] ?? null;

            if (in_array($nroVuelta, $vueltasEnviadas)) {
                $estado = 'enviada';
            } elseif ($horaFinStr && $ahora->gt(Carbon::createFromFormat('H:i', $horaFinStr, 'America/Guayaquil'))) {
                $estado = 'pendiente';
            } else {
                $estado = 'futura';
            }

            $vueltas[] = [
                'nro_vuelta'  => $nroVuelta,
                'ruta'        => $v['ruta']        ?? null,
                'hora_inicio' => $v['horaInicio']  ?? null,
                'hora_fin'    => $horaFinStr,
                'estado'      => $estado,
            ];
        }

        return response()->json([
            'id_unidad' => (int) $id,
            'placa'     => $placa,
            'id_hoja'   => $hoja?->id_hoja,
            'vueltas'   => $vueltas,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'numero_habilitacion' => 'nullable|string|max:255',
            'placa' => 'nullable|string|max:255',
            'propietario' => 'nullable|string|max:255',
            'anio_fabricacion' => 'nullable|integer',
            'chasis' => 'nullable|string|max:255',
            'carroceria' => 'nullable|string|max:255',
            'tipo_especial' => 'nullable|string|max:255',
            'capacidad_pasajeros' => 'nullable|integer',
            'puertas_ingreso' => 'nullable|integer',
            'puertas_izquierdas' => 'nullable|integer',
            'usu_id' => 'nullable|exists:USUARIO,USU_ID'
        ]);

        $unidad = Unidad::findOrFail($id);
        $unidad->update($request->all());

        return redirect()->route('unidades.index')->with('success', 'Unidad actualizada exitosamente.');
    }
}
