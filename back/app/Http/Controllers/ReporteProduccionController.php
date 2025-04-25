<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HojaTrabajo;
use App\Models\ProduccionUsuario;
use App\Models\Ruta;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
class ReporteProduccionController extends Controller
{
    public function index(Request $request)
    {

        $user = auth()->user();

        $query = HojaTrabajo::with('unidad', 'ruta')
            ->whereHas('ruta', function ($q) use ($user) {
                $q->where('EMP_ID', $user->EMP_ID);
            });

        //  $query = HojaTrabajo::with('unidad', 'ruta');

        if ($request->filled('fecha')) {
            $query->where('fecha', $request->fecha);
        }

        if ($request->filled('ruta')) {
            $query->whereHas('ruta', function ($q) use ($request) {
                $q->where('descripcion', 'like', '%' . $request->ruta . '%');
            });
        }

        if ($request->filled('unidad')) {
            $query->whereHas('unidad', function ($q) use ($request) {
                $q->where('placa', 'like', '%' . $request->unidad . '%')
                    ->orWhere('numero_habilitacion', 'like', '%' . $request->unidad . '%');
            });
        }

        // Ordenar por fecha descendente y por número de habilitación ascendente
        $hojas = $query->get()->sortBy([
            ['fecha', 'desc'],
            [
                function ($hoja) {
                    if ($hoja->unidad && $hoja->unidad->numero_habilitacion) {
                        preg_match('/^(\d+)/', $hoja->unidad->numero_habilitacion, $matches);
                        return intval($matches[1] ?? PHP_INT_MAX);
                    }
                    return PHP_INT_MAX;
                }
            ]
        ]);

        $rutas = Ruta::where('EMP_ID', $user->EMP_ID)->get();

        return view('reportes.index', compact('hojas', 'rutas'));
    }



    public function create($id)
    {

        $user = Auth::user();
        $permisoLectura = $user->permisos()->where('DESCRIPCION', 'LECTURA')->exists();

        $hoja = HojaTrabajo::with('unidad', 'ruta')->findOrFail($id);

        // Obtener vueltas que ya registró este usuario para esta hoja
        $registros = ProduccionUsuario::where('id_hoja', $id)
            ->where('usu_id', Auth::user()->USU_ID)
            ->orderBy('nro_vuelta')
            ->get();

        // Si hay registros, obtener el último número de vuelta registrado, sino inicia desde 1
        $ultimoNumeroVuelta = $registros->max('nro_vuelta') ?? 0;
        $contador = $ultimoNumeroVuelta + 1; // Este es el próximo número de vuelta disponible

        return view('reportes.create', compact('hoja', 'registros', 'contador', 'permisoLectura'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_hoja' => 'required|exists:hojas_trabajo,id_hoja',
            'reportes' => 'required|array|min:1',
            'reportes.*.nro_vuelta' => 'required|integer|min:1',
            'reportes.*.pasaje_completo' => 'required|integer|min:0',
            'reportes.*.pasaje_medio' => 'required|integer|min:0',
        ]);

        foreach ($request->reportes as $reporte) {
            $valor = ($reporte['pasaje_completo'] * 0.35) + ($reporte['pasaje_medio'] * 0.17);

            ProduccionUsuario::updateOrCreate(
                [
                    'id_hoja' => $request->id_hoja,
                    'nro_vuelta' => $reporte['nro_vuelta'],
                    'usu_id' => Auth::user()->USU_ID
                ],
                [
                    'pasaje_completo' => $reporte['pasaje_completo'],
                    'pasaje_medio' => $reporte['pasaje_medio'],
                    'valor_vuelta' => $valor
                ]
            );
        }


        return redirect()->route('reportes.index')->with('success', 'Reporte guardado con éxito.');
    }

    public function generarReporteGlobal(Request $request)
    {
        $user = auth()->user();

        $fecha = $request->input('fecha');
        $rutaId = $request->input('ruta');

        $query = HojaTrabajo::with(['unidad', 'producciones', 'ruta'])
            ->whereDate('fecha', $fecha);
        $query->whereHas('ruta', function ($q) use ($user) {
            $q->where('EMP_ID', $user->EMP_ID);
        });

        if ($rutaId) {
            $query->where('id_ruta', $rutaId);
        }

        $hojas = $query->get();

        $produccionPorUnidad = [];
        $totalGlobal = 0;
        $totalVueltasGlobal = 0;

        foreach ($hojas as $hoja) {
            $unidadKey = $hoja->unidad->placa . ' (' . $hoja->unidad->numero_habilitacion . ')';

            $totalUnidad = 0;
            $totalVueltas = 0;
            $ultimaVuelta = 0;

            foreach ($hoja->producciones as $produccion) {
                $totalUnidad += $produccion->valor_vuelta;
                $totalVueltas++;
                $totalVueltasGlobal++;

                if ($produccion->nro_vuelta > $ultimaVuelta) {
                    $ultimaVuelta = $produccion->nro_vuelta;
                }
            }

            if (!isset($produccionPorUnidad[$unidadKey])) {
                $produccionPorUnidad[$unidadKey] = [
                    'total_produccion' => 0,
                    'total_vueltas' => 0,
                    'ultima_vuelta' => 0
                ];
            }

            $produccionPorUnidad[$unidadKey]['total_produccion'] += $totalUnidad;
            $produccionPorUnidad[$unidadKey]['total_vueltas'] += $totalVueltas;
            $produccionPorUnidad[$unidadKey]['ultima_vuelta'] = $ultimaVuelta;

            $totalGlobal += $totalUnidad;
        }
        // Guardar la consulta en sesión para generar PDF o Excel posteriormente
        session(['reporte_global_data' => $hojas]);

        $result = view('partials.reporte_global', compact('produccionPorUnidad', 'totalGlobal', 'totalVueltasGlobal'))->render();

        return response()->json(['html' => $result]);
    }



    public function generarExcel()
    {
        $hojas = Session::get('reporte_global_data'); // Obtenemos los datos filtrados desde la sesión

        if (!$hojas) {
            return redirect()->back()->with('error', 'No hay datos para exportar.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte Global');

        // Encabezados
        $headers = ['Fecha', 'Unidad (Placa - Habilitación)', 'Ruta', 'Tipo Día', 'Vueltas', 'Producción ($)'];
        $sheet->fromArray($headers, null, 'A1');

        // Aplicar estilo a los encabezados
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCCCCC');
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        $totalGlobal = 0;

        foreach ($hojas as $hoja) {
            $produccionTotal = 0;
            $vueltas = 0;

            if ($hoja->producciones) {
                foreach ($hoja->producciones as $produccion) {
                    $produccionTotal += $produccion->valor_vuelta;
                    $vueltas++;
                }
            }

            // Concatenar Placa y Habilitación correctamente
            $unidad = ($hoja->unidad->placa ?? '-') . ' (' . ($hoja->unidad->numero_habilitacion ?? '-') . ')';

            $sheet->setCellValue('A' . $row, $hoja->fecha);
            $sheet->setCellValue('B' . $row, $unidad);
            $sheet->setCellValue('C' . $row, $hoja->ruta->descripcion ?? '-');
            $sheet->setCellValue('D' . $row, $hoja->tipo_dia ?? '-');
            $sheet->setCellValue('E' . $row, $vueltas);
            $sheet->setCellValue('F' . $row, $produccionTotal);

            $totalGlobal += $produccionTotal;
            $row++;
        }

        // Total de Producción en la última fila
        $sheet->setCellValue('E' . $row, 'Total');
        $sheet->setCellValue('F' . $row, $totalGlobal);

        // Estilo para el total
        $sheet->getStyle('E' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('E' . $row . ':F' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFCCFFCC');

        // Bordes para toda la tabla
        $sheet->getStyle('A1:F' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Alineación al centro para columnas específicas
        $sheet->getStyle('A1:F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F1:F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Ajustar tamaño de columnas automáticamente
        foreach (range('A', 'F') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Establecer el nombre del archivo
        $filename = 'reporte_recaudo.xlsx';

        // Configuración de cabeceras HTTP para la descarga
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }



    public function generarPDF()
    {
        $hojas = Session::get('reporte_global_data'); // Obtenemos los datos filtrados desde la sesión

        if (!$hojas) {
            return redirect()->back()->with('error', 'No hay datos para exportar.');
        }

        $totalGlobal = 0;
        $datos = [];

        foreach ($hojas as $hoja) {
            $produccionTotal = 0;
            $vueltas = 0;

            if ($hoja->producciones) {
                foreach ($hoja->producciones as $produccion) {
                    $produccionTotal += $produccion->valor_vuelta;
                    $vueltas++;
                }
            }

            $unidad = ($hoja->unidad->placa ?? '-') . ' (' . ($hoja->unidad->numero_habilitacion ?? '-') . ')';

            $datos[] = [
                'fecha' => $hoja->fecha,
                'unidad' => $unidad,
                'ruta' => $hoja->ruta->descripcion ?? '-',
                'tipo_dia' => $hoja->tipo_dia ?? '-',
                'vueltas' => $vueltas,
                'produccion' => $produccionTotal
            ];

            $totalGlobal += $produccionTotal;
        }

        $pdf = new Dompdf();
        $pdf->setPaper('A4', 'landscape');

        $view = View::make('pdf.reporte_global_pdf', compact('datos', 'totalGlobal'))->render();
        $pdf->loadHtml($view);

        $pdf->render();
        return $pdf->stream('reporte_recaudo.pdf');
    }

}
