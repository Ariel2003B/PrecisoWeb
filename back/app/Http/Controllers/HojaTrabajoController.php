<?php

namespace App\Http\Controllers;

use App\Models\HojaTrabajo;
use App\Models\Gasto;
use App\Models\Produccion;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
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
            'ayudante_nombre' => 'required|string|max:100',
            'id_ruta' => 'required|exists:rutas,id_ruta',
            'id_unidad' => 'required|exists:unidades,id_unidad',
            'gastos' => 'array',
            'produccion' => 'array'
        ]);

        // Crear hoja de trabajo con nombre de ayudante tipeado
        $hoja = HojaTrabajo::create([
            'fecha' => $request->fecha,
            'tipo_dia' => $request->tipo_dia,
            'id_conductor' => $request->id_conductor,
            'id_ruta' => $request->id_ruta,
            'id_unidad' => $request->id_unidad,
            'ayudante_nombre' => $request->ayudante_nombre
        ]);

        // Crear gastos
        foreach ($request->gastos as $gasto) {
            $rutaImagen = null;

            // Solo si el tipo es DIESEL u OTROS y hay imagen_base64
            if (in_array($gasto['tipo_gasto'], ['DIESEL', 'OTROS']) && !empty($gasto['imagen_base64'])) {
                $base64 = $gasto['imagen_base64'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                    $imageData = base64_decode(substr($base64, strpos($base64, ',') + 1));
                    $extension = strtolower($type[1]); // jpg, png, etc.
                    $imageName = 'gasto_' . uniqid() . '.' . $extension;
                    $gastosPath = storage_path('app/public/gastos');
                    if (!file_exists($gastosPath)) {
                        mkdir($gastosPath, 0777, true); // crea la carpeta con permisos recursivos
                    }
                    $savePath = storage_path('app/public/gastos/' . $imageName);
                    file_put_contents($savePath, $imageData);
                    $rutaImagen = 'gastos/' . $imageName;
                }
            }

            Gasto::create([
                'id_hoja' => $hoja->id_hoja,
                'tipo_gasto' => $gasto['tipo_gasto'],
                'valor' => $gasto['valor'],
                'imagen' => $rutaImagen
            ]);
        }

        // Crear producción
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

    public function index(Request $request)
    {
        $query = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'gastos', 'producciones']);

        if ($request->has('fecha')) {
            $query->where('fecha', $request->input('fecha'));
        }
        return response()->json($query->get());
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'tipo_dia' => 'required|in:LABORABLE,FERIADO,SABADO,DOMINGO',
            'id_conductor' => 'required|exists:personal,id_personal',
            'ayudante_nombre' => 'required|string|max:100',
            'id_ruta' => 'required|exists:rutas,id_ruta',
            'id_unidad' => 'required|exists:unidades,id_unidad',
            'gastos' => 'array',
            'produccion' => 'array'
        ]);

        $hoja = HojaTrabajo::findOrFail($id);
        $hoja->update([
            'fecha' => $request->fecha,
            'tipo_dia' => $request->tipo_dia,
            'id_conductor' => $request->id_conductor,
            'ayudante_nombre' => $request->ayudante_nombre,
            'id_ruta' => $request->id_ruta,
            'id_unidad' => $request->id_unidad,
        ]);

        // Limpiar y volver a insertar gastos y producción
        $hoja->gastos()->delete();
        $hoja->producciones()->delete();

        foreach ($request->gastos as $gasto) {
            $hoja->gastos()->create([
                'tipo_gasto' => $gasto['tipo_gasto'],
                'valor' => $gasto['valor'],
                'imagen' => $gasto['imagen'] ?? null
            ]);
        }

        foreach ($request->produccion as $prod) {
            $hoja->producciones()->create([
                'nro_vuelta' => $prod['nro_vuelta'],
                'hora_subida' => $prod['hora_subida'],
                'valor_subida' => $prod['valor_subida'],
                'hora_bajada' => $prod['hora_bajada'],
                'valor_bajada' => $prod['valor_bajada']
            ]);
        }

        return response()->json(['message' => 'Hoja de trabajo actualizada']);
    }


    public function destroy($id)
    {
        $hoja = HojaTrabajo::findOrFail($id);
        $hoja->gastos()->delete();
        $hoja->producciones()->delete();
        $hoja->delete();

        return response()->json(['message' => 'Hoja de trabajo eliminada']);
    }

    public function generarPDF($id)
    {
        $user = Auth::user(); // gracias a Sanctum
        $hoja = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'ayudante', 'gastos', 'producciones'])->findOrFail($id);
        // dd($hoja);
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
            $pdfPath = storage_path('app/public/pdf/hoja_trabajo_' . $id . '.pdf');

            file_put_contents($pdfPath, $pdf->output());

            // Puedes devolver el PDF directamente si quieres:
            return response()->download($pdfPath);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error generando PDF: ' . $e->getMessage()
            ], 500);
        }

        return $pdf->download('hoja_trabajo_' . $id . '.pdf');
    }





}
