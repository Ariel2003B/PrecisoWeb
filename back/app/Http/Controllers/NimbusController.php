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
            'http://159.203.177.210:443/api/minutoscaidos/reporte-merge'
            // 'http://localhost:5000/api/minutoscaidos/reporte-merge'
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
        $unidades = \App\Models\Unidad::query()
            ->get(['unidades.idWialon', 'unidades.placa', 'unidades.numero_habilitacion']);
        $rutas = $resp->json(); // Estructura tal cual la que envías en el ejemplo
        /* Adjunta a cada ruta un mapa de tarifas [NIMBUS_ID => VALOR_MINUTO] */
        $displayByWialon = [];
        foreach ($unidades as $un) {
            $idW = (int) ($un->idWialon ?? 0);
            if ($idW <= 0)
                continue;

            $placa = trim((string) $un->placa);
            $hab = trim((string) $un->numero_habilitacion);

            // Construcción del display:
            //  - Si hay placa y habilitación: "PLACA(HAB)"
            //  - Si solo placa: "PLACA"
            //  - Si solo habilitación: "(HAB)"
            //  - Si no hay nada: queda vacío y no reemplazamos
            $display = '';
            if ($placa !== '' && $hab !== '')
                $display = strtoupper($placa) . '(' . $hab . ')';
            elseif ($placa !== '')
                $display = strtoupper($placa);
            elseif ($hab !== '')
                $display = '(' . $hab . ')';

            if ($display !== '') {
                $displayByWialon[$idW] = $display;
            }
        }

        // Recorremos rutas y reemplazamos NombreUnidad cuando sea numérico = idWialon
        if (is_array($rutas)) {
            foreach ($rutas as &$r) {
                if (!isset($r['data']) || !is_array($r['data']))
                    continue;

                foreach ($r['data'] as &$vuelta) {
                    $idUnidad = (int) ($vuelta['idUnidad'] ?? 0);
                    $nomOrig = trim((string) ($vuelta['nombreUnidad'] ?? ''));

                    // Caso a cubrir: cuando el nombre viene como "136433" (numérico puro)
                    // y coincide con el idWialon/idUnidad
                    $esSoloNumero = ($nomOrig !== '' && ctype_digit($nomOrig));
                    if ($idUnidad > 0 && $esSoloNumero && (int) $nomOrig === $idUnidad) {
                        if (isset($displayByWialon[$idUnidad])) {
                            $vuelta['nombreUnidad'] = $displayByWialon[$idUnidad];
                        }
                    }
                }
                unset($vuelta);
            }
            unset($r);
        }



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
        // Si viene en modo "poll" (consulta periódica), responde JSON simple
        if ($request->boolean('poll')) {
            return response()->json([
                'fecha' => $fecha,
                'rutas' => $rutas,  // mismo shape que usas en Blade (stops, data, tarifas, etc.)
            ]);
        }

        // Retorna a la vista (luego me dices cómo la quieres)
        return view('nimbus.reporte', [
            'fecha' => $fecha,
            'empresa' => $empresa,
            'rutas' => $rutas,
            // 'payload' => $payload, // útil para debug si deseas
        ]);
    }
}
