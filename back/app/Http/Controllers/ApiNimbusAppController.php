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
        // Espera: [ { "nm": "ABC1234 (12/1122)", "id": 401971053 }, ... ]
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

        $dryRun = $request->boolean('dry', false);
        $updated = [];
        $created = [];
        $notFound = [];
        $conflicts = [];

        DB::transaction(function () use ($payload, $dryRun, &$updated, &$created, &$notFound, &$conflicts) {
            foreach ($payload as $row) {
                $nm = trim($row['nm']);

                // Normalizar espacios Unicode (NBSP, thin space, etc.) a espacio normal
                $nm = preg_replace('/[\\x{00A0}\\x{2000}-\\x{200B}\\x{202F}\\x{205F}\\x{3000}]/u', ' ', $nm);
                // Colapsar múltiples espacios a uno
                $nm = preg_replace('/[[:space:]]+/u', ' ', $nm);

                $wId = (int) $row['id'];

                // 1) Parsear "PLACA (HAB)" admitiendo puntos finales opcionales
                $parsed = $this->parsePlacaHabilitacion($nm);

                $query = Unidad::query()->select('id_unidad', 'placa', 'numero_habilitacion');

                if ($parsed) {
                    // Limpia placa y hab para comparar y guardar
                    $placaClean = strtoupper(trim($parsed['placa']));
                    // Quitamos puntos finales (si vienen), NO tocamos barras
                    $habClean = rtrim(trim($parsed['hab']), ". ");

                    // Comparación ignorando espacios y puntos
                    $query->whereRaw(
                        'UPPER(REPLACE(REPLACE(placa, " ", ""), ".", "")) = ?',
                        [strtoupper(str_replace([' ', '.'], '', $placaClean))]
                    )->whereRaw(
                            'REPLACE(TRIM(numero_habilitacion), ".", "") = ?',
                            [str_replace('.', '', $habClean)]
                        );
                } else {
                    // Fallback por string completo (sin espacios ni puntos) o por placa sola
                    $nmNorm = strtoupper($this->normalizeName($nm)); // quita espacios y puntos
                    $query->where(function ($q) use ($nmNorm) {
                        $q->orWhereRaw(
                            'UPPER(REPLACE(REPLACE(CONCAT(placa, " (", numero_habilitacion, ")"), " ", ""), ".", "")) = ?',
                            [$nmNorm]
                        );
                        $q->orWhereRaw(
                            'UPPER(REPLACE(REPLACE(CONCAT(placa, "(", numero_habilitacion, ")"), " ", ""), ".", "")) = ?',
                            [$nmNorm]
                        );
                        $q->orWhereRaw(
                            'UPPER(REPLACE(REPLACE(placa, " ", ""), ".", "")) = ?',
                            [$nmNorm]
                        );
                    });
                }

                $matches = $query->get();

                if ($matches->count() === 1) {
                    // --- Coincidencia única -> actualizar ---
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
                    // --- Varias coincidencias -> no tocar nada, reportar ---
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
                    // --- No existe -> crear si pudimos parsear ---
                    if ($parsed) {
                        $placaClean = strtoupper(trim($parsed['placa']));
                        $habClean = rtrim(trim($parsed['hab']), ". "); // quita puntos finales

                        if (!$dryRun) {
                            $unidad = Unidad::create([
                                'placa' => $placaClean,
                                'numero_habilitacion' => $habClean,
                                'idWialon' => $wId,
                            ]);
                        } else {
                            $unidad = (object) [
                                'id_unidad' => null,
                                'placa' => $placaClean,
                                'numero_habilitacion' => $habClean,
                            ];
                        }

                        $created[] = [
                            'unidad_id' => $unidad->id_unidad,
                            'placa' => $placaClean,
                            'numero_habilitacion' => $habClean,
                            'idWialon' => $wId,
                            'from' => $nm
                        ];
                    } else {
                        // No se pudo extraer placa y hab con seguridad
                        $notFound[] = $nm;
                    }
                }
            }
        });

        return response()->json([
            'ok' => true,
            'dryRun' => $dryRun,
            'updated_count' => count($updated),
            'created_count' => count($created),
            'not_found_count' => count($notFound),
            'conflicts_count' => count($conflicts),
            'updated' => $updated,
            'created' => $created,
            'not_found' => $notFound,
            'conflicts' => $conflicts
        ]);
    }

    /**
     * Parsear "PLACA (HAB)" o "PLACA(HAB)" con puntos finales opcionales.
     * Retorna ['placa' => ..., 'hab' => ...] o null si no matchea.
     */
    private function parsePlacaHabilitacion(string $nm): ?array
    {
        // Permite espacios y cualquier cantidad de puntos tras el cierre de ')'
        if (preg_match('/^\s*([A-Z0-9\-]+)\s*\(\s*([^)]+)\s*\)\s*\.{0,}\s*$/i', $nm, $m)) {
            // No limpiamos aquí (solo recortamos); la limpieza final ocurre antes de guardar/comparar
            return [
                'placa' => strtoupper(trim($m[1])),
                'hab' => trim($m[2]),
            ];
        }
        return null;
    }

    /**
     * Normaliza para comparar: mayúsculas y sin espacios ni puntos.
     * OJO: sólo para COMPARAR, no para GUARDAR.
     */
    private function normalizeName(string $s): string
    {
        $s = strtoupper($s);
        // Quitar TODOS los espacios (incl. Unicode) y puntos
        $s = preg_replace('/[[:space:]\\x{00A0}\\x{2000}-\\x{200B}\\x{202F}\\x{205F}\\x{3000}\.]/u', '', $s);
        return $s;
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

