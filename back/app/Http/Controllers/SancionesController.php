<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Hamcrest\Core\IsNull;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;



class SancionesController extends Controller
{
    // public function index()
    // {
    //     return view('sanciones.sanciones');
    // }

    public function index($parametro)
    {
        // Obtener detalles de la ruta seleccionada
        $details = DB::table('sanciones')
            ->select('sanciones.ruta', 'sanciones.fecha')
            ->where('sanciones.ruta', '=', $parametro)
            ->first();

        // Inicializar `$detalles` con valores predeterminados
        $detalles = $details ? (array) $details : [
            'ruta' => $parametro,
            'fecha' => 'No disponible',
        ];

        // Obtener sanciones relacionadas con la ruta
        $sanciones = DB::table('sanciones')
            ->join('geocercas', 'sanciones.id', '=', 'geocercas.sancion_id')
            ->select(
                'sanciones.id as sancion_id',
                'sanciones.unidad',
                'sanciones.vuelta',
                'sanciones.hora',
                'sanciones.total',
                'sanciones.valor_total',
                'sanciones.ruta',
                'sanciones.fecha',
                'geocercas.nombre as geocerca_nombre',
                'geocercas.sancion as geocerca_sancion'
            )
            ->where('sanciones.ruta', '=', $parametro)
            ->orderBy('sanciones.vuelta')
            ->get();

        // Agrupar datos por sanción ID
        $datos = $sanciones->groupBy('sancion_id')->map(function ($grupo) {
            $primerElemento = $grupo->first();
            return [
                'fecha' => $primerElemento->fecha,
                'ruta' => $primerElemento->ruta,
                'vuelta' => $primerElemento->vuelta,
                'unidad' => $primerElemento->unidad,
                'hora' => $primerElemento->hora,
                'sanciones' => $grupo->pluck('geocerca_sancion')->toArray(),
                'total' => $primerElemento->total,
                'valor_total' => $primerElemento->valor_total,
            ];
        });

        // Extraer geocercas únicas
        $geocercas = $sanciones->pluck('geocerca_nombre')->unique();

        // Retornar vista con datos
        return view('sanciones.sanciones', compact('datos', 'geocercas', 'detalles'));
    }

    // public function cargarCSV(Request $request)
    // {
    //     $request->validate([
    //         'archivo' => 'required|mimes:csv,txt',
    //     ]);

    //     $rutaArchivo = $request->file('archivo')->store('temp');
    //     $rutaCompleta = storage_path('app/' . $rutaArchivo);

    //     // Leer el archivo CSV
    //     $archivo = fopen($rutaCompleta, 'r');
    //     $encabezados = fgetcsv($archivo, 1000, ','); // Leer la primera fila como encabezados
    //     // Capturar los dos primeros valores
    //     $date = $encabezados[0];
    //     $fecha = DateTime::createFromFormat('d-m-Y', $date);

    //     $ruta = $encabezados[1];
    //     $encabezados = array_slice($encabezados, 2);


    //     // Extraer geocercas de los encabezados
    //     $geocercas = array_filter($encabezados, function ($columna) {
    //         return preg_match('/^\d+\.\s+.+$/u', $columna);
    //     });
    //     // Reindexar y ordenar las geocercas
    //     $geocercasOrdenadas = [];
    //     foreach ($geocercas as $columna) {
    //         // Extraer el índice inicial del encabezado (ejemplo: "1. Carapungo" => 1)
    //         preg_match('/^(\d+)\./', $columna, $matches);
    //         $indice = isset($matches[1]) ? (int) $matches[1] : PHP_INT_MAX;

    //         // Asignar a un array temporal con el índice extraído como clave
    //         $geocercasOrdenadas[$indice] = $columna;
    //     }

    //     // Ordenar por clave numérica (índice)
    //     ksort($geocercasOrdenadas);

    //     // Reindexar desde 0
    //     $geocercas = array_values($geocercasOrdenadas);

    //     $datos = [];
    //     $unidades = [];
    //     $contadorFila = 0; // Contador para identificar las filas
    //     $unidadesRep = [];
    //     while (($fila = fgetcsv($archivo, 1000, ',')) !== false) {
    //         $contadorFila++;

    //         // Omitir las primeras filas, incluyendo encabezados
    //         if ($contadorFila <= 1) {
    //             continue;
    //         }
    //         try {
    //             $unidad = $fila[0]; // Columna de unidad
    //             $time = $fila[1];
    //             $hora = str_replace(' ', '', $time);
    //         } catch (\Throwable $th) {
    //             $error = $th->getMessage();
    //         }

    //         // Extraer los valores "Min" a partir de la columna 5, con un salto de 3 columnas
    //         $minutos = [];
    //         for ($i = 4; $i < count($fila); $i += 3) {
    //             $minutos[] = $fila[$i];
    //         }

    //         if (!isset($unidades[$unidad])) {
    //             $unidades[$unidad] = 1; // Primera vuelta
    //         }

    //         $sanciones = array_map(function ($min) {
    //             // Validar si empieza con '-' seguido por un número
    //             return preg_match('/^-\d+$/', $min) ? 1 : 0;
    //         }, $minutos);

    //         $totalSanciones = array_sum($sanciones);
    //         $valorTotal = $totalSanciones * (0.25 * $unidades[$unidad]);

    //         $unidadesRep[] = $unidad;

    //         $contadorVueltas = 0;
    //         foreach ($unidadesRep as $data) {
    //             if ($data === $unidad) {
    //                 $contadorVueltas++;
    //             }
    //         }
    //         $sancionId = DB::table('sanciones')->insertGetId([
    //             'unidad' => $unidad,
    //             'vuelta' => $contadorVueltas,
    //             'hora' => $hora,
    //             'total' => $totalSanciones,
    //             'valor_total' => $valorTotal,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //             'fecha' => $fecha,
    //             'ruta' => $ruta
    //         ]);

    //         // Insertar sanciones de geocercas
    //         foreach ($geocercas as $index => $nombreGeocerca) {
    //             DB::table('geocercas')->insert([
    //                 'sancion_id' => $sancionId,
    //                 'nombre' => $nombreGeocerca,
    //                 'sancion' => $sanciones[$index] ?? 0,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    //         if ($totalSanciones > 0) {
    //             $unidades[$unidad]++;
    //         }
    //     }

    //     DB::commit();

    //     fclose($archivo);

    //     return redirect()->route('sanciones.index', ['parametro' => $ruta])->with('success', 'Datos cargados correctamente.');
    // }


    public function cargarCSV(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:csv,txt',
        ]);

        $rutaArchivo = $request->file('archivo')->store('temp');
        $rutaCompleta = storage_path('app/' . $rutaArchivo);

        // Leer el archivo CSV
        $archivo = fopen($rutaCompleta, 'r');
        $encabezados = fgetcsv($archivo, 1000, ','); // Leer la primera fila como encabezados

        // Capturar los dos primeros valores
        $date = $encabezados[0];
        $fecha = DateTime::createFromFormat('d-m-Y', $date);
        $ruta = $encabezados[1];
        $encabezados = array_slice($encabezados, 2); // Omitir fecha y ruta

        // Extraer geocercas de los encabezados
        $geocercas = array_filter($encabezados, function ($columna) {
            return preg_match('/^\d+\.\s+.+$/u', $columna);
        });

        // Ordenar las geocercas por su índice
        $geocercasOrdenadas = [];
        foreach ($geocercas as $columna) {
            preg_match('/^(\d+)\./', $columna, $matches);
            $indice = isset($matches[1]) ? (int) $matches[1] : PHP_INT_MAX;
            $geocercasOrdenadas[$indice] = $columna;
        }
        ksort($geocercasOrdenadas);
        $geocercas = array_values($geocercasOrdenadas);

        // 🔥 **Eliminar la primera y última geocerca**
        array_shift($geocercas); // Elimina la primera
        array_pop($geocercas);   // Elimina la última

        $datos = [];
        $unidades = [];
        $contadorFila = 0;
        $unidadesRep = [];

        while (($fila = fgetcsv($archivo, 1000, ',')) !== false) {
            $contadorFila++;

            if ($contadorFila <= 1) {
                continue;
            }

            try {
                $unidad = $fila[0]; // Columna de unidad
                $hora = str_replace(' ', '', $fila[1]);
            } catch (\Throwable $th) {
                $error = $th->getMessage();
            }

            // Extraer los valores "Min" a partir de la columna 5, con un salto de 3 columnas
            $minutos = [];
            for ($i = 4; $i < count($fila); $i += 3) {
                $minutos[] = $fila[$i];
            }

            // 🔥 **Eliminar la primera y la última sanción (para que coincida con las geocercas eliminadas)**
            if (!empty($minutos)) {
                array_shift($minutos); // Elimina el primer valor de sanción
                array_pop($minutos);   // Elimina el último valor de sanción
            }

            if (!isset($unidades[$unidad])) {
                $unidades[$unidad] = 1;
            }

            $sanciones = array_map(function ($min) {
                return preg_match('/^-\d+$/', $min) ? 1 : 0;
            }, $minutos);

            $totalSanciones = array_sum($sanciones);
            $valorTotal = $totalSanciones * (0.25 * $unidades[$unidad]);

            $unidadesRep[] = $unidad;

            $contadorVueltas = 0;
            foreach ($unidadesRep as $data) {
                if ($data === $unidad) {
                    $contadorVueltas++;
                }
            }

            // Insertar en tabla sanciones
            $sancionId = DB::table('sanciones')->insertGetId([
                'unidad' => $unidad,
                'vuelta' => $contadorVueltas,
                'hora' => $hora,
                'total' => $totalSanciones,
                'valor_total' => $valorTotal,
                'created_at' => now(),
                'updated_at' => now(),
                'fecha' => $fecha,
                'ruta' => $ruta
            ]);

            // Insertar sanciones de geocercas (EXCLUYENDO la primera y última)
            foreach ($geocercas as $index => $nombreGeocerca) {
                DB::table('geocercas')->insert([
                    'sancion_id' => $sancionId,
                    'nombre' => $nombreGeocerca,
                    'sancion' => $sanciones[$index] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($totalSanciones > 0) {
                $unidades[$unidad]++;
            }
        }

        DB::commit();

        fclose($archivo);

        return redirect()->route('sanciones.index', ['parametro' => $ruta])->with('success', 'Datos cargados correctamente.');
    }

    public function truncateTable()
    {
        $tableName = 'sanciones';
        $tableDos = 'geocercas';
        try {
            // Desactiva temporalmente la protección de claves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Ejecuta el TRUNCATE TABLE
            DB::table($tableName)->truncate();
            DB::table($tableDos)->truncate();


            // Reactiva la protección de claves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return redirect()->route('sanciones.index', ['parametro' => 'S-N']);
        } catch (\Exception $e) {
            return response()->json(['error' => "Error al truncar la tabla $tableName: " . $e->getMessage()], 500);
        }
    }
    public function generarReporte(Request $request)
    {
        $datosSeleccionados = json_decode($request->input('datosSeleccionados'), true);
        $geocercasActivas = json_decode($request->input('geocercasActivas'), true);

        if (!$datosSeleccionados || empty($datosSeleccionados)) {
            return back()->withErrors(['error' => 'No hay datos seleccionados para generar el reporte']);
        }

        usort($datosSeleccionados, function ($a, $b) {
            if ($a['unidad'] === $b['unidad']) {
                return $a['vuelta'] <=> $b['vuelta'];
            }
            return $a['unidad'] <=> $b['unidad'];
        });

        $todasLasGeocercas = [];
        if (!empty($datosSeleccionados)) {
            $todasLasGeocercas = array_keys($datosSeleccionados[0]['geocercas'] ?? []);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $header = ['N Vuelta', 'Unidad', 'Hora salida'];
        foreach ($todasLasGeocercas as $geocerca) {
            $header[] = $geocerca;
        }
        $header[] = 'Total';
        $header[] = 'Valor Total';

        $sheet->fromArray($header, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '007BFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'textRotation' => 90],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        foreach (range('D', $sheet->getHighestColumn()) as $column) {
            $sheet->getColumnDimension($column)->setWidth(5);
        }
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $row = 2;
        foreach ($datosSeleccionados as $dato) {
            $fila = [
                $dato['vuelta'],
                $dato['unidad'],
                $dato['hora'],
            ];

            foreach ($todasLasGeocercas as $geocerca) {
                $fila[] = $dato['geocercas'][$geocerca] ?? 0;
            }

            $fila[] = $dato['total'];
            $valorTotal = str_replace(['$', ','], '', $dato['valor_total']);
            $valorTotal = is_numeric($valorTotal) ? floatval($valorTotal) : 0.00;
            $fila[] = '$' . number_format($valorTotal, 2, '.', ',');

            $sheet->fromArray($fila, null, "A$row");
            // ✅ Cambiar color según si está seleccionada o no
            if (!empty($dato['seleccionado']) && $dato['seleccionado'] === true) {
                $colorFondo = 'FFFFFF'; // Azul claro
            } else {
                $colorFondo = 'CB2400'; // Gris claro por ejemplo
            }

            $sheet->getStyle("A$row:" . $sheet->getHighestColumn() . "$row")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $colorFondo]
                ]
            ]);

            $sheet->getStyle("A$row:" . $sheet->getHighestColumn() . "$row")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            $row++;
        }

        $ultimaFila = $sheet->getHighestRow();
        $colIndex = 4;
        foreach ($todasLasGeocercas as $geocerca) {
            if (!in_array($geocerca, $geocercasActivas)) {
                $colLetra = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getStyle($colLetra . '2:' . $colLetra . $ultimaFila)->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFFF00');
            }
            $colIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = date("Y-m-d") . '_Reporte_Sanciones.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

}
