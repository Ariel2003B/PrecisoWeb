<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use Hamcrest\Core\IsNull;
use Illuminate\Support\Facades\DB;
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

    //     // Extraer geocercas de los encabezados
    //     $geocercas = array_filter($encabezados, function ($columna) {
    //         return preg_match('/^\d+\.\s+.+$/u', $columna);
    //     });



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

    //         // $datos[] = [
    //         //     'vuelta' => $unidades[$unidad],
    //         //     'unidad' => $unidad,
    //         //     'placa' => $placa,
    //         //     'sanciones' => $sanciones,
    //         //     'total' => $totalSanciones,
    //         //     'valor_total' => $valorTotal,
    //         // ];
    //         $datos[] = [
    //             'vuelta' => $contadorVueltas,
    //             'unidad' => $unidad,
    //             'hora' => $hora,
    //             'sanciones' => $sanciones,
    //             'total' => $totalSanciones,
    //             'valor_total' => $valorTotal,
    //         ];

    //         if ($totalSanciones > 0) {
    //             $unidades[$unidad]++;
    //         }
    //     }

    //     fclose($archivo);

    //     return view('sanciones.sanciones', compact('datos', 'geocercas'));
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
        $encabezados = array_slice($encabezados, 2);


        // Extraer geocercas de los encabezados
        $geocercas = array_filter($encabezados, function ($columna) {
            return preg_match('/^\d+\.\s+.+$/u', $columna);
        });
        // Reindexar y ordenar las geocercas
        $geocercasOrdenadas = [];
        foreach ($geocercas as $columna) {
            // Extraer el índice inicial del encabezado (ejemplo: "1. Carapungo" => 1)
            preg_match('/^(\d+)\./', $columna, $matches);
            $indice = isset($matches[1]) ? (int) $matches[1] : PHP_INT_MAX;

            // Asignar a un array temporal con el índice extraído como clave
            $geocercasOrdenadas[$indice] = $columna;
        }

        // Ordenar por clave numérica (índice)
        ksort($geocercasOrdenadas);

        // Reindexar desde 0
        $geocercas = array_values($geocercasOrdenadas);



        $datos = [];
        $unidades = [];
        $contadorFila = 0; // Contador para identificar las filas
        $unidadesRep = [];
        while (($fila = fgetcsv($archivo, 1000, ',')) !== false) {
            $contadorFila++;

            // Omitir las primeras filas, incluyendo encabezados
            if ($contadorFila <= 1) {
                continue;
            }
            try {
                $unidad = $fila[0]; // Columna de unidad
                $time = $fila[1];
                $hora = str_replace(' ', '', $time);
            } catch (\Throwable $th) {
                $error = $th->getMessage();
            }

            // Extraer los valores "Min" a partir de la columna 5, con un salto de 3 columnas
            $minutos = [];
            for ($i = 4; $i < count($fila); $i += 3) {
                $minutos[] = $fila[$i];
            }

            if (!isset($unidades[$unidad])) {
                $unidades[$unidad] = 1; // Primera vuelta
            }

            $sanciones = array_map(function ($min) {
                // Validar si empieza con '-' seguido por un número
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

            // $datos[] = [
            //     'vuelta' => $unidades[$unidad],
            //     'unidad' => $unidad,
            //     'placa' => $placa,
            //     'sanciones' => $sanciones,
            //     'total' => $totalSanciones,
            //     'valor_total' => $valorTotal,
            // ];

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

            // Insertar sanciones de geocercas
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

        return redirect()->route('sanciones.index',['parametro' => $ruta])->with('success', 'Datos cargados correctamente.');
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

            return redirect()->route('sanciones.index',['parametro'=>'S-N']);
        } catch (\Exception $e) {
            return response()->json(['error' => "Error al truncar la tabla $tableName: " . $e->getMessage()], 500);
        }
    }
    public function generarReporte(Request $request)
    {
        $datosSeleccionados = json_decode($request->input('datosSeleccionados'), true);

        if (!$datosSeleccionados || empty($datosSeleccionados)) {
            return back()->withErrors(['error' => 'No hay datos seleccionados para generar el reporte']);
        }

        // Ordenar los datos seleccionados por unidad
        usort($datosSeleccionados, function ($a, $b) {
            return $a['unidad'] <=> $b['unidad']; // Orden ascendente por la clave 'unidad'
        });

        // Obtener todos los nombres de geocercas globalmente
        $geocercas = [];
        if (!empty($datosSeleccionados)) {
            $geocercas = array_keys($datosSeleccionados[0]['geocercas'] ?? []);
        }

        // Crear un nuevo archivo de Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Escribir el encabezado de la tabla
        $header = ['N Vuelta', 'Unidad', 'Hora salida'];
        foreach ($geocercas as $geocerca) {
            $header[] = $geocerca;
        }
        $header[] = 'Total';
        $header[] = 'Valor Total';

        // Estilos para el encabezado
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007BFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'textRotation' => 90, // Girar texto a vertical
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // Aplicar el encabezado y estilos
        $sheet->fromArray($header, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

        // Ajustar ancho de las columnas de las geocercas
        foreach (range('D', $sheet->getHighestColumn()) as $column) { // Cambia 'C' a 'D' porque "Placa" ahora es la tercera columna
            $sheet->getColumnDimension($column)->setWidth(5); // Reducir espacio de las columnas solo para las geocercas
        }

        // Ajustar el ancho automático para las primeras columnas
        $sheet->getColumnDimension('A')->setAutoSize(true); // N Vuelta
        $sheet->getColumnDimension('B')->setAutoSize(true); // Unidad
        $sheet->getColumnDimension('C')->setAutoSize(true); // Placa

        // Escribir los datos seleccionados en las filas
        $row = 2; // Comienza en la segunda fila después del encabezado
        foreach ($datosSeleccionados as $dato) {
            $fila = [
                $dato['vuelta'],
                $dato['unidad'],
                $dato['hora']
            ];

            // Añadir las sanciones de geocercas
            foreach ($geocercas as $geocerca) {
                $fila[] = $dato['geocercas'][$geocerca] ?? 0;
            }

            $fila[] = $dato['total'];
            $fila[] = $dato['valor_total'];

            $sheet->fromArray($fila, null, "A$row");

            // Aplicar bordes a las filas de datos
            $sheet->getStyle("A$row:" . $sheet->getHighestColumn() . "$row")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Crear el archivo Excel
        $writer = new Xlsx($spreadsheet);
        $hoy = date("Y-m-d"); // Obtiene solo la fecha en el formato Año-Mes-Día
        $fileName = $hoy . '_Reporte_Sanciones.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }


}
