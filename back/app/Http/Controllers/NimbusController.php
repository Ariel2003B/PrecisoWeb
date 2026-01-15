<?php

namespace App\Http\Controllers;

use App\Models\GeoStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
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


        // ---- ORDENAR VUELTAS POR HORA ----
        $toMinutes = function ($hhmm) {
            if (!is_string($hhmm) || !preg_match('/^\d{2}:\d{2}$/', $hhmm)) {
                return PHP_INT_MAX; // sin hora => al final
            }
            [$H, $M] = explode(':', $hhmm);
            return ((int) $H) * 60 + ((int) $M);
        };

        if (is_array($rutas)) {
            foreach ($rutas as &$ruta) {
                if (!isset($ruta['data']) || !is_array($ruta['data']))
                    continue;


                usort($ruta['data'], function ($a, $b) use ($toMinutes) {
                    $pa = $a['horaProgramada'] ?? [];
                    $pb = $b['horaProgramada'] ?? [];

                    $startA = isset($pa[0]) ? $toMinutes($pa[0]) : PHP_INT_MAX;
                    $startB = isset($pb[0]) ? $toMinutes($pb[0]) : PHP_INT_MAX;

                    if ($startA !== $startB)
                        return $startA <=> $startB;

                    // desempate por última hora de la rutina
                    $endA = !empty($pa) ? $toMinutes($pa[count($pa) - 1]) : PHP_INT_MAX;
                    $endB = !empty($pb) ? $toMinutes($pb[count($pb) - 1]) : PHP_INT_MAX;

                    if ($endA !== $endB)
                        return $endA <=> $endB;

                    // último desempate por nombre/placa
                    $na = strtoupper(trim($a['nombreUnidad'] ?? ''));
                    $nb = strtoupper(trim($b['nombreUnidad'] ?? ''));
                    return $na <=> $nb;
                });
            }
            unset($ruta);
        }

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

    public function exportMinutosCaidosRango(Request $request)
    {
        // === 1) Auth / empresa ===
        $user = $request->user();
        if (!$user)
            abort(401, 'No autenticado.');

        $empresa = $user->empresa;
        if (!$empresa)
            abort(422, 'El usuario no tiene empresa asociada.');

        if (empty($empresa->TOKEN) || empty($empresa->DEPOT)) {
            abort(422, 'La empresa no tiene TOKEN o DEPOT configurados.');
        }

        // === 2) Validar fechas ===
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $idUnidad = (int) ($request->input('id_unidad') ?? 0);

        if (
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fechaDesde) ||
            !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $fechaHasta)
        ) {
            abort(422, 'Fechas inválidas. Usa Y-m-d.');
        }

        // (opcional) normaliza orden si vienen invertidas
        if ($fechaDesde > $fechaHasta) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        // === 3) Llamar a tu backend .NET (full days) ===
        $backendUrl = $request->input(
            'url',
            'http://159.203.177.210:443/api/minutoscaidos/report-full-days'
        );

        // Ajusta nombres EXACTOS según tu .NET
        $payload = [
            'token' => $empresa->TOKEN,
            'depot' => (int) $empresa->DEPOT,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'idUnidad' => $idUnidad, // 0 = todas
        ];

        try {
            $resp = Http::timeout(180)->post($backendUrl, $payload);
        } catch (\Throwable $e) {
            abort(500, 'No se pudo contactar el backend: ' . $e->getMessage());
        }

        if ($resp->failed()) {
            abort(500, 'Backend respondió con error (' . $resp->status() . ').');
        }

        $rutas = $resp->json();
        if (!is_array($rutas) || empty($rutas)) {
            abort(404, 'Sin datos para el rango indicado.');
        }

        // === 4) Adjuntar tarifas igual que en tu reporteDiaAll ===
        foreach ($rutas as &$ruta) {
            $stops = $ruta['stops'] ?? [];
            $nimbusIds = array_values(array_unique(array_map(
                fn($s) => (int) ($s['id'] ?? 0),
                is_array($stops) ? $stops : []
            )));
            $map = \App\Models\GeoStop::mapaTarifas($empresa->EMP_ID, $nimbusIds);
            $ruta['tarifas'] = collect($map)->mapWithKeys(fn($v, $k) => [(string) $k => (float) $v])->all();
        }
        unset($ruta);

        // === 5) Reemplazar nombreUnidad si vino solo numérico (igual que tú) ===
        $unidades = \App\Models\Unidad::query()
            ->get(['unidades.idWialon', 'unidades.placa', 'unidades.numero_habilitacion']);

        $displayByWialon = [];
        foreach ($unidades as $un) {
            $idW = (int) ($un->idWialon ?? 0);
            if ($idW <= 0)
                continue;

            $placa = trim((string) $un->placa);
            $hab = trim((string) $un->numero_habilitacion);

            $display = '';
            if ($placa !== '' && $hab !== '')
                $display = strtoupper($placa) . '(' . $hab . ')';
            elseif ($placa !== '')
                $display = strtoupper($placa);
            elseif ($hab !== '')
                $display = '(' . $hab . ')';

            if ($display !== '')
                $displayByWialon[$idW] = $display;
        }

        foreach ($rutas as &$r) {
            if (!isset($r['data']) || !is_array($r['data']))
                continue;
            foreach ($r['data'] as &$vuelta) {
                $idU = (int) ($vuelta['idUnidad'] ?? 0);
                $nom = trim((string) ($vuelta['nombreUnidad'] ?? ''));

                if ($idU > 0 && $nom !== '' && ctype_digit($nom) && (int) $nom === $idU) {
                    if (isset($displayByWialon[$idU]))
                        $vuelta['nombreUnidad'] = $displayByWialon[$idU];
                }
            }
            unset($vuelta);
        }
        unset($r);

        // === 6) Agrupar por fecha usando fechaYmd dentro de cada vuelta ===
        $rutasPorFecha = []; // ['2026-01-12' => [rutas clonadas con data filtrada]]
        foreach ($rutas as $ruta) {
            $vueltas = $ruta['data'] ?? [];
            if (!is_array($vueltas) || empty($vueltas))
                continue;

            $byDate = [];
            foreach ($vueltas as $v) {
                $f = (string) ($v['fechaYmd'] ?? '');
                if ($f === '')
                    continue;
                $byDate[$f][] = $v;
            }

            foreach ($byDate as $f => $vueltasDia) {
                $clone = $ruta;
                $clone['data'] = $vueltasDia;
                $rutasPorFecha[$f][] = $clone;
            }
        }

        if (empty($rutasPorFecha)) {
            abort(404, 'No se encontraron fechas en los datos (fechaYmd vacío).');
        }

        ksort($rutasPorFecha);

        // === 7) Crear Excel: 1 hoja por RUTA (encabezado correcto por ruta) ===
        $spreadsheet = new Spreadsheet();

        // estilos reutilizables (tu estilo)
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $headerStopNameStyle = $headerStyle;
        $headerStopNameStyle['alignment']['textRotation'] = 90;

        $headerStopSubStyle = $headerStyle;
        $headerStopSubStyle['font']['size'] = 9;   // más pequeño
// sin rotación


        $cellStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
        ];

        // Helper para nombre de hoja (31 chars max, sin caracteres raros)
        $sheetTitle = function (string $name) {
            $name = preg_replace('/[\\\\\\/\\?\\*\\[\\]:]/', ' ', $name); // Excel invalid chars
            $name = trim(preg_replace('/\\s+/', ' ', $name));
            return mb_substr($name, 0, 31);
        };

        $sheetIndex = 0;

        // Recorremos rutas "originales" (NO agrupadas por fecha) porque cada ruta trae sus stops correctos
        foreach ($rutas as $ruta) {

            $routeName = (string) ($ruta['nombre'] ?? ('Ruta ' . ($ruta['idRoute'] ?? '')));
            $stops = $ruta['stops'] ?? [];
            $tarifas = $ruta['tarifas'] ?? [];
            $vueltas = $ruta['data'] ?? [];

            if (!is_array($vueltas) || empty($vueltas))
                continue;

            // Crear hoja
            if ($sheetIndex === 0)
                $sheet = $spreadsheet->getActiveSheet();
            else
                $sheet = $spreadsheet->createSheet($sheetIndex);

            $sheet->setTitle($sheetTitle($routeName));

            // ===== HEADER EN 2 FILAS (Stop una sola vez + Plan/Eje/Dif abajo) =====
            $sheet->setCellValue('A1', 'FECHA');
            $sheet->setCellValue('B1', 'PLACA');
            $sheet->setCellValue('C1', 'RUTINA');

            // Rowspan=2 para FECHA/PLACA/RUTINA
            $sheet->mergeCells('A1:A2');
            $sheet->mergeCells('B1:B2');
            $sheet->mergeCells('C1:C2');

            // Empezamos a escribir stops desde la columna D
            $col = 4; // D = 4 (1=A)
            foreach ($stops as $s) {
                $stopName = (string) ($s['n'] ?? 'Parada');
                $stopName = mb_substr($stopName, 0, 24);

                $colStart = Coordinate::stringFromColumnIndex($col);
                $colMid = Coordinate::stringFromColumnIndex($col + 1);
                $colEnd = Coordinate::stringFromColumnIndex($col + 2);

                // Nombre de la parada (una sola vez arriba)
                $sheet->setCellValue($colStart . '1', $stopName);
                $sheet->mergeCells("{$colStart}1:{$colEnd}1");

                // Segunda fila: Plan/Eje/Dif
                $sheet->setCellValue($colStart . '2', 'Plan.');
                $sheet->setCellValue($colMid . '2', 'Eje.');
                $sheet->setCellValue($colEnd . '2', 'Dif');

                $col += 3;
            }

            // Totales al final (rowspan=2)
            $colAdel = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($colAdel . '1', 'Total adelantos');
            $sheet->mergeCells("{$colAdel}1:{$colAdel}2");
            $col++;

            $colAtr = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($colAtr . '1', 'Total atrasos');
            $sheet->mergeCells("{$colAtr}1:{$colAtr}2");
            $col++;

            $colSan = Coordinate::stringFromColumnIndex($col);
            $sheet->setCellValue($colSan . '1', 'Sanción (USD)');
            $sheet->mergeCells("{$colSan}1:{$colSan}2");

            // Última columna del header
            $lastCol = Coordinate::stringFromColumnIndex($col);
            // 1) base a todo el header (2 filas)
            $sheet->getStyle("A1:{$lastCol}2")->applyFromArray($headerStyle);

            // 2) Rotar SOLO nombres de paradas (fila 1 desde D hasta antes de Totales)
            $firstStopCol = 'D';
            $lastStopCol = Coordinate::stringFromColumnIndex(3 + count($stops) * 3); // último col de stops (incluye Plan/Eje/Dif)
            $sheet->getStyle("{$firstStopCol}1:{$lastStopCol}1")->applyFromArray($headerStopNameStyle);

            // 3) Plan/Eje/Dif sin rotación (fila 2)
            $sheet->getStyle("{$firstStopCol}2:{$lastStopCol}2")->applyFromArray($headerStopSubStyle);

            // 4) FECHA/PLACA/RUTINA sin rotación y centrado (ya lo hace $headerStyle, pero refuerzo)
            $sheet->getStyle("A1:C2")->applyFromArray($headerStyle);

            // Alturas más bonitas
            $sheet->getRowDimension(1)->setRowHeight(70); // más espacio para rotación
            $sheet->getRowDimension(2)->setRowHeight(18);

            // Estilo para TODO el header (2 filas)
            $sheet->getStyle("A1:{$lastCol}2")->applyFromArray($headerStyle);

            // Altura de filas (ajusta a gusto)
            $sheet->getRowDimension(1)->setRowHeight(55);
            $sheet->getRowDimension(2)->setRowHeight(22);

            // Anchos
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);

            // desde D en adelante compactito
            foreach (range('D', $lastCol) as $c) {
                $sheet->getColumnDimension($c)->setWidth(5);
            }
            usort($vueltas, function ($x, $y) {
                $fx = $x['fechaYmd'] ?? '';
                $fy = $y['fechaYmd'] ?? '';
                if ($fx !== $fy)
                    return strcmp($fx, $fy);
                $hx = $x['horaProgramada'][0] ?? '99:99';
                $hy = $y['horaProgramada'][0] ?? '99:99';
                return strcmp($hx, $hy);
            });
            // IMPORTANTE: ahora los datos empiezan en la fila 3
            $row = 3;


            foreach ($vueltas as $v) {

                $fechaYmd = (string) ($v['fechaYmd'] ?? '');
                if ($fechaYmd === '')
                    $fechaYmd = $fechaDesde; // fallback

                $plan = $v['horaProgramada'] ?? [];
                $eje = $v['horaEjecutada'] ?? [];
                $dif = $v['diferencia'] ?? [];

                $first = $plan[0] ?? '--:--';
                $last = (is_array($plan) && count($plan) > 0) ? ($plan[count($plan) - 1] ?? '--:--') : '--:--';
                $rutina = ($first ?: '--:--') . ' - ' . ($last ?: '--:--');

                $placa = (string) ($v['nombreUnidad'] ?? '');

                $adelantos = 0;
                $atrasos = 0;
                $sancion = 0;

                $fila = [$fechaYmd, $placa, $rutina];

                // IMPORTANTE: aquí iteramos por count($stops) (paradas de ESTA ruta)
                for ($j = 0; $j < count($stops); $j++) {

                    $p = $plan[$j] ?? '--:--';
                    $a = $eje[$j] ?? '--:--';
                    $d = $dif[$j] ?? null;

                    $fila[] = $p;
                    $fila[] = $a;
                    $fila[] = ($d === null || $d === '') ? '—' : (int) $d;

                    if ($d !== null && $d !== '') {
                        $n = (int) $d;
                        if ($n > 0)
                            $adelantos += $n;
                        elseif ($n < 0)
                            $atrasos += (-$n);

                        if ($n < 0) {
                            $stopId = (int) ($stops[$j]['id'] ?? 0);
                            $tarifa = (float) ($tarifas[(string) $stopId] ?? 0);
                            $sancion += (-$n) * $tarifa;
                        }
                    }
                }

                $fila[] = $adelantos;
                $fila[] = $atrasos;
                $fila[] = '$' . number_format((float) $sancion, 2, '.', ',');

                $sheet->fromArray($fila, null, "A{$row}");
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($cellStyle);

                $row++;
            }

            $sheetIndex++;
        }

        // Si no se creó ninguna hoja con data, error
        if ($sheetIndex === 0) {
            abort(404, 'Sin datos para exportar.');
        }

        // === 8) Descargar ===
        $writer = new Xlsx($spreadsheet);
        $fileName = date("Y-m-d") . "_MinutosCaidos_{$fechaDesde}_{$fechaHasta}.xlsx";
        $tempFile = tempnam(sys_get_temp_dir(), 'mc_');
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);

    }


}


