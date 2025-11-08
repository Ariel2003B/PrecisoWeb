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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
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
        // 1) Reglas más estrictas (para evitar warnings)
        $rules = [
            'fecha' => 'required|date',
            'tipo_dia' => 'required|in:LABORABLE,FERIADO,SABADO,DOMINGO',
            'id_conductor' => 'required|exists:personal,id_personal',
            'id_ruta' => 'required|exists:rutas,id_ruta',
            'id_unidad' => 'required|exists:unidades,id_unidad',

            'gastos' => 'array',
            'gastos.*.tipo_gasto' => 'required|string',
            'gastos.*.valor' => 'required|numeric|min:0',
            'gastos.*.imagen_base64' => 'nullable|string',

            'produccion' => 'array',
            'produccion.*.nro_vuelta' => 'required|integer|min:1',
            'produccion.*.hora_subida' => 'required|date_format:H:i',
            'produccion.*.hora_bajada' => 'required|date_format:H:i|after:produccion.*.hora_subida',
            'produccion.*.valor_vuelta' => 'required|numeric|min:0',
        ];

        Log::info('HojaTrabajo.update llamado', [
            'hoja_id' => $id,
            'ip' => $request->ip(),
            'user_id' => optional($request->user())->id,
        ]);

        try {
            $data = $request->validate($rules);

            return DB::transaction(function () use ($data, $id) {

                $hoja = HojaTrabajo::with(['gastos', 'producciones'])->lockForUpdate()->findOrFail($id);

                // --- Actualizar cabecera
                $hoja->update([
                    'fecha' => $data['fecha'],
                    'tipo_dia' => $data['tipo_dia'],
                    'id_conductor' => $data['id_conductor'],
                    'ayudante_nombre' => $data['ayudante_nombre'] ?? null,
                    'id_ruta' => $data['id_ruta'],
                    'id_unidad' => $data['id_unidad'],
                ]);

                // --- Gastos
                foreach (($data['gastos'] ?? []) as $idx => $gasto) {
                    try {
                        $rutaImagen = null;

                        $tipo = $gasto['tipo_gasto'];
                        $imgB64 = $gasto['imagen_base64'] ?? null;
                        if (in_array($tipo, ['DIESEL', 'OTROS']) && $imgB64) {
                            if (!preg_match('/^data:image\/(\w+);base64,/', $imgB64, $m)) {
                                throw new \RuntimeException('Formato base64 inválido');
                            }
                            $raw = base64_decode(substr($imgB64, strpos($imgB64, ',') + 1), true);
                            if ($raw === false) {
                                throw new \RuntimeException('Contenido base64 corrupto');
                            }

                            // --- Comprimir con GD ---
                            $compressed = $this->compressToJpeg($raw, 1600, 72);

                            $file = 'gastos/gasto_' . Str::uuid() . '.jpg';
                            Storage::disk('public')->put($file, $compressed);
                            $rutaImagen = $file;
                        }

                        $hoja->gastos()->updateOrCreate(
                            ['tipo_gasto' => $tipo],
                            [
                                'valor' => $gasto['valor'],
                                'imagen' => $rutaImagen
                                    ?? optional($hoja->gastos->firstWhere('tipo_gasto', $tipo))->imagen
                            ]
                        );
                    } catch (\Throwable $e) {
                        Log::error('Error guardando gasto', [
                            'hoja_id' => $hoja->id,
                            'index' => $idx,
                            'tipo' => $gasto['tipo_gasto'] ?? null,
                            'mensaje' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e; // fuerza rollback
                    }
                }

                // --- Producción
                foreach (($data['produccion'] ?? []) as $idx => $prod) {
                    try {
                        $hoja->producciones()->updateOrCreate(
                            ['nro_vuelta' => $prod['nro_vuelta']],
                            [
                                'hora_subida' => $prod['hora_subida'],
                                'hora_bajada' => $prod['hora_bajada'],
                                'valor_vuelta' => $prod['valor_vuelta'],
                            ]
                        );
                    } catch (\Throwable $e) {
                        Log::error('Error guardando producción', [
                            'hoja_id' => $hoja->id,
                            'index' => $idx,
                            'nro' => $prod['nro_vuelta'] ?? null,
                            'mensaje' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        throw $e; // rollback
                    }
                }

                Log::info('Hoja de trabajo actualizada llega hasta aqui OK', ['hoja_id' => $hoja->id]);
                return response()->json(['message' => 'Hoja de trabajo actualizada correctamente']);
            });

        } catch (ValidationException $e) {
            Log::warning('Validación fallida en update HojaTrabajo', [
                'hoja_id' => $id,
                'errors' => $e->errors(),
            ]);
            throw $e; // Laravel responderá 422 con los errores
        } catch (ModelNotFoundException $e) {
            Log::notice('HojaTrabajo no encontrada', ['hoja_id' => $id]);
            return response()->json(['message' => 'Hoja no encontrada'], 404);
        } catch (\Throwable $e) {
            Log::critical('Fallo inesperado en HojaTrabajo.update', [
                'hoja_id' => $id,
                'mensaje' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Error interno del servidor'], 500);
        }
    }
    private function compressToJpeg(string $raw, int $maxW = 1600, int $quality = 72): string
    {
        // intento PNG/JPEG; si falla, devuelve el raw para no romper flujo
        $src = @imagecreatefromstring($raw);
        if (!$src)
            return $raw;

        $w = imagesx($src);
        $h = imagesy($src);

        if ($w > $maxW) {
            $newW = $maxW;
            $newH = intval($h * ($newW / $w));
        } else {
            $newW = $w;
            $newH = $h;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

        ob_start();
        // Siempre JPEG para máxima reducción
        imagejpeg($dst, null, $quality);
        $jpeg = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $jpeg;
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
