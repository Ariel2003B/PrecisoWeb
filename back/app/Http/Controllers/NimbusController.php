<?php

namespace App\Http\Controllers;

use App\Models\GeoStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class NimbusController extends Controller
{
    public function reporteDiaAll(Request $request)
    {
        // Usuario autenticado
        $user = $request->user();
        if (!$user) {
            abort(401, 'No autenticado.');
        }

        // Empresa del usuario (relación USUARIO->empresa)
        $empresa = $user->empresa;
        if (!$empresa) {
            abort(422, 'El usuario no tiene empresa asociada.');
        }

        // Validaciones mínimas
        if (empty($empresa->TOKEN) || empty($empresa->DEPOT)) {
            abort(422, 'La empresa no tiene TOKEN o DEPOT configurados.');
        }

        // Fecha: usa la enviada o por defecto hoy (America/Guayaquil)
        $fecha = $request->input('fecha');
        if (!$fecha) {
            $fecha = Carbon::now('America/Guayaquil')->toDateString(); // Y-m-d
        } else {
            // valida formato Y-m-d simple
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                abort(422, 'La fecha debe tener formato Y-m-d.');
            }
        }

        // Permite sobreescribir la URL por query (si quieres), con default a tu back local
        $backendUrl = $request->input(
            'url',
            'http://159.203.177.210:5000/api/minutoscaidos/reporte-dia-all'
        );

        // Payload que espera tu back .NET
        $payload = [
            'token' => $empresa->TOKEN,
            'depot' => (int) $empresa->DEPOT,
            'fecha' => $fecha,
        ];

        // Llamada al back
        try {
            $resp = Http::timeout(90)->post($backendUrl, $payload);
        } catch (\Throwable $e) {
            // Puedes loguearlo si quieres: \Log::error($e);
            return view('nimbus.reporte', [
                'fecha' => $fecha,
                'empresa' => $empresa,
                'rutas' => [],
                'error' => 'No se pudo contactar el backend: ' . $e->getMessage(),
            ]);
        }

        if ($resp->failed()) {
            return view('nimbus.reporte', [
                'fecha' => $fecha,
                'empresa' => $empresa,
                'rutas' => [],
                'error' => 'Backend respondió con error (' . $resp->status() . ').',
            ]);
        }

        $rutas = $resp->json(); // Estructura tal cual la que envías en el ejemplo
        /* Adjunta a cada ruta un mapa de tarifas [NIMBUS_ID => VALOR_MINUTO] */
        foreach ($rutas as &$ruta) {
            $stops = $ruta['stops'] ?? [];
            // IDs de nimbus que vienen en la ruta
            $nimbusIds = array_values(array_unique(array_map(
                fn($s) => (int) ($s['id'] ?? 0),
                $stops
            )));
            // Mapa desde BD
            $map = GeoStop::mapaTarifas($empresa->EMP_ID, $nimbusIds);

            // Fuerza llaves como strings para que no se “aplane” en JSON
            $map = collect($map)->mapWithKeys(fn($v, $k) => [(string) $k => (float) $v])->all();

            $ruta['tarifas'] = $map;
        }
        unset($ruta);
        // Retorna a la vista (luego me dices cómo la quieres)
        return view('nimbus.reporte', [
            'fecha' => $fecha,
            'empresa' => $empresa,
            'rutas' => $rutas,
            // 'payload' => $payload, // útil para debug si deseas
        ]);
    }
}
