<?php

namespace App\Http\Controllers;

use App\Models\HojaTrabajo;
use App\Models\Gasto;
use App\Models\Produccion;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class HojaTrabajoController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'tipo_dia' => 'required|in:LABORABLE,FERIADO,SABADO,DOMINGO',
            'id_conductor' => 'required|exists:personal,id_personal',
            'id_ayudante' => 'required|exists:personal,id_personal',
            'id_ruta' => 'required|exists:rutas,id_ruta',
            'id_unidad' => 'required|exists:unidades,id_unidad',
            'gastos' => 'array',
            'produccion' => 'array'
        ]);

        $hoja = HojaTrabajo::create($request->only([
            'fecha', 'tipo_dia', 'id_conductor', 'id_ayudante', 'id_ruta', 'id_unidad'
        ]));

        foreach ($request->gastos as $gasto) {
            Gasto::create([
                'id_hoja' => $hoja->id_hoja,
                'tipo_gasto' => $gasto['tipo_gasto'],
                'valor' => $gasto['valor'],
            ]);
        }

        foreach ($request->produccion as $vuelta) {
            Produccion::create([
                'id_hoja' => $hoja->id_hoja,
                'nro_vuelta' => $vuelta['nro_vuelta'],
                'hora_subida' => $vuelta['hora_subida'],
                'valor_subida' => $vuelta['valor_subida'],
                'hora_bajada' => $vuelta['hora_bajada'],
                'valor_bajada' => $vuelta['valor_bajada'],
            ]);
        }

        return response()->json(['message' => 'Hoja de trabajo creada', 'id' => $hoja->id_hoja]);
    }

    public function generarPDF($id)
    {
        $user = Auth::user(); // gracias a Sanctum
        $hoja = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'ayudante', 'gastos', 'producciones'])->findOrFail($id);

        try {
            // Renderizamos el contenido HTML de la vista
            $html = view('pdf.hoja_trabajo', compact('hoja', 'user'))->render();
    
            // Configuramos las opciones de DomPDF
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('chroot', public_path()); // Para evitar el error "Cannot resolve public path"
    
            // Instanciamos DomPDF
            $pdf = new Dompdf($options);
            $pdf->loadHtml($html);
            $pdf->setPaper('A4');
            $pdf->render();
    
            // Ruta donde se guarda el PDF generado
            $pdfPath = storage_path('app/public/pdf/hoja_trabajo_'.$id.'.pdf');
    
            file_put_contents($pdfPath, $pdf->output());
    
            // Puedes devolver el PDF directamente si quieres:
            return response()->download($pdfPath);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error generando PDF: ' . $e->getMessage()
            ], 500);
        }

        return $pdf->download('hoja_trabajo_'.$id.'.pdf');
    }
}
