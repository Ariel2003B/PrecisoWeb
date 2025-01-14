<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class SancionesController extends Controller
{
    public function index()
    {
        return view('sanciones.sanciones');
    }

    public function cargarCSV(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:csv,txt',
        ]);

        $rutaArchivo = $request->file('archivo')->store('temp');
        $rutaCompleta = storage_path('app/' . $rutaArchivo);

        // Leer el archivo CSV
        $archivo = fopen($rutaCompleta, 'r');
        $encabezados = fgetcsv($archivo, 1000, ';'); // Leer la primera fila como encabezados

        // Extraer geocercas de los encabezados
        $geocercas = array_filter($encabezados, function ($columna) {
            return preg_match('/\d+\.\s+(.*)/u', $columna);
        });

        $datos = [];
        $unidades = [];
        $contadorFila = 0; // Contador para identificar las filas
        $unidadesRep = [];
        while (($fila = fgetcsv($archivo, 1000, ';')) !== false) {
            $contadorFila++;

            // Omitir las primeras filas, incluyendo encabezados
            if ($contadorFila <= 1) {
                continue;
            }

            $unidad = $fila[0]; // Columna de unidad
            $placa = $fila[1];  // Columna de placa

            // Extraer los valores "Min" a partir de la columna 5, con un salto de 3 columnas
            $minutos = [];
            for ($i = 5; $i < count($fila); $i += 3) {
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
                if ($data===$unidad){
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
            $datos[] = [
                'vuelta' => $contadorVueltas,
                'unidad' => $unidad,
                'placa' => $placa,
                'sanciones' => $sanciones,
                'total' => $totalSanciones,
                'valor_total' => $valorTotal,
            ];

            if ($totalSanciones > 0) {
                $unidades[$unidad]++;
            }
        }

        fclose($archivo);

        return view('sanciones.sanciones', compact('datos', 'geocercas'));
    }

    // public function generarReporte(Request $request)
    // {
    //     $datosSeleccionados = json_decode($request->input('datosSeleccionados'), true);

    //     if (!$datosSeleccionados || empty($datosSeleccionados)) {
    //         return back()->withErrors(['error' => 'No hay datos seleccionados para generar el reporte']);
    //     }

    //     // Obtener todos los nombres de geocercas globalmente
    //     $geocercas = [];
    //     if (!empty($datosSeleccionados)) {
    //         $geocercas = array_keys($datosSeleccionados[0]['geocercas'] ?? []);
    //     }

    //     $options = new Options();
    //     $options->set('isHtml5ParserEnabled', true);
    //     $options->set('isRemoteEnabled', true); // Permitir imágenes externas
    //     $dompdf = new Dompdf($options);

    //     // Generar el HTML para el PDF
    //     $html = view('sanciones.pdf', compact('datosSeleccionados', 'geocercas'))->render();

    //     try {
    //         $dompdf->loadHtml($html);
    //         $dompdf->setPaper('A4', 'landscape');
    //         $dompdf->render();
    //     } catch (\Throwable $th) {
    //         return back()->withErrors(['error' => $th->getMessage()]);
    //     }

    //     return response($dompdf->output(), 200)
    //         ->header('Content-Type', 'application/pdf')
    //         ->header('Content-Disposition', 'attachment; filename="Reporte_Sanciones.pdf"');
    // }



    public function generarReporte(Request $request)
    {
        $datosSeleccionados = json_decode($request->input('datosSeleccionados'), true);

        if (!$datosSeleccionados || empty($datosSeleccionados)) {
            return back()->withErrors(['error' => 'No hay datos seleccionados para generar el reporte']);
        }

        // Obtener todos los nombres de geocercas globalmente
        $geocercas = [];
        if (!empty($datosSeleccionados)) {
            $geocercas = array_keys($datosSeleccionados[0]['geocercas'] ?? []);
        }

        // Crear un nuevo archivo de Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Escribir el encabezado de la tabla
        $header = ['Unidad', 'Placa'];
        foreach ($geocercas as $geocerca) {
            $header[] = $geocerca;
        }
        $header[] = 'Total';
        $header[] = 'Valor Total';

        $sheet->fromArray($header, null, 'A1');

        // Escribir los datos seleccionados en las filas
        $row = 2; // Comienza en la segunda fila después del encabezado
        foreach ($datosSeleccionados as $dato) {
            $fila = [
                $dato['unidad'],
                $dato['placa']
            ];

            // Añadir las sanciones de geocercas
            foreach ($geocercas as $geocerca) {
                $fila[] = $dato['geocercas'][$geocerca] ?? 0;
            }

            $fila[] = $dato['total'];
            $fila[] = $dato['valor_total'];

            $sheet->fromArray($fila, null, "A$row");
            $row++;
        }

        // Crear el archivo Excel
        $writer = new Xlsx($spreadsheet);

        // Configurar la respuesta para descargar el archivo
        $fileName = 'Reporte_Sanciones.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

}
