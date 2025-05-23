<?php

namespace App\Http\Controllers;

use App\Models\HojaTrabajo;
use App\Models\Gasto;
use App\Models\Produccion;
use App\Models\ProduccionUsuario;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use PDF;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\File;
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
                'hora_bajada' => $vuelta['hora_bajada'],
                'valor_vuelta' => $vuelta['valor_vuelta'],

            ]);
        }

        return response()->json(['message' => 'Hoja de trabajo creada', 'id' => $hoja->id_hoja]);
    }

    public function index(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $token = substr($authHeader, 7);
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        $user = $accessToken->tokenable; // Usuario autenticado

        // Consultar el perfil si lo tienes relacionado (puedes ajustar esto según tu modelo)
        $esAdmin = optional($user->p_e_r_f_i_l)->DESCRIPCION === 'admin';

        $query = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'gastos', 'producciones']);

        // Si no es admin, filtra por unidades del usuario
        // if (!$esAdmin) {
        //     $query->whereHas('unidad', function ($q) use ($user) {
        //         $q->where('usu_id', $user->USU_ID);
        //     });
        // }
        if ($esAdmin) {
            // Solo hojas donde la ruta pertenece a su empresa
            $query->whereHas('ruta', function ($q) use ($user) {
                $q->where('EMP_ID', $user->EMP_ID);
            });
        } else {
            // Hojas asociadas a sus propias unidades
            $query->whereHas('unidad', function ($q) use ($user) {
                $q->where('usu_id', $user->USU_ID);
            });
        }

        // Filtro opcional por fecha
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
            'id_ruta' => 'required|exists:rutas,id_ruta',
            'id_unidad' => 'required|exists:unidades,id_unidad',
            'gastos' => 'array'
        ]);

        $hoja = HojaTrabajo::with(['gastos', 'producciones'])->findOrFail($id);

        // Actualizar la hoja
        $hoja->update([
            'fecha' => $request->fecha,
            'tipo_dia' => $request->tipo_dia,
            'id_conductor' => $request->id_conductor,
            'ayudante_nombre' => $request->ayudante_nombre,
            'id_ruta' => $request->id_ruta,
            'id_unidad' => $request->id_unidad,
        ]);

        // Actualizar o crear gastos
        foreach ($request->gastos as $gasto) {
            $rutaImagen = null;

            if (in_array($gasto['tipo_gasto'], ['DIESEL', 'OTROS']) && !empty($gasto['imagen_base64'])) {
                if (preg_match('/^data:image\/(\w+);base64,/', $gasto['imagen_base64'], $type)) {
                    $imageData = base64_decode(substr($gasto['imagen_base64'], strpos($gasto['imagen_base64'], ',') + 1));
                    $extension = strtolower($type[1]);
                    $imageName = 'gasto_' . uniqid() . '.' . $extension;
                    $gastosPath = storage_path('app/public/gastos');
                    if (!file_exists($gastosPath)) {
                        mkdir($gastosPath, 0777, true);
                    }
                    $savePath = $gastosPath . '/' . $imageName;
                    file_put_contents($savePath, $imageData);
                    $rutaImagen = 'gastos/' . $imageName;
                }
            }

            $hoja->gastos()->updateOrCreate(
                ['tipo_gasto' => $gasto['tipo_gasto']],
                [
                    'valor' => $gasto['valor'],
                    'imagen' => $rutaImagen ?? $hoja->gastos->firstWhere('tipo_gasto', $gasto['tipo_gasto'])?->imagen
                ]
            );
        }
        // Producción: actualizar si existe, crear si no
        if (!empty($request->produccion) && is_array($request->produccion)) { // Verificar que producción no esté vacío y sea un array
            foreach ($request->produccion as $prod) {
                if (isset($prod['nro_vuelta']) && isset($prod['hora_subida']) && isset($prod['hora_bajada']) && isset($prod['valor_vuelta'])) {
                    $hoja->producciones()->updateOrCreate(
                        ['nro_vuelta' => $prod['nro_vuelta']],
                        [
                            'hora_subida' => $prod['hora_subida'],
                            'hora_bajada' => $prod['hora_bajada'],
                            'valor_vuelta' => $prod['valor_vuelta'],
                        ]
                    );
                }
            }
        }



        return response()->json(['message' => 'Hoja de trabajo actualizada correctamente']);
    }

    public function destroy($id)
    {
        $hoja = HojaTrabajo::findOrFail($id);
        $hoja->gastos()->delete();
        $hoja->producciones()->delete();
        $hoja->delete();

        return response()->json(['message' => 'Hoja de trabajo eliminada']);
    }

    public function generarPDF($id, Request $request)
    {

        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        $token = substr($authHeader, 7); // quita "Bearer "
        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        $user = $accessToken->tokenable; // Este es el usuario


        //$user = Auth::user(); // gracias a Sanctum
        Log::info('Usuario autenticado para generar PDF', ['user_id' => $user->id ?? 'No autenticado']);

        try {
            $vueltasUsuario = ProduccionUsuario::with('usuario')
                ->where('id_hoja', $id)
                ->orderBy('nro_vuelta')
                ->get();

            $hoja = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'ayudante', 'gastos', 'producciones'])->findOrFail($id);
            $gastoDiesel = $hoja->gastos->firstWhere('tipo_gasto', 'DIESEL');
            $gastoOtros = $hoja->gastos->firstWhere('tipo_gasto', 'OTROS');

            // Construir la URL pública
            $baseUrl = "http://precisogps.com/back/storage/app/public/";
            $imagenDiesel = $gastoDiesel && $gastoDiesel->imagen ? $baseUrl . $gastoDiesel->imagen : null;
            $imagenOtros = $gastoOtros && $gastoOtros->imagen ? $baseUrl . $gastoOtros->imagen : null;


            $logoEmpresa = $hoja->ruta->empresa->IMAGEN
                ? $baseUrl . $hoja->ruta->empresa->IMAGEN
                : null;

            $nombreEmpresa = $hoja->ruta->empresa->NOMBRE ?? 'EMPRESA SIN NOMBRE';
            Log::info('Hoja de trabajo encontrada', ['id' => $id]);

            $html = view('pdf.hoja_trabajo', compact(
                'hoja',
                'user',
                'vueltasUsuario',
                'imagenDiesel',
                'imagenOtros',
                'logoEmpresa',
                'nombreEmpresa'
            ))->render();
            Log::info('Vista HTML renderizada correctamente.');

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('chroot', public_path());

            $pdf = new Dompdf($options);
            $pdf->loadHtml($html);
            $pdf->setPaper('A4');
            $pdf->render();
            Log::info('PDF renderizado correctamente.');

            $pdfPath = storage_path('app/public/pdf/hoja_trabajo_' . $id . '.pdf');
            file_put_contents($pdfPath, $pdf->output());
            Log::info('PDF guardado correctamente', ['path' => $pdfPath]);

            return response()->download($pdfPath);

        } catch (\Exception $e) {
            Log::error('Error al generar PDF', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error generando PDF: ' . $e->getMessage()
            ], 500);
        }
    }


    public function generarPDFWeb($id)
    {

        //$user = Auth::user(); // gracias a Sanctum
        Log::info('Usuario autenticado para generar PDF', ['user_id' => $user->id ?? 'No autenticado']);

        try {
            $vueltasUsuario = ProduccionUsuario::with('usuario')
                ->where('id_hoja', $id)
                ->orderBy('nro_vuelta')
                ->get();

            $hoja = HojaTrabajo::with(['unidad', 'ruta.empresa', 'conductor', 'ayudante', 'gastos', 'producciones'])->findOrFail($id);
            $gastoDiesel = $hoja->gastos->firstWhere('tipo_gasto', 'DIESEL');
            $gastoOtros = $hoja->gastos->firstWhere('tipo_gasto', 'OTROS');

            // Construir la URL pública
            $baseUrl = "http://precisogps.com/back/storage/app/public/";
            $imagenDiesel = $gastoDiesel && $gastoDiesel->imagen ? $baseUrl . $gastoDiesel->imagen : null;
            $imagenOtros = $gastoOtros && $gastoOtros->imagen ? $baseUrl . $gastoOtros->imagen : null;

            $logoEmpresa = $hoja->ruta->empresa->IMAGEN
                ? $baseUrl . $hoja->ruta->empresa->IMAGEN
                : null;

            $nombreEmpresa = $hoja->ruta->empresa->NOMBRE ?? 'EMPRESA SIN NOMBRE';

            Log::info('Hoja de trabajo encontrada', ['id' => $id]);

            $html = view('pdf.hoja_trabajo', compact(
                'hoja',
                'vueltasUsuario',
                'imagenDiesel',
                'imagenOtros',
                'logoEmpresa',
                'nombreEmpresa'
            ))->render();
            Log::info('Vista HTML renderizada correctamente.');

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $options->set('chroot', public_path());

            $pdf = new Dompdf($options);
            $pdf->loadHtml($html);
            $pdf->setPaper('A4');
            $pdf->render();
            Log::info('PDF renderizado correctamente.');

            $pdfPath = storage_path('app/public/pdf/hoja_trabajo_' . $id . '.pdf');
            file_put_contents($pdfPath, $pdf->output());
            Log::info('PDF guardado correctamente', ['path' => $pdfPath]);

            return response()->download($pdfPath);

        } catch (\Exception $e) {
            Log::error('Error al generar PDF', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error generando PDF: ' . $e->getMessage()
            ], 500);
        }
    }




    // public function ReportePorRango(Request $request)
    // {
    //     ini_set('memory_limit', '1024M'); // o más, como '1024M'
    //     ini_set('max_execution_time', 300); // 300 segundos = 5 minutos

    //     $request->validate([
    //         'fecha_inicio' => 'required|date',
    //         'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
    //     ]);

    //     $inicio = $request->fecha_inicio;
    //     $fin = $request->fecha_fin;
    //     $esUnSoloDia = $inicio === $fin;
    //     $hojas = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'gastos', 'producciones'])
    //         ->whereBetween('fecha', [$inicio, $fin])
    //         ->orderBy('fecha')
    //         ->orderBy('id_unidad')
    //         ->get();

    //     $htmlCompleto = '';

    //     foreach ($hojas as $hoja) {
    //         $vueltasUsuario = ProduccionUsuario::with('usuario')
    //             ->where('id_hoja', $hoja->id_hoja)
    //             ->orderBy('nro_vuelta')
    //             ->get();

    //         $gastoDiesel = $hoja->gastos->firstWhere('tipo_gasto', 'DIESEL');
    //         $gastoOtros = $hoja->gastos->firstWhere('tipo_gasto', 'OTROS');

    //         $baseUrl = "http://precisogps.com/back/storage/app/public/";
    //         $imagenDiesel = $gastoDiesel && $gastoDiesel->imagen ? $baseUrl . $gastoDiesel->imagen : null;
    //         $imagenOtros = $gastoOtros && $gastoOtros->imagen ? $baseUrl . $gastoOtros->imagen : null;

    //         // Renderiza UNA HOJA con su vista
    //         $vista = $esUnSoloDia ? 'pdf.hoja_trabajo' : 'pdf.hojaTrabajoBulk';

    //         $htmlHoja = view($vista, compact('hoja', 'vueltasUsuario', 'imagenDiesel', 'imagenOtros'))->render();

    //         // Separamos con salto de página
    //         $htmlCompleto .= '<div style="page-break-after: always;">' . $htmlHoja . '</div>';
    //     }

    //     $options = new Options();
    //     $options->set('isRemoteEnabled', true);
    //     $options->set('isHtml5ParserEnabled', true);

    //     $pdf = new Dompdf($options);
    //     $pdf->loadHtml($htmlCompleto);
    //     $pdf->setPaper('A4');
    //     $pdf->render();

    //     return $pdf->stream("reporte_rango_{$inicio}_{$fin}.pdf");
    // }


    public function ReportePorRango(Request $request)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $inicio = $request->fecha_inicio;
        $fin = $request->fecha_fin;
        $esUnSoloDia = $inicio === $fin;

        $user = auth()->user();  // <--- obtenemos el usuario logueado
        $empresaId = $user->EMP_ID;

        $hojas = HojaTrabajo::with(['unidad', 'ruta.empresa', 'conductor', 'ayudante', 'gastos', 'producciones'])
            ->whereBetween('fecha', [$inicio, $fin])
            ->whereHas('ruta', function ($query) use ($empresaId) {
                $query->where('EMP_ID', $empresaId);
            })
            ->orderBy('fecha')
            ->orderBy('id_unidad')
            ->get();

        $baseUrl = "http://precisogps.com/back/storage/app/public/";
        $htmlCompleto = '';

        foreach ($hojas as $hoja) {
            $vueltasUsuario = ProduccionUsuario::with('usuario')
                ->where('id_hoja', $hoja->id_hoja)
                ->orderBy('nro_vuelta')
                ->get();

            $gastoDiesel = $hoja->gastos->firstWhere('tipo_gasto', 'DIESEL');
            $gastoOtros = $hoja->gastos->firstWhere('tipo_gasto', 'OTROS');

            $imagenDiesel = $gastoDiesel && $gastoDiesel->imagen ? $baseUrl . $gastoDiesel->imagen : null;
            $imagenOtros = $gastoOtros && $gastoOtros->imagen ? $baseUrl . $gastoOtros->imagen : null;

            $logoEmpresa = $hoja->ruta && $hoja->ruta->empresa && $hoja->ruta->empresa->IMAGEN
                ? $baseUrl . $hoja->ruta->empresa->IMAGEN
                : null;

            $nombreEmpresa = $hoja->ruta && $hoja->ruta->empresa
                ? $hoja->ruta->empresa->NOMBRE
                : 'EMPRESA SIN NOMBRE';

            $vista = $esUnSoloDia ? 'pdf.hoja_trabajo' : 'pdf.hojaTrabajoBulk';

            $htmlHoja = view($vista, compact(
                'hoja',
                'vueltasUsuario',
                'imagenDiesel',
                'imagenOtros',
                'logoEmpresa',
                'nombreEmpresa'
            ))->render();

            $htmlCompleto .= '<div style="page-break-after: always;">' . $htmlHoja . '</div>';
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $pdf = new Dompdf($options);
        $pdf->loadHtml($htmlCompleto);
        $pdf->setPaper('A4');
        $pdf->render();

        return $pdf->stream("reporte_rango_{$inicio}_{$fin}.pdf");
    }

    public function verHojaTrabajo($id)
    {
        try {
            $hoja = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'ayudante', 'gastos', 'producciones'])->findOrFail($id);
            $vueltasUsuario = ProduccionUsuario::with('usuario')
                ->where('id_hoja', $id)
                ->orderBy('nro_vuelta')
                ->get();

            // Calcular totales
            $totalProduccion = $hoja->producciones->sum('valor_vuelta');
            $totalUsuario = $vueltasUsuario->sum('valor_vuelta');

            // Calcular total de gastos por tipo
            $tiposGastos = ['DIESEL', 'CONDUCTOR', 'AYUDANTE', 'ALIMENTACION', 'OTROS'];
            $gastos = [];
            $totalGastos = 0;

            foreach ($tiposGastos as $tipo) {
                $valor = $hoja->gastos->where('tipo_gasto', $tipo)->sum('valor');
                $gastos[$tipo] = $valor;
                $totalGastos += $valor;
            }

            // Seleccionar el mayor entre Total Producción y Total Usuario
            $totalMayor = max($totalProduccion, $totalUsuario);

            // Calcular Total a Depositar restando los gastos
            $totalADepositar = $totalMayor - $totalGastos;

            return view('hoja_trabajo.ver', compact(
                'hoja',
                'vueltasUsuario',
                'totalProduccion',
                'totalUsuario',
                'gastos',
                'totalGastos',
                'totalADepositar'
            ));
        } catch (\Exception $e) {
            return redirect()->route('home.inicio')->with('error', 'No se pudo cargar la hoja de trabajo.');
        }
    }


    public function verHojaTrabajoApi($id)
    {
        try {
            $hoja = HojaTrabajo::with(['unidad', 'ruta', 'conductor', 'ayudante', 'gastos', 'producciones'])->findOrFail($id);

            $vueltasUsuario = ProduccionUsuario::with('usuario')
                ->where('id_hoja', $id)
                ->orderBy('nro_vuelta')
                ->get();

            // Calcular totales
            $totalProduccion = $hoja->producciones->sum('valor_vuelta');
            $totalUsuario = $vueltasUsuario->sum('valor_vuelta');

            // Calcular total de gastos por tipo
            $tiposGastos = ['DIESEL', 'CONDUCTOR', 'AYUDANTE', 'ALIMENTACION', 'OTROS'];
            $gastos = [];
            $totalGastos = 0;

            foreach ($tiposGastos as $tipo) {
                $valor = $hoja->gastos->where('tipo_gasto', $tipo)->sum('valor');
                $gastos[] = [
                    'tipo' => $tipo,
                    'valor' => $valor
                ];
                $totalGastos += $valor;
            }

            $totalMayor = max($totalProduccion, $totalUsuario);
            $totalADepositar = $totalMayor - $totalGastos;

            return response()->json([
                'hoja' => $hoja,
                'produccion_conductor' => $hoja->producciones,
                'produccion_fiscalizador' => $vueltasUsuario,
                'gastos' => $gastos,
                'total_produccion' => $totalProduccion,
                'total_fiscalizador' => $totalUsuario,
                'total_gastos' => $totalGastos,
                'total_a_depositar' => $totalADepositar,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo obtener la hoja de trabajo.'], 500);
        }
    }

}
