<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Session;

function generarExcel()
{
    $hojas = Session::get('reporte_global_data'); // Obtenemos los datos filtrados desde la sesión

    if (!$hojas) {
        return redirect()->back()->with('error', 'No hay datos para exportar.');
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados
    $sheet->setCellValue('A1', 'Fecha');
    $sheet->setCellValue('B1', 'Placa');
    $sheet->setCellValue('C1', 'Número Habilitación');
    $sheet->setCellValue('D1', 'Ruta');
    $sheet->setCellValue('E1', 'Tipo Día');

    // Agregar datos
    $row = 2;
    foreach ($hojas as $hoja) {
        $sheet->setCellValue('A' . $row, $hoja->fecha);
        $sheet->setCellValue('B' . $row, $hoja->unidad->placa ?? '-');
        $sheet->setCellValue('C' . $row, $hoja->unidad->numero_habilitacion ?? '-');
        $sheet->setCellValue('D' . $row, $hoja->ruta->descripcion ?? '-');
        $sheet->setCellValue('E' . $row, $hoja->tipo_dia ?? '-');
        $row++;
    }

    // Establecer el nombre del archivo
    $filename = 'reporte_global.xlsx';

    // Configuración de cabeceras HTTP para la descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

