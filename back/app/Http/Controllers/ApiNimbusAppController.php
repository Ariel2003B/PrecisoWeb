<?php
namespace App\Http\Controllers;

use App\Models\GeoStop;
use Illuminate\Http\Request;
use App\Models\Unidad;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
            'placa' => $unidad->placa . '(' . $unidad->numero_habilitacion . ')  ',
            'idWialon' => $unidad->idWialon,
            'token' => $empresa?->TOKEN,
            'depot' => $empresa?->DEPOT,

        ]);
    }



    public function updateIdWialon(Request $request)
    {
        // Espera un array tipo: [ { "nm": "ABC1234 (15/2515)", "id": 401971053 }, ... ]
        $payload = $request->json()->all();

        $v = Validator::make(
            ['items' => $payload],
            [
                'items' => 'required|array|min:1',
                'items.*.nm' => 'required|string',
                'items.*.id' => 'required|integer'
            ]
        );

        if ($v->fails()) {
            return response()->json([
                'ok' => false,
                'errors' => $v->errors()
            ], 422);
        }

        $dryRun = $request->boolean('dry', false); // opcional ?dry=1 para probar sin guardar
        $updated = [];
        $created = [];        // <-- NUEVO
        $notFound = [];
        $conflicts = [];       // por si hay más de una coincidencia

        DB::transaction(function () use ($payload, $dryRun, &$updated, &$created, &$notFound, &$conflicts) {
            foreach ($payload as $row) {
                $nm = trim($row['nm']);
                $wId = (int) $row['id'];

                // 1) Intentar parsear "PLACA (HAB)"
                $parsed = $this->parsePlacaHabilitacion($nm);

                $query = Unidad::query()->select('id_unidad', 'placa', 'numero_habilitacion');

                if ($parsed) {
                    // Búsqueda exacta por placa + numero_habilitacion
                    $query->whereRaw('UPPER(REPLACE(placa, " ", "")) = ?', [strtoupper(str_replace(' ', '', $parsed['placa']))])
                        ->whereRaw('TRIM(numero_habilitacion) = ?', [trim($parsed['hab'])]);
                } else {
                    // Fallback: intentar por concatenación "placa (hab)" o "placa(hab)" o por placa sola
                    $nmNorm = strtoupper($this->normalizeName($nm));
                    $query->where(function ($q) use ($nmNorm) {
                        $q->orWhereRaw('UPPER(REPLACE(CONCAT(placa, " (", numero_habilitacion, ")"), " ", "")) = ?', [$nmNorm]);
                        $q->orWhereRaw('UPPER(REPLACE(CONCAT(placa, "(", numero_habilitacion, ")"), " ", "")) = ?', [$nmNorm]);
                        $q->orWhereRaw('UPPER(REPLACE(placa, " ", "")) = ?', [$nmNorm]);
                    });
                }

                $matches = $query->get();

                if ($matches->count() === 1) {
                    // --- Caso: existe exactamente una ---
                    $unidad = $matches->first();
                    if (!$dryRun) {
                        $unidad->idWialon = $wId;
                        $unidad->save();
                    }
                    $updated[] = [
                        'unidad_id' => $unidad->id_unidad,
                        'placa' => $unidad->placa,
                        'numero_habilitacion' => $unidad->numero_habilitacion,
                        'idWialon' => $wId,
                        'from' => $nm
                    ];
                } elseif ($matches->count() > 1) {
                    // --- Caso: varios candidatos (no tocamos nada) ---
                    $conflicts[] = [
                        'from' => $nm,
                        'count' => $matches->count(),
                        'candidatos' => $matches->map(fn($u) => [
                            'id_unidad' => $u->id_unidad,
                            'placa' => $u->placa,
                            'numero_habilitacion' => $u->numero_habilitacion,
                        ])->values()
                    ];
                } else {
                    // --- Caso: no existe ---
                    if ($parsed) {
                        // Si se pudo parsear "PLACA (HAB)", creamos la unidad
                        if (!$dryRun) {
                            $unidad = Unidad::create([
                                'placa' => $parsed['placa'],
                                'numero_habilitacion' => $parsed['hab'],
                                'idWialon' => $wId,
                                // agrega aquí otros defaults si quieres: 'usu_id' => auth()->id(),
                            ]);
                        } else {
                            // Solo preview en dry-run
                            $unidad = (object) [
                                'id_unidad' => null,
                                'placa' => $parsed['placa'],
                                'numero_habilitacion' => $parsed['hab'],
                            ];
                        }

                        $created[] = [
                            'unidad_id' => $unidad->id_unidad,
                            'placa' => $parsed['placa'],
                            'numero_habilitacion' => $parsed['hab'],
                            'idWialon' => $wId,
                            'from' => $nm
                        ];
                    } else {
                        // Si no parsea, no podemos crear con seguridad
                        $notFound[] = $nm;
                    }
                }
            }
        });

        return response()->json([
            'ok' => true,
            'dryRun' => $dryRun,
            'updated_count' => count($updated),
            'created_count' => count($created),   // <-- NUEVO
            'not_found_count' => count($notFound),
            'conflicts_count' => count($conflicts),
            'updated' => $updated,
            'created' => $created,          // <-- NUEVO
            'not_found' => $notFound,
            'conflicts' => $conflicts
        ]);
    }


    /**
     * Parsear "PLACA (HAB)" o "PLACA(HAB)" → ['placa' => ..., 'hab' => ...]
     * Retorna null si no coincide el patrón.
     */
    private function parsePlacaHabilitacion(string $nm): ?array
    {
        // Admite espacios opcionales antes/después del paréntesis
        if (preg_match('/^\s*([A-Z0-9\-]+)\s*\(\s*([^)]+)\s*\)\s*$/i', $nm, $m)) {
            return [
                'placa' => strtoupper(trim($m[1])),
                'hab' => trim($m[2])
            ];
        }
        return null;
    }

    /**
     * Normaliza para comparar: mayúsculas y sin espacios.
     */
    private function normalizeName(string $s): string
    {
        return strtoupper(str_replace(' ', '', $s));
    }

    public function getValorGeocercaTest(Request $request)
    {
        $data = $request->validate([
            'idWialon' => 'required'
        ]);

        // Busca la unidad por idWialon y trae directamente el valor de sanción
        $valor = Unidad::query()
            ->join('USUARIO as u', 'unidades.usu_id', '=', 'u.USU_ID')
            ->join('EMPRESA as e', 'u.EMP_ID', '=', 'e.EMP_ID')
            ->where('unidades.idWialon', $data['idWialon'])
            ->value('e.VALOR_SANCION_GEOCERCA'); // <-- devuelve un escalar

        if ($valor === null) {
            // Si no se encuentra, 404 (puedes cambiar a 200 con 0 si prefieres)
            return response()->json(null, 404);
        }

        // "Solo ese dato": un número JSON (no un objeto envoltorio)
        return response()->json($valor);
    }


    public function getValorGeocerca(Request $request)
    {
        $data = $request->validate([
            'idWialon' => 'required'
        ]);

        // 1) Ubicar la EMPRESA a partir de la unidad (igual que antes)
        $empId = Unidad::query()
            ->join('USUARIO as u', 'unidades.usu_id', '=', 'u.USU_ID')
            ->join('EMPRESA as e', 'u.EMP_ID', '=', 'e.EMP_ID')
            ->where('unidades.idWialon', $data['idWialon'])
            ->value('e.EMP_ID');

        if ($empId === null) {
            // No existe la unidad/empresa para ese idWialon
            return response()->json(null, 404);
        }

        // 2) Traer las geocercas (stops) activas de esa empresa: NIMBUS_ID y VALOR_MINUTO
        $rows = GeoStop::deEmpresa((int) $empId)
            ->activas()
            ->get(['NIMBUS_ID', 'VALOR_MINUTO']);

        // 3) Formato de salida: lista de objetos { nimbusId, valorMinuto }
        $tarifas = $rows->map(fn($r) => [
            'paradaId' => (int) $r->NIMBUS_ID,
            'valorMinuto' => (float) $r->VALOR_MINUTO,
        ])->values();

        // Si no hay registros, devolvemos lista vacía con 200
        return response()->json($tarifas);
    }

}

