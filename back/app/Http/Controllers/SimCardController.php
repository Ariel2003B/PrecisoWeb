<?php

namespace App\Http\Controllers;

use App\Models\CUOTAS;
use App\Models\DETALLE_SIMCARD;
use App\Models\SIMCARD;
use App\Models\USUARIO;
use App\Models\VEHICULO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Mime\Part\Text\HtmlPart;
use Symfony\Component\Mime\Part\TextPart;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class SimCardController extends Controller
{

    public function info(SIMCARD $simcard)
    {
        // Eager loading de relaciones para el modal
        $simcard->load([
            'usuario',
            'v_e_h_i_c_u_l_o',
            'detalleSimcards.cuotas' => fn($q) => $q->orderBy('FECHA_PAGO'),
            'documentosGenerados',
        ]);

        // Contrato vigente (si existe) y el historial
        $vigente = $simcard->detalleSimcards()->vigente()->first();
        $historial = $simcard->detalleSimcards()
            ->orderByDesc('FECHA_ACTIVACION_RENOVACION')
            ->get();

        // Retornamos HTML para inyectar en el modal (AJAX)
        return view('simcard.partials.info', compact('simcard', 'vigente', 'historial'));
    }
    public function index(Request $request)
    {
        $query = SIMCARD::with([
            'usuario',
            'v_e_h_i_c_u_l_o',
            'servicioReciente',
            'detalleVigente.cuotas',     // para cuotas del detalle vigente
            // Fallback si no usas detalleVigente:
            // 'detalleSimcards.cuotas',
        ]);

        // filtros existentes...
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->Where('CUENTA', 'like', "%$search%")
                    ->orWhere('PLAN', 'like', "%$search%")
                    ->orWhere('TIPOPLAN', 'like', "%$search%")
                    ->orWhere('ICC', 'like', "%$search%")
                    ->orWhere('NUMEROTELEFONO', 'like', "%$search%")
                    ->orWhere('ESTADO', 'like', "%$search%")
                    ->orWhere('GRUPO', 'like', "%$search%")
                    ->orWhere('ASIGNACION', 'like', "%$search%")
                    ->orWhere('EQUIPO', 'like', "%$search%")
                    ->orWhere('IMEI', 'like', "%$search%")
                    ->orWhere('PLATAFORMA', 'like', "%$search%")
                    ->orWhere('PROVEEDOR', 'like', "%$search%")
                    ->orWhereHas('usuario', function ($uq) use ($search) {
                        $uq->where(function ($qq) use ($search) {
                            $qq->where('NOMBRE', 'like', "%$search%")
                                ->orWhere('APELLIDO', 'like', "%$search%")
                                ->orWhere('CEDULA', 'like', "%$search%");
                        });
                    });
            });
        }
        // === Filtro por estado de pago (backend) ===
// Estados: 'AL_DIA' | 'PROXIMO' | 'VENCIDO'
        if ($request->filled('pago_estado')) {
            $estado = $request->input('pago_estado');
            $hoy = now()->toDateString();
            $proximo = now()->addDays(5)->toDateString();

            $query->where(function ($qq) use ($estado, $hoy, $proximo) {

                // ==== CUOTAS PENDIENTES (COMPROBANTE NULL) ====
                $cuotasPendientes = function ($q) {
                    $q->whereNull('COMPROBANTE');
                };

                if ($estado === 'AL_DIA') {
                    // AL_DIA = NO tener cuotas ni servicios pendientes
                    // con fecha <= hoy+5 (nada vencido ni próximo)
                    $qq->whereDoesntHave('detalleVigente.cuotas', function ($q) use ($cuotasPendientes, $proximo) {
                        $cuotasPendientes($q);
                        $q->where('FECHA_PAGO', '<=', $proximo);
                    })
                        ->whereDoesntHave('detalleSimcards.cuotas', function ($q) use ($cuotasPendientes, $proximo) {
                            $cuotasPendientes($q);
                            $q->where('FECHA_PAGO', '<=', $proximo);
                        })
                        ->whereDoesntHave('servicios', function ($q) use ($hoy, $proximo) {
                            $q->whereNull('COMPROBANTE')
                                ->where('FECHA_SERVICIO', '<=', $proximo);
                        });

                    return; // importante: no seguir con el resto del código
                }

                // ==== VENCIDO / PROXIMO ====
                // 1) Cuotas
                $cuotasFiltro = function ($q) use ($estado, $hoy, $proximo, $cuotasPendientes) {
                    $cuotasPendientes($q);

                    if ($estado === 'PROXIMO') {
                        $q->whereBetween('FECHA_PAGO', [$hoy, $proximo]);
                    } elseif ($estado === 'VENCIDO') {
                        $q->where('FECHA_PAGO', '<', $hoy);
                    }
                };

                $qq->whereHas('detalleVigente.cuotas', $cuotasFiltro)
                    ->orWhereHas('detalleSimcards.cuotas', $cuotasFiltro);

                // 2) Servicios PENDIENTES (COMPROBANTE NULL)
                $qq->orWhereHas('servicios', function ($q) use ($estado, $hoy, $proximo) {
                    $q->whereNull('COMPROBANTE');

                    if ($estado === 'PROXIMO') {
                        $q->whereBetween('FECHA_SERVICIO', [$hoy, $proximo]);
                    } elseif ($estado === 'VENCIDO') {
                        $q->where('FECHA_SERVICIO', '<', $hoy);
                    }
                });
            });
        }


        // filtros por dropdown (CUENTA, PLAN, TIPOPLAN)...
        if ($request->filled('CUENTA'))
            $query->where('CUENTA', $request->input('CUENTA'));
        if ($request->filled('PLAN'))
            $query->where('PLAN', $request->input('PLAN'));
        if ($request->filled('TIPOPLAN'))
            $query->where('TIPOPLAN', $request->input('TIPOPLAN'));

        $query->orderBy('ID_SIM', 'desc');
        $simcards = $query->paginate(20);

        // Opciones únicas para tus selects
        $cuentas = SIMCARD::select('CUENTA')->distinct()->pluck('CUENTA');
        $planes = SIMCARD::select('PLAN')->distinct()->pluck('PLAN');
        $tiposPlan = SIMCARD::select('TIPOPLAN')->distinct()->pluck('TIPOPLAN');

        return view('simcard.index', compact('simcards', 'cuentas', 'planes', 'tiposPlan'));
    }




    public function fetchWialonData(Request $request)
    {
        $asignacion = $request->input('asignacion');

        if (!$asignacion) {
            return response()->json(['error' => 'Asignación no proporcionada'], 400);
        }

        // Autenticación y búsqueda en Wialon en una sola URL
        $token = 'c189ef69fcbd980c9f3740cf36824fe0DD7E2CFE53154BE1A0D6A5A6B66DD2D74DF90157';
        $url = "https://hst-api.wialon.com/wialon/ajax.html?svc=token/login&params=" . urlencode(json_encode(['token' => $token]));

        // Obtener el SID
        $authResponse = Http::get($url);

        if ($authResponse->failed()) {
            return response()->json(['error' => 'Error al autenticar con Wialon'], 500);
        }

        $sid = $authResponse->json('eid');

        if (!$sid) {
            return response()->json(['error' => 'SID no encontrado en la respuesta'], 500);
        }

        // Construir la URL para buscar datos en Wialon
        $searchUrl = "https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params=" . urlencode(json_encode([
            'spec' => [
                'itemsType' => 'avl_unit',
                'propName' => 'sys_name',
                'propValueMask' => $asignacion,
                'sortType' => 'sys_name',
            ],
            'force' => 1,
            'flags' => 4611686018427387903,
            'from' => 0,
            'to' => 0,
        ])) . "&sid=" . $sid;

        // Realizar la búsqueda
        $searchResponse = Http::get($searchUrl);

        if ($searchResponse->failed()) {
            return response()->json(['error' => 'Error al buscar datos en Wialon'], 500);
        }

        $data = $searchResponse->json('items');

        if (empty($data)) {
            return response()->json(['error' => 'No se encontraron datos en Wialon'], 404);
        }

        $item = $data[0];

        // Extraer los datos necesarios
        $icc = isset($item['prms']['iccid']['v']) ? rtrim($item['prms']['iccid']['v'], 'F') : null;
        $imei = isset($item['uid']) ? rtrim($item['uid'], 'F') : null;
        $telefono = isset($item['ph']) ? substr($item['ph'], 4) : null;

        return response()->json([
            'icc' => $icc,
            'imei' => $imei,
            'telefono' => $telefono,
        ]);
    }


    public function create()
    {
        return view('simcard.create');
    }



    public function downloadTemplate()
    {
        // Definir los encabezados de la plantilla
        $headers = [
            'PROPIETARIO',
            'CUENTA',
            'PLAN',
            'CODIGO PLAN',
            'ICC',
            'NUMERO TELEFONO',
            'GRUPO',
            'ASIGNACION',
            'ESTADO'
        ];

        // Definir un ejemplo de fila para mayor claridad
        $exampleRow = [
            'PRECISOGPS S.A.S.',
            '120013636',
            'CLARO EMPRESA BAM 1.5',
            'BP-9980',
            "'8959301001049890843'",
            '991906800',
            'COMERCIALES',
            'JQ049D',
            'Activa'
        ];

        // Configurar el archivo para la descarga
        $fileName = "Plantilla_SIMCards.csv";

        // Stream del archivo CSV con separador de punto y coma
        return Response::stream(function () use ($headers, $exampleRow) {
            $output = fopen('php://output', 'w');

            // Configurar el delimitador como punto y coma
            $options = [
                'delimiter' => ';', // Aquí usamos punto y coma
            ];

            // Escribir los encabezados y ejemplo
            fputcsv($output, $headers, $options['delimiter']);
            fputcsv($output, $exampleRow, $options['delimiter']);

            fclose($output);
        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName"
        ]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'PROPIETARIO' => 'required|string|max:255',
            'NUMEROTELEFONO' => 'required|string|max:10|unique:SIMCARD,NUMEROTELEFONO',
            'TIPOPLAN' => 'required|string|max:255',
            'PLAN' => 'nullable|string|max:255',
            'ICC' => 'nullable|string|max:255|unique:SIMCARD,ICC',
            'ESTADO' => 'required|string',
            'GRUPO' => 'nullable|string|max:255',
            'EQUIPO' => 'nullable|string|in:GPS,MODEM,LECTOR DE QR,COMPUTADOR ABORDO,MOVIL', // EQUIPO ahora puede ser nulo
            'ASIGNACION' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    // Aplicar validación solo si ASIGNACION y EQUIPO tienen datos
                    if (!empty($value) && !empty($request->EQUIPO)) {
                        // Obtén los primeros 7 caracteres de la columna ASIGNACION
                        $prefix = substr($value, 0, 7);

                        // Validar que no exista la misma combinación de ASIGNACION y EQUIPO
                        $exists = DB::table('SIMCARD')
                            ->where('ASIGNACION', 'LIKE', $prefix . '%')
                            ->where('EQUIPO', $request->EQUIPO)
                            ->exists();

                        if ($exists) {
                            $fail("La combinación de asignación '$prefix' y equipo '{$request->EQUIPO}' ya existe.");
                        }
                    }
                },
            ],
        ]);

        SIMCARD::create([
            'CUENTA' => $request->CUENTA,
            'PROPIETARIO' => $request->PROPIETARIO,
            'NUMEROTELEFONO' => $request->NUMEROTELEFONO,
            'TIPOPLAN' => $request->TIPOPLAN,
            'PLAN' => $request->PLAN,
            'ICC' => $request->ICC,
            'ESTADO' => $request->ESTADO,
            'ASIGNACION' => $request->ASIGNACION,
            'GRUPO' => $request->GRUPO,
            'EQUIPO' => $request->EQUIPO, // EQUIPO puede ser nulo
        ]);

        return redirect()->route('simcards.index')->with('success', 'SIM Card creada exitosamente.');
    }
    public function edit(SIMCARD $simcard)
    {
        return view('simcard.edit', compact('simcard'));
    }

    public function bulkUpload(Request $request)
    {
        $file = $request->file('csv_file');
        $csvData = file_get_contents($file);

        // Procesar las filas con el delimitador ";"
        $rows = array_map(function ($row) {
            return str_getcsv($row, ';'); // Especifica ";" como delimitador
        }, explode("\n", $csvData));

        // Extraer y limpiar el encabezado
        $header = array_shift($rows);
        $header = array_map(function ($value) {
            return trim(str_replace(' ', '_', $value)); // Reemplaza espacios por "_"
        }, $header);

        // Validar encabezado esperado
        $expectedHeaders = ['PROPIETARIO', 'CUENTA', 'PLAN', 'CODIGO_PLAN', 'ICC', 'NUMERO_TELEFONO', 'GRUPO', 'ASIGNACION', 'ESTADO'];
        if ($header !== $expectedHeaders) {
            return redirect()->back()->withErrors(['El formato del archivo CSV no es válido. Verifica los encabezados y vuelve a intentarlo.']);
        }

        $errors = [];
        $created = 0;

        foreach ($rows as $index => $row) {
            // Verificar si la fila está vacía
            if (count(array_filter($row)) === 0) {
                continue; // Ignora filas completamente vacías
            }

            // Verificar si la fila tiene la misma cantidad de columnas que el encabezado
            if (count($row) !== count($header)) {
                $errors[] = "La fila " . ($index + 2) . " tiene un número incorrecto de columnas.";
                continue;
            }

            $data = array_combine($header, $row);

            // Limpiar los datos y verificar formato
            $data = array_map('trim', $data);
            $data['ICC'] = isset($data['ICC']) ? trim($data['ICC'], "'") : null;

            // Validar campos obligatorios
            if (empty($data['NUMERO_TELEFONO']) || empty($data['PROPIETARIO'])) {
                $errors[] = "La fila " . ($index + 2) . " no contiene un número de teléfono o propietario válido.";
                continue;
            }

            // Validar unicidad de ICC
            if (!empty($data['ICC']) && SIMCARD::where('ICC', $data['ICC'])->exists()) {
                $errors[] = "El ICC en la fila " . ($index + 2) . " ya existe.";
                continue;
            }

            // Validar unicidad de NUMERO_TELEFONO
            if (SIMCARD::where('NUMEROTELEFONO', $data['NUMERO_TELEFONO'])->exists()) {
                $errors[] = "El número de teléfono en la fila " . ($index + 2) . " ya existe.";
                continue;
            }

            // Validar unicidad de los primeros 4 caracteres de ASIGNACION si el estado es ACTIVA o LIBRE
            if (!empty($data['ASIGNACION']) && in_array(strtoupper($data['ESTADO']), ['ACTIVA', 'LIBRE'])) {
                $firstSixChars = substr($data['ASIGNACION'], 0, 7);
                $asignacionExists = SIMCARD::where('ESTADO', '!=', 'ELIMINADA')
                    ->where('ASIGNACION', 'like', "$firstSixChars%")
                    ->exists();

                if ($asignacionExists) {
                    $errors[] = "La ASIGNACION en la fila " . ($index + 2) . " tiene los primeros 6 caracteres duplicados para estado 'ACTIVA' o 'LIBRE'.";
                    continue;
                }
            }

            // Crear el registro de SIM Card
            try {
                SIMCARD::create([
                    'RUC' => $data['PROPIETARIO'] === 'PRECISOGPS S.A.S.' ? '1793212253001' : null,
                    'PROPIETARIO' => $data['PROPIETARIO'],
                    'NUMEROTELEFONO' => $data['NUMERO_TELEFONO'],
                    'TIPOPLAN' => $data['CODIGO_PLAN'],
                    'PLAN' => $data['PLAN'] ?? null,
                    'ICC' => $data['ICC'],
                    'CUENTA' => $data['CUENTA'] ?? null,
                    'ESTADO' => strtoupper($data['ESTADO']) ?: 'LIBRE',
                    'GRUPO' => !empty($data['GRUPO']) ? $data['GRUPO'] : null,
                    'ASIGNACION' => !empty($data['ASIGNACION']) ? $data['ASIGNACION'] : null,
                ]);

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Ocurrió un error al procesar la fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        // Retornar mensaje de éxito o errores
        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors)->with('success', "Se crearon $created registros correctamente.");
        }

        return redirect()->route('simcards.index')->with('success', "Todos los datos se cargaron correctamente. Total registros: $created.");
    }


    public function simcardcontratos(SIMCARD $simcard)
    {
        $hoy = \Carbon\Carbon::today();

        // EDITAR: toma el último detalle (no el vigente)
        $detalle = $simcard->detalleSimcards()
            ->with(['cuotas' => fn($q) => $q->orderBy('FECHA_PAGO')])
            ->orderByDesc('FECHA_ACTIVACION_RENOVACION')
            ->orderByDesc('DET_ID')
            ->first();

        // ahora siempre puede editar (si no hay, el form sirve para crear)
        $puedeEditar = true;

        // Historial de contratos (como antes)
        $historial = $simcard->detalleSimcards()
            ->with(['cuotas' => fn($q) => $q->orderBy('FECHA_PAGO')])
            ->orderByDesc('FECHA_ACTIVACION_RENOVACION')
            ->orderByDesc('DET_ID')
            ->get();

        // SERVICIO: último y su historial
        $servicioReciente = $simcard->servicios()
            ->orderByDesc('FECHA_SERVICIO')
            ->orderByDesc('SERV_ID')
            ->first();

        $historialServicios = $simcard->servicios()
            ->orderByDesc('FECHA_SERVICIO')
            ->orderByDesc('SERV_ID')
            ->get();

        // Usuarios
        $usuarios = USUARIO::orderBy('APELLIDO')
            ->orderBy('NOMBRE')
            ->get(['USU_ID', 'NOMBRE', 'APELLIDO', 'CEDULA', 'TELEFONO']);

        return view('simcard.contrato', compact(
            'simcard',
            'usuarios',
            'detalle',
            'historial',
            'puedeEditar',
            'servicioReciente',
            'historialServicios'
        ));
    }



    // public function storeContrato(Request $request, SIMCARD $simcard)
    // {
    //     $data = $request->validate([
    //         'DET_ID' => ['nullable', 'integer', 'exists:DETALLE_SIMCARD,DET_ID'],

    //         'USU_ID' => ['required', 'integer', 'exists:USUARIO,USU_ID'],
    //         'FECHA_ACTIVACION_RENOVACION' => ['required', 'date'],
    //         'PLAZO_CONTRATADO' => ['required', 'integer', 'min:1', 'max:60'],
    //         'VALOR_TOTAL' => ['required', 'numeric', 'min:0'],
    //         'NUMERO_CUOTAS' => ['required', 'integer', 'min:1', 'max:60'],
    //         'FECHA_SIGUIENTE_PAGO' => ['nullable', 'date'],

    //         'cuotas' => ['nullable', 'array'],
    //         'cuotas.*.CUO_ID' => ['nullable', 'integer', 'exists:CUOTAS,CUO_ID'],
    //         'cuotas.*.FECHA_PAGO' => ['nullable', 'date'],
    //         'cuotas.*.VALOR_CUOTA' => ['nullable', 'numeric', 'min:0'],
    //         'cuotas.*.COMPROBANTE' => ['nullable', 'string', 'max:8000'],
    //         'cuotas.*.COMPROBANTE_FILE' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
    //     ]);

    //     // Asegura relación simcard -> usuario
    //     if ($simcard->USU_ID !== (int) $data['USU_ID']) {
    //         $simcard->USU_ID = $data['USU_ID'];
    //         $simcard->save();
    //     }

    //     $fechaActivacion = Carbon::parse($data['FECHA_ACTIVACION_RENOVACION']);
    //     $plazo = (int) $data['PLAZO_CONTRATADO'];
    //     $fechaSiguiente = $fechaActivacion->copy()->addMonths($plazo);

    //     DB::transaction(function () use ($simcard, $data, $fechaSiguiente, $request) {
    //         // 1) Upsert del DETALLE (igual que ya lo tienes)...
    //         if (!empty($data['DET_ID'])) {
    //             $detalle = DETALLE_SIMCARD::where('DET_ID', $data['DET_ID'])
    //                 ->where('SIM_ID', $simcard->ID_SIM)
    //                 ->firstOrFail();
    //             $detalle->FECHA_ACTIVACION_RENOVACION = $data['FECHA_ACTIVACION_RENOVACION'];
    //             $detalle->FECHA_SIGUIENTE_PAGO = $fechaSiguiente->toDateString();
    //             $detalle->PLAZO_CONTRATADO = $data['PLAZO_CONTRATADO'];
    //             $detalle->VALOR_TOTAL = $data['VALOR_TOTAL'];
    //             $detalle->NUMERO_CUOTAS = $data['NUMERO_CUOTAS'];
    //             $detalle->save();
    //         } else {
    //             $detalle = new DETALLE_SIMCARD();
    //             $detalle->SIM_ID = $simcard->ID_SIM;
    //             $detalle->FECHA_ACTIVACION_RENOVACION = $data['FECHA_ACTIVACION_RENOVACION'];
    //             $detalle->FECHA_SIGUIENTE_PAGO = $fechaSiguiente->toDateString();
    //             $detalle->PLAZO_CONTRATADO = $data['PLAZO_CONTRATADO'];
    //             $detalle->VALOR_TOTAL = $data['VALOR_TOTAL'];
    //             $detalle->VALOR_ABONADO = 0;
    //             $detalle->SALDO = $data['VALOR_TOTAL'];
    //             $detalle->NUMERO_CUOTAS = $data['NUMERO_CUOTAS'];
    //             $detalle->save();
    //         }

    //         // 2) Reconciliación de CUOTAS por CUO_ID
    //         $basePath = "simcards/{$simcard->ID_SIM}/detalles/{$detalle->DET_ID}/cuotas";
    //         $cuotasReq = $data['cuotas'] ?? [];

    //         $idsRecibidos = [];

    //         foreach ($cuotasReq as $idx => $c) {
    //             // Normalizar valores: '' -> null
    //             $fecha = !empty($c['FECHA_PAGO']) ? $c['FECHA_PAGO'] : null;
    //             $valor = (isset($c['VALOR_CUOTA']) && $c['VALOR_CUOTA'] !== '') ? $c['VALOR_CUOTA'] : null;
    //             $compTexto = (isset($c['COMPROBANTE']) && $c['COMPROBANTE'] !== '') ? $c['COMPROBANTE'] : null;
    //             $tieneArchivo = $request->hasFile("cuotas.$idx.COMPROBANTE_FILE");

    //             // Si la fila está completamente vacía y no hay archivo ni CUO_ID => NO crear fila basura
    //             $cuoId = $c['CUO_ID'] ?? null;
    //             $filaVacia = is_null($fecha) && is_null($valor) && is_null($compTexto) && !$tieneArchivo;

    //             if ($filaVacia && empty($cuoId)) {
    //                 continue; // no crear nada
    //             }

    //             // Buscar existente si viene CUO_ID
    //             $cuota = null;
    //             if (!empty($cuoId)) {
    //                 $cuota = \App\Models\CUOTAS::where('CUO_ID', $cuoId)
    //                     ->where('DET_ID', $detalle->DET_ID)
    //                     ->first();
    //             }
    //             if (!$cuota) {
    //                 $cuota = new \App\Models\CUOTAS();
    //                 $cuota->DET_ID = $detalle->DET_ID; // SIEMPRE setear DET_ID
    //             }

    //             $cuota->FECHA_PAGO = $fecha;      // null si venía vacío
    //             $cuota->VALOR_CUOTA = $valor;     // null si venía vacío
    //             // Si mandaron texto/URL de comprobante
    //             if (!is_null($compTexto)) {
    //                 $cuota->COMPROBANTE = $compTexto;
    //             }

    //             $cuota->save();

    //             // Manejo de archivo (si llega)
    //             if ($tieneArchivo) {
    //                 $stored = $request->file("cuotas.$idx.COMPROBANTE_FILE")->store($basePath, 'public');
    //                 // si había uno anterior interno, lo borramos
    //                 if (!empty($cuota->COMPROBANTE) && Str::startsWith($cuota->COMPROBANTE, ['simcards/'])) {
    //                     Storage::disk('public')->delete($cuota->COMPROBANTE);
    //                 }
    //                 $cuota->COMPROBANTE = $stored;
    //                 $cuota->save();
    //             }

    //             $idsRecibidos[] = $cuota->CUO_ID;
    //         }

    //         // Borrar cuotas que no vinieron (cuando bajan el número o eliminan filas)
    //         // Si $idsRecibidos está vacío, borra todas las actuales del detalle.
    //         \App\Models\CUOTAS::where('DET_ID', $detalle->DET_ID)
    //             ->when(count($idsRecibidos) > 0, fn($q) => $q->whereNotIn('CUO_ID', $idsRecibidos))
    //             ->when(count($idsRecibidos) === 0, fn($q) => $q) // sin filtro => borra todas
    //             ->delete();
    //         // === Recalcular ABONADO y SALDO ===
    //         $abonado = (float) \App\Models\CUOTAS::where('DET_ID', $detalle->DET_ID)
    //             ->sum('VALOR_CUOTA'); // suma solo las cuotas con valor (null no suma)

    //         $total = (float) $detalle->VALOR_TOTAL;
    //         $saldo = round($total - $abonado, 2);
    //         if ($saldo < 0) { // por si se pagó de más
    //             $saldo = 0.00;
    //         }

    //         $detalle->VALOR_ABONADO = round($abonado, 2);
    //         $detalle->SALDO = $saldo;

    //         // Si quieres que NUMERO_CUOTAS refleje lo que quedó realmente cargado,
    //         // descomenta la siguiente línea (opcional):
    //         // $detalle->NUMERO_CUOTAS = \App\Models\CUOTAS::where('DET_ID', $detalle->DET_ID)->count();

    //         $detalle->save();

    //     });

    //     return redirect()
    //         ->route('simcards.contrato', $simcard->ID_SIM) // o a donde prefieras volver
    //         ->with('ok', 'Contrato guardado correctamente.');
    // }



    public function storeContrato(Request $request, SIMCARD $simcard)
    {
        // 1) Validación base (sin forzar contrato/servicio)
        $baseRules = [
            'USU_ID' => ['required', 'integer', 'exists:USUARIO,USU_ID'],

            // Contrato
            'DET_ID' => ['nullable', 'integer', 'exists:DETALLE_SIMCARD,DET_ID'],
            'FECHA_ACTIVACION_RENOVACION' => ['nullable', 'date'],
            'VALOR_TOTAL' => ['nullable', 'numeric', 'min:0'],
            'NUMERO_CUOTAS' => ['nullable', 'integer', 'min:1', 'max:60'],

            'cuotas' => ['nullable', 'array'],
            'cuotas.*.CUO_ID' => ['nullable', 'integer', 'exists:CUOTAS,CUO_ID'],
            'cuotas.*.FECHA_PAGO' => ['nullable', 'date'],
            'cuotas.*.VALOR_CUOTA' => ['nullable', 'numeric', 'min:0'],
            'cuotas.*.COMPROBANTE' => ['nullable', 'string', 'max:8000'],
            'cuotas.*.COMPROBANTE_FILE' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],

            // Servicio
            'SERV_ID' => ['nullable', 'integer'], // <-- NUEVO (para actualizar)
            'SERV_FECHA' => ['nullable', 'date'],
            'SERV_PLAZO' => ['nullable', 'integer', 'min:1', 'max:60'],
            'SERV_SIGUIENTE_PAGO' => ['nullable', 'date'],
            'SERV_VALOR' => ['nullable', 'numeric', 'min:0'],
            'SERV_OBSERVACION' => ['nullable', 'string', 'max:1000'],
            'SERV_COMPROBANTE' => ['nullable', 'string', 'max:8000'],
            'SERV_COMPROBANTE_FILE' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
        $data = $request->validate($baseRules);

        // 2) Flags
        $hasContrato = filled($data['FECHA_ACTIVACION_RENOVACION'] ?? null)
            || filled($data['VALOR_TOTAL'] ?? null)
            || filled($data['NUMERO_CUOTAS'] ?? null)
            || !empty($data['DET_ID'])
            || !empty($data['cuotas']);

        $hasServicio = filled($data['SERV_FECHA'] ?? null)
            || filled($data['SERV_VALOR'] ?? null)
            || filled($data['SERV_PLAZO'] ?? null)
            || $request->hasFile('SERV_COMPROBANTE_FILE')
            || filled($data['SERV_COMPROBANTE'] ?? null)
            || filled($data['SERV_OBSERVACION'] ?? null)
            || !empty($data['SERV_ID']);

        // 3) Reglas requeridas según flags
        if ($hasContrato) {
            $request->validate([
                'FECHA_ACTIVACION_RENOVACION' => ['required', 'date'],
                'VALOR_TOTAL' => ['required', 'numeric', 'min:0'],
                'NUMERO_CUOTAS' => ['required', 'integer', 'min:1', 'max:60'],
            ]);
        }
        if ($hasServicio) {
            $request->validate([
                'SERV_FECHA' => ['required', 'date'],
                'SERV_PLAZO' => ['required', 'integer', 'min:1', 'max:60'],
                'SERV_VALOR' => ['required', 'numeric', 'min:0'],
            ]);
        }

        // 4) Actualiza propietario siempre
        if ($simcard->USU_ID !== (int) $data['USU_ID']) {
            $simcard->USU_ID = (int) $data['USU_ID'];
            $simcard->save();
        }

        \DB::transaction(function () use ($simcard, $data, $request, $hasServicio, $hasContrato) {

            // 5) SERVICIO: update-or-create
            if ($hasServicio) {
                $fecha = \Carbon\Carbon::parse($data['SERV_FECHA']);
                $plazo = (int) $data['SERV_PLAZO'];
                $siguiente = $fecha->copy()->addMonths($plazo);

                // si mandan SERV_ID y pertenece a la SIM, actualizamos; sino creamos
                $serv = null;
                if (!empty($data['SERV_ID'])) {
                    $serv = \App\Models\DETALLE_SIMCARD_SERVICIO::where('SERV_ID', $data['SERV_ID'])
                        ->where('SIM_ID', $simcard->ID_SIM)
                        ->first();
                }
                if (!$serv) {
                    $serv = new \App\Models\DETALLE_SIMCARD_SERVICIO();
                    $serv->SIM_ID = $simcard->ID_SIM;
                }

                $serv->FECHA_SERVICIO = $fecha->toDateString();
                $serv->FECHA_SIGUIENTE_PAGO = $siguiente->toDateString();
                $serv->PLAZO_CONTRATADO = $plazo; // <-- IMPORTANTE: guardar plazo
                $serv->VALOR_PAGO = $data['SERV_VALOR'];
                $serv->OBSERVACION = $data['SERV_OBSERVACION'] ?? null;
                // URL de comprobante (texto) la pisamos sólo si viene algo
                if (!empty($data['SERV_COMPROBANTE'])) {
                    $serv->COMPROBANTE = $data['SERV_COMPROBANTE'];
                }
                $serv->save(); // asegura SERV_ID

                // archivo: archivo > reemplaza lo que haya
                if ($request->hasFile('SERV_COMPROBANTE_FILE')) {
                    $stored = $request->file('SERV_COMPROBANTE_FILE')
                        ->store("simcards/{$simcard->ID_SIM}/servicios/{$serv->SERV_ID}", 'public');
                    $serv->COMPROBANTE = $stored;
                    $serv->save();
                }
            }

            // 6) CONTRATO
            if ($hasContrato) {
                if (!empty($data['DET_ID'])) {
                    $detalle = \App\Models\DETALLE_SIMCARD::where('DET_ID', $data['DET_ID'])
                        ->where('SIM_ID', $simcard->ID_SIM)->firstOrFail();
                } else {
                    $detalle = new \App\Models\DETALLE_SIMCARD();
                    $detalle->SIM_ID = $simcard->ID_SIM;
                    $detalle->VALOR_ABONADO = 0;
                    $detalle->SALDO = 0;
                }

                $detalle->FECHA_ACTIVACION_RENOVACION = $data['FECHA_ACTIVACION_RENOVACION'];
                $detalle->FECHA_SIGUIENTE_PAGO = null;    // ya no se usa
                $detalle->PLAZO_CONTRATADO = null;        // ya no se usa
                $detalle->VALOR_TOTAL = $data['VALOR_TOTAL'];
                $detalle->NUMERO_CUOTAS = $data['NUMERO_CUOTAS'];
                $detalle->save();

                $basePath = "simcards/{$simcard->ID_SIM}/detalles/{$detalle->DET_ID}/cuotas";
                $cuotasReq = $data['cuotas'] ?? [];
                $idsRecibidos = [];

                foreach ($cuotasReq as $idx => $c) {
                    $fecha = $c['FECHA_PAGO'] ?? null;
                    $valor = (isset($c['VALOR_CUOTA']) && $c['VALOR_CUOTA'] !== '') ? $c['VALOR_CUOTA'] : null;
                    $compTexto = (isset($c['COMPROBANTE']) && $c['COMPROBANTE'] !== '') ? $c['COMPROBANTE'] : null;
                    $tieneArchivo = $request->hasFile("cuotas.$idx.COMPROBANTE_FILE");
                    $pagado = filter_var($c['PAGADO'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $fechaReal = $c['FECHA_REAL_PAGO'] ?? null;
                    $teniaArchivoAntes = !empty($cuota?->COMPROBANTE);

                    // Si marcan pagado y no hay archivo nuevo ni existente => error de validación
                    if ($pagado && !$tieneArchivo && !$teniaArchivoAntes) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "cuotas.$idx.COMPROBANTE_FILE" => 'Comprobante obligatorio cuando la cuota está marcada como pagada.',
                        ]);
                    }

                    $cuoId = $c['CUO_ID'] ?? null;
                    $filaVacia = is_null($fecha) && is_null($valor) && is_null($compTexto) && !$tieneArchivo;
                    if ($filaVacia && empty($cuoId))
                        continue;

                    $cuota = null;
                    if (!empty($cuoId)) {
                        $cuota = \App\Models\CUOTAS::where('CUO_ID', $cuoId)
                            ->where('DET_ID', $detalle->DET_ID)->first();
                    }
                    if (!$cuota) {
                        $cuota = new \App\Models\CUOTAS();
                        $cuota->DET_ID = $detalle->DET_ID;
                    }

                    $cuota->FECHA_PAGO = $fecha;
                    $cuota->VALOR_CUOTA = $valor;
                    if (!is_null($compTexto))
                        $cuota->COMPROBANTE = $compTexto;
                    // Establecer FECHA_REAL_PAGO:
                    //  - Si viene en request, usarla
                    //  - Si está pagado y no vino, fijar hoy()
                    //  - Si NO está pagado y no hay archivo ni texto, la limpiamos
                    if (!empty($fechaReal)) {
                        $cuota->FECHA_REAL_PAGO = \Carbon\Carbon::parse($fechaReal)->toDateString();
                    } elseif ($pagado || $tieneArchivo || !empty($cuota->COMPROBANTE)) {
                        $cuota->FECHA_REAL_PAGO = now()->toDateString();
                    } else {
                        $cuota->FECHA_REAL_PAGO = null;
                    }


                    $cuota->save();

                    if ($tieneArchivo) {
                        $stored = $request->file("cuotas.$idx.COMPROBANTE_FILE")->store($basePath, 'public');
                        if (!empty($cuota->COMPROBANTE) && \Illuminate\Support\Str::startsWith($cuota->COMPROBANTE, ['simcards/'])) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($cuota->COMPROBANTE);
                        }
                        $cuota->COMPROBANTE = $stored;
                        $cuota->save();
                    }

                    $idsRecibidos[] = $cuota->CUO_ID;
                }

                // Si quitaron todas, se eliminan todas las cuotas
                \App\Models\CUOTAS::where('DET_ID', $detalle->DET_ID)
                    ->when(count($idsRecibidos) > 0, fn($q) => $q->whereNotIn('CUO_ID', $idsRecibidos))
                    ->delete();

                $abonado = (float) \App\Models\CUOTAS::where('DET_ID', $detalle->DET_ID)->sum('VALOR_CUOTA');
                $total = (float) $detalle->VALOR_TOTAL;
                $saldo = round(max($total - $abonado, 0), 2);
                $detalle->VALOR_ABONADO = round($abonado, 2);
                $detalle->SALDO = $saldo;
                $detalle->save();
            }
        });

        // 7) Mensaje final
        $msg = match (true) {
            $hasContrato && $hasServicio => 'Contrato y servicio guardados correctamente.',
            $hasContrato => 'Contrato guardado correctamente.',
            $hasServicio => 'Servicio guardado correctamente.',
            default => 'No se enviaron datos de contrato o servicio. Sin cambios.',
        };

        return redirect()->route('simcards.contrato', $simcard->ID_SIM)->with('ok', $msg);
    }


    public function dependencies(SIMCARD $simcard)
    {
        $detalles = DETALLE_SIMCARD::where('SIM_ID', $simcard->ID_SIM)->count();
        // Si tienes modelo DOCUMENTOS_GENERADOS, úsalo; si no, usa DB::
        $docs = DB::table('DOCUMENTOS_GENERADOS')->where('SIM_ID', $simcard->ID_SIM)->count();

        return response()->json([
            'detalles' => $detalles,
            'documentos' => $docs,
            'has' => ($detalles + $docs) > 0,
        ]);
    }

    public function eligibleTargets(Request $request)
    {
        $excludeId = (int) $request->query('exclude');

        // SIMs sin detalles asignados (0 detalles) y distintas a la actual.
        // Opcional: excluir ELIMINADA
        $targets = SIMCARD::query()
            ->where('ID_SIM', '!=', $excludeId)
            ->where(function ($q) { // sin detalles
                $q->whereNotIn('ID_SIM', function ($sub) {
                    $sub->from('DETALLE_SIMCARD')->select('SIM_ID')->whereNotNull('SIM_ID');
                })->orWhereNull('ID_SIM'); // redundante, pero harmless
            })
            ->where(function ($q) {
                $q->whereNull('ESTADO')->orWhere('ESTADO', '!=', 'ELIMINADA');
            })
            ->orderBy('CUENTA')
            ->orderBy('NUMEROTELEFONO')
            ->limit(300)
            ->get(['ID_SIM', 'CUENTA', 'PLAN', 'NUMEROTELEFONO', 'ESTADO']);

        return response()->json([
            'items' => $targets->map(fn($s) => [
                'id' => $s->ID_SIM,
                'label' => sprintf('#%d | %s | %s | %s', $s->ID_SIM, $s->CUENTA ?? '—', $s->PLAN ?? '—', $s->NUMEROTELEFONO ?? '—'),
                'estado' => $s->ESTADO,
            ]),
        ]);
    }

    public function migrateDependents(Request $request, SIMCARD $simcard)
    {
        $data = $request->validate([
            'target_sim_id' => ['required', 'integer', 'different:current_sim_id', 'exists:SIMCARD,ID_SIM'],
        ], [], [
                'target_sim_id' => 'SIM destino',
            ] + ['current_sim_id' => $simcard->ID_SIM]);

        $targetId = (int) $data['target_sim_id'];

        // 1) Validar que el destino NO tenga detalles
        $hasDetalles = DETALLE_SIMCARD::where('SIM_ID', $targetId)->exists();
        if ($hasDetalles) {
            return response()->json(['ok' => false, 'message' => 'La SIM destino ya tiene detalles asignados.'], 422);
        }

        // 2) (Opcional) Si NO quieres sobreescribir un USU_ID ya asignado en destino, descomenta:
        // $targetSim = SIMCARD::findOrFail($targetId);
        // if (!is_null($targetSim->USU_ID)) {
        //     return response()->json(['ok' => false, 'message' => 'La SIM destino ya tiene un usuario asignado.'], 422);
        // }

        DB::transaction(function () use ($simcard, $targetId) {
            // a) Mover DETALLE_SIMCARD al destino
            DETALLE_SIMCARD::where('SIM_ID', $simcard->ID_SIM)->update(['SIM_ID' => $targetId]);

            // b) Mover DOCUMENTOS_GENERADOS al destino (ajusta el nombre de la tabla/campos si difieren)
            DB::table('DOCUMENTOS_GENERADOS')->where('SIM_ID', $simcard->ID_SIM)->update(['SIM_ID' => $targetId]);

            // c) Migrar también el USUARIO vinculado a la SIM (USU_ID) y, si quieres, el PROPIETARIO (texto)
            $sourceUserId = $simcard->USU_ID;
            $sourcePropietario = $simcard->PROPIETARIO;

            // Sobreescribimos destino con el usuario de origen (comportamiento más práctico para "migrar")
            SIMCARD::where('ID_SIM', $targetId)->update([
                'USU_ID' => $sourceUserId,
                'PROPIETARIO' => $sourcePropietario, // quita esta línea si NO quieres mover el nombre visual
            ]);

            // Deja la SIM de origen sin usuario (ya que vas a dejarla LIBRE/ELIMINADA)
            SIMCARD::where('ID_SIM', $simcard->ID_SIM)->update([
                'USU_ID' => null,
                // 'PROPIETARIO' => null, // descomenta si quieres limpiar también el texto
            ]);
        });

        return response()->json(['ok' => true, 'message' => 'Dependencias y usuario migrados correctamente.']);
    }
    public function update(Request $request, SIMCARD $simcard)
    {
        $request->validate([
            'PROPIETARIO' => 'required|string|max:255',
            'NUMEROTELEFONO' => 'required|string|max:10|unique:SIMCARD,NUMEROTELEFONO,' . $simcard->ID_SIM . ',ID_SIM',
            'TIPOPLAN' => 'required|string|max:255',
            'PLAN' => 'nullable|string|max:255',
            'ICC' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('SIMCARD', 'ICC')->ignore($simcard->ID_SIM, 'ID_SIM'),
            ],
            'ESTADO' => 'required|string',
            'GRUPO' => 'nullable|string|max:255',
            'EQUIPO' => 'nullable|string|in:GPS,MODEM,LECTOR DE QR,COMPUTADOR ABORDO,MOVIL', // EQUIPO ahora puede ser nulo
            'ASIGNACION' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request, $simcard) {
                    // Validar solo si ASIGNACION y EQUIPO tienen valores
                    if (!empty($value) && !empty($request->EQUIPO)) {
                        // Extraer los primeros 7 caracteres de ASIGNACION
                        $prefix = substr($value, 0, 7);

                        // Validar que no exista la misma combinación de ASIGNACION y EQUIPO
                        $exists = SIMCARD::where('ASIGNACION', 'LIKE', $prefix . '%')
                            ->where('EQUIPO', $request->EQUIPO)
                            ->where('ID_SIM', '<>', $simcard->ID_SIM) // Ignorar el registro actual
                            ->exists();

                        if ($exists) {
                            $fail("La combinación de asignación '$prefix' y equipo '{$request->EQUIPO}' ya existe en otro registro.");
                        }
                    }
                },
            ],
        ]);

        // Actualizar los datos del registro
        $simcard->update([
            'CUENTA' => $request->CUENTA,
            'PROPIETARIO' => $request->PROPIETARIO,
            'NUMEROTELEFONO' => $request->NUMEROTELEFONO,
            'TIPOPLAN' => $request->TIPOPLAN,
            'PLAN' => $request->PLAN,
            'ICC' => $request->ICC,
            'ESTADO' => $request->ESTADO,
            'ASIGNACION' => $request->ASIGNACION,
            'GRUPO' => $request->GRUPO,
            'EQUIPO' => $request->EQUIPO, // EQUIPO puede ser nulo y se actualiza
            'IMEI' => $request->IMEI,
            'MODELO_EQUIPO' => $request->MODELO_EQUIPO,
            'MARCA_EQUIPO' => $request->MARCA_EQUIPO,
            'PLATAFORMA' => $request->PLATAFORMA,
            'PROVEEDOR' => $request->PROVEEDOR
        ]);

        return redirect()->route('simcards.index')->with('success', 'SIM Card actualizada exitosamente.');
    }



    public function destroy(SIMCARD $simcard)
    {
        $simcard->delete();

        return redirect()->route('simcards.index')->with('success', 'SIM Card eliminada exitosamente.');
    }



    public function updateWialonPhones()
    {
        $wialon_api_url = "https://hst-api.wialon.com/wialon/ajax.html";
        $token = "a21e2472955b1cb0847730f34edcf3e804692BDC51F76DAA7CC69358123221016F111F39";
        $updatedSimcards = [];

        // 1. Obtener la sesión (SID) de Wialon
        $authResponse = Http::get("$wialon_api_url?svc=token/login&params=" . urlencode(json_encode(["token" => $token])));
        $authData = $authResponse->json();

        if (!isset($authData['eid'])) {
            return response()->json(["message" => "Error autenticando en Wialon."], 500);
        }
        $sid = $authData['eid'];

        // 2. Obtener todas las unidades con UID desde Wialon
        $params = json_encode([
            "spec" => [
                "itemsType" => "avl_unit",
                "propName" => "sys_name",
                "propValueMask" => "*",
                "sortType" => "sys_name"
            ],
            "force" => 1,
            "flags" => 256, // Cambiado a 256 para asegurar que traiga el UID (IMEI)
            "from" => 0,
            "to" => 0
        ]);

        $response = Http::get("$wialon_api_url?svc=core/search_items&params=" . urlencode($params) . "&sid=$sid");
        $data = $response->json();

        if (!isset($data["items"]) || empty($data["items"])) {
            return response()->json(["message" => "No se encontraron unidades en Wialon."], 404);
        }

        // 3. Obtener IMEIs desde la base de datos
        $simcards = SIMCARD::select("IMEI", "NUMEROTELEFONO")->get();
        $imei_phone_map = $simcards->pluck("NUMEROTELEFONO", "IMEI")->toArray();

        // 4. Buscar coincidencias y obtener itemId
        foreach ($data["items"] as $unit) {
            if (!isset($unit["uid"]) || empty($unit["uid"])) {
                Log::warning("⚠️ No se encontró UID (IMEI) en esta unidad, se omite.");
                continue;
            }

            $imei = $unit["uid"];
            if (isset($imei_phone_map[$imei])) {
                $new_phone = "+593" . $imei_phone_map[$imei];

                // Obtener el número de teléfono actual en Wialon
                $current_phone = isset($unit["ph"]) ? $unit["ph"] : "";

                // Comparar antes de actualizar
                if ($new_phone === $current_phone) {
                    Log::info("⚠️ El número de IMEI '$imei' ya está actualizado. Se omite.");
                    continue;
                }

                // Obtener itemId desde Wialon
                $params_item = json_encode([
                    "spec" => [
                        "itemsType" => "avl_unit",
                        "propName" => "sys_unique_id",
                        "propValueMask" => $imei,
                        "sortType" => "sys_name"
                    ],
                    "force" => 1,
                    "flags" => 1,
                    "from" => 0,
                    "to" => 0
                ]);

                $item_response = Http::get("$wialon_api_url?svc=core/search_items&params=" . urlencode($params_item) . "&sid=$sid");
                $item_data = $item_response->json();

                if (!isset($item_data["items"][0]["id"])) {
                    Log::warning("⚠️ No se encontró itemId para IMEI '$imei'. Se omite.");
                    continue;
                }

                $item_id = $item_data["items"][0]["id"];

                // 5. Actualizar el número de teléfono en Wialon
                $params_update = json_encode([
                    "itemId" => $item_id,
                    "phoneNumber" => $new_phone
                ]);

                $update_response = Http::get("$wialon_api_url?svc=unit/update_phone&params=" . urlencode($params_update) . "&sid=$sid");
                $update_data = $update_response->json();

                if (!isset($update_data["error"])) {
                    $updatedSimcards[] = ["IMEI" => $imei, "Nuevo Número" => $new_phone];
                }
            }
        }

        // 6. Generar el PDF con los números actualizados
        // try {
        //     $html = view('pdf.reporteactualizacion', ['updatedSimcards' => $updatedSimcards])->render();

        //     $options = new Options();
        //     $options->set('isRemoteEnabled', true);
        //     $options->set('isHtml5ParserEnabled', true);

        //     $pdf = new Dompdf($options);
        //     $pdf->loadHtml($html);
        //     $pdf->setPaper('A4');
        //     $pdf->render();

        //     // Guardar el PDF en storage/app/public/pdf/
        //     $pdfPath = storage_path('app/public/pdf/actualizacion_numeros.pdf');
        //     file_put_contents($pdfPath, $pdf->output());

        //     // Verificar si el archivo se guardó correctamente


        //     // Enviar el PDF por correo

        //     Mail::send([], [], function ($message) use ($pdfPath) {
        //         $message->to("cesar.vargas@precisogps.com")
        //             ->subject("Reporte de Actualización en Wialon")
        //             ->html('<h3>Reporte de Actualización</h3><p>Adjunto encontrarás el reporte de actualización de números en Wialon.</p>')
        //             ->attach($pdfPath, [
        //                 'as' => 'reporte_actualizacion.pdf',
        //                 'mime' => 'application/pdf',
        //             ]);
        //     });



        //     Log::info("🔹 Enviado al correo electronico");
        //     return response()->json(["message" => "Actualización completada. Se enviaron " . count($updatedSimcards) . " cambios."]);

        // } catch (\Exception $th) {
        //     return response()->json(["message" => "Error generando PDF: " . $th->getMessage()], 500);
        // }

    }


    public function getWialonSid()
    {
        $token = "d3f43687417e572e5992f9ce8b5ae098A45E897294385480A33BAD97150A8C7F4C4AC389";
        //$token = "339faffbbfc67f8961beff738db6ccdfA8B13DBE16F44FE34D3967CAA3865BCF29789194";

        $url = "https://hst-api.wialon.com/wialon/ajax.html?svc=token/login&params=" . urlencode(json_encode(["token" => $token]));

        $response = Http::get($url);
        if ($response->failed() || !isset($response->json()['eid'])) {
            return null;
        }

        return $response->json()['eid']; // Retorna el `sid`
    }

    // public function updateSimCardFromWialon()
    // {
    //     set_time_limit(1200);
    //     $sid = $this->getWialonSid();
    //     if (!$sid) {
    //         return response()->json(["message" => "Error obteniendo SID de Wialon."], 500);
    //     }
    //     // URL para obtener los grupos
    //     $groupsUrl = "https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params=" . urlencode(json_encode([
    //         "spec" => [
    //             "itemsType" => "avl_unit_group",
    //             "propName" => "sys_name",
    //             "propValueMask" => "*",
    //             "sortType" => "sys_name"
    //         ],
    //         "force" => 1,
    //         "flags" => 1,
    //         "from" => 0,
    //         "to" => 0
    //     ])) . "&sid=" . $sid;

    //     $groupsResponse = Http::get($groupsUrl);
    //     if ($groupsResponse->failed() || !isset($groupsResponse->json()['items'])) {
    //         return response()->json(["message" => "No se pudieron obtener los grupos de Wialon."], 500);
    //     }

    //     $groups = $groupsResponse->json()['items'];

    //     foreach ($groups as $group) {
    //         $groupName = strtoupper($group['nm']); // Convertir grupo a mayúsculas
    //         $unitIds = $group['u'] ?? [];

    //         foreach ($unitIds as $unitId) {
    //             // Obtener detalles de cada unidad
    //             $unitUrl = "https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_item&params=" . urlencode(json_encode([
    //                 "id" => $unitId,
    //                 "flags" => 4611686018427387903
    //             ])) . "&sid=" . $sid;

    //             $unitResponse = Http::get($unitUrl);
    //             if ($unitResponse->failed() || !isset($unitResponse->json()['item']['uid'])) {
    //                 continue;
    //             }

    //             $unitData = $unitResponse->json()['item'];
    //             $imei = $unitData['uid'];
    //             $assignmentName = $unitData['nm'];

    //             // Validación específica para el grupo "TRANSPERIFERICOS"
    //             if (strtolower($group['nm']) === "transperifericos") {
    //                 if (preg_match('/\.$|\.\.$/', $assignmentName)) {
    //                     Log::info("❌ Ignorada unidad '$assignmentName' en grupo 'TRANSPERIFERICOS' porque termina en punto.");
    //                     continue;
    //                 }
    //             }

    //             // Buscar en la base de datos y actualizar
    //             SIMCARD::where('IMEI', $imei)->update([
    //                 'ASIGNACION' => $assignmentName,
    //                 'GRUPO' => $groupName
    //             ]);
    //         }
    //     }

    //     return response()->json(["message" => "Actualización de SIMCards completada."]);
    // }

    public function updateSimCardFromWialon()
    {
        set_time_limit(seconds: 400); // Evita que Laravel cierre la ejecución si tarda

        $sid = $this->getWialonSid();
        if (!$sid) {
            return response()->json(["message" => "Error obteniendo SID de Wialon."], 500);
        }

        // 🔹 Obtener TODAS las unidades de una sola vez (evita hacer múltiples peticiones)
        $unitsUrl = "https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params=" . urlencode(json_encode([
            "spec" => [
                "itemsType" => "avl_unit",
                "propName" => "sys_name",
                "propValueMask" => "*",
                "sortType" => "sys_name"
            ],
            "force" => 1,
            "flags" => 4611686018427387903, // Trae todos los datos de cada unidad
            "from" => 0,
            "to" => 0
        ])) . "&sid=" . $sid;

        $unitsResponse = Http::get($unitsUrl);
        if ($unitsResponse->failed() || !isset($unitsResponse->json()['items'])) {
            return response()->json(["message" => "No se pudieron obtener las unidades de Wialon."], 500);
        }

        $units = $unitsResponse->json()['items'];

        // 🔹 Obtener los grupos
        $groupsUrl = "https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params=" . urlencode(json_encode([
            "spec" => [
                "itemsType" => "avl_unit_group",
                "propName" => "sys_name",
                "propValueMask" => "*",
                "sortType" => "sys_name"
            ],
            "force" => 1,
            "flags" => 1,
            "from" => 0,
            "to" => 0
        ])) . "&sid=" . $sid;

        $groupsResponse = Http::get($groupsUrl);
        if ($groupsResponse->failed() || !isset($groupsResponse->json()['items'])) {
            return response()->json(["message" => "No se pudieron obtener los grupos de Wialon."], 500);
        }

        $groups = $groupsResponse->json()['items'];

        // 🔹 Crear un mapa de unidades con su grupo correspondiente
        $unitGroupMap = [];
        foreach ($groups as $group) {
            $groupName = strtoupper($group['nm']); // Convertimos el grupo a mayúsculas
            $unitIds = $group['u'] ?? [];

            foreach ($unitIds as $unitId) {
                $unitGroupMap[$unitId] = $groupName; // Asignamos la unidad a su grupo
            }
        }

        // 🔹 Actualizar todas las SIMCARD en una sola transacción
        DB::beginTransaction();
        try {
            foreach ($units as $unit) {
                $imei = $unit['uid'];
                $assignmentName = $unit['nm'];
                $groupName = $unitGroupMap[$unit['id']] ?? null; // Buscar el grupo al que pertenece la unidad

                // 🔹 Validación especial para "TRANSPERIFERICOS"
                if ($groupName === "TRANSPERIFERICOS") {
                    if (preg_match('/\.$|\.\.$/', $assignmentName)) {
                        Log::info("❌ Ignorada unidad '$assignmentName' en grupo 'TRANSPERIFERICOS' porque termina en punto.");
                        continue;
                    }
                }
                // 🔹 Actualizar en la base de datos
                SIMCARD::where('IMEI', $imei)->update([
                    'ASIGNACION' => $assignmentName,
                    'GRUPO' => $groupName
                ]);
            }

            DB::commit();
            try {
                $this->updateWialonPhones();
                return response()->json(["message" => "Actualización de SIMCards completada."]);

            } catch (\Exception $e) {
                return response()->json(["message" => "Error al actualizar las SIMCards: " . $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(["message" => "Error al actualizar las SIMCards: " . $e->getMessage()], 500);
        }
    }



    public function generarReporteExcel(Request $request)
    {
        $simcards = SIMCARD::with('v_e_h_i_c_u_l_o')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte SIMCARD');

        // Definir encabezados
        $headers = [
            'ID SIM',
            'RUC',
            'PROPIETARIO',
            'CUENTA',
            'NUMERO TELEFONO',
            'TIPO PLAN',
            'PLAN',
            'ICC',
            'ESTADO',
            'GRUPO',
            'ASIGNACION',
            'EQUIPO',
            'VEH_ID',
            'IMEI'
        ];

        // Aplicar encabezados al Excel
        $sheet->fromArray($headers, null, 'A1');

        // Aplicar estilos a los encabezados
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '007BFF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN]
            ]
        ]);

        // Congelar la primera fila
        $sheet->freezePane('A2');

        // Insertar datos desde la base de datos
        $row = 2;
        foreach ($simcards as $simcard) {
            $sheet->fromArray([
                $simcard->ID_SIM,
                $simcard->RUC,
                $simcard->PROPIETARIO,
                $simcard->CUENTA,
                $simcard->NUMEROTELEFONO,
                $simcard->TIPOPLAN,
                $simcard->PLAN,
                $simcard->ICC,
                $simcard->ESTADO,
                $simcard->GRUPO,
                $simcard->ASIGNACION,
                $simcard->EQUIPO,
                $simcard->VEH_ID,
                $simcard->IMEI
            ], null, "A$row");

            // Aplicar estilo condicional por estado
            $colorFondo = $simcard->ESTADO === 'ACTIVA' ? 'D4EDDA' : 'F8D7DA';

            $sheet->getStyle("A$row:N$row")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $colorFondo]
                ],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            $row++;
        }

        // Ajustar automáticamente el ancho de las columnas
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar el archivo Excel
        $writer = new Xlsx($spreadsheet);
        $fileName = date("Y-m-d") . '_Reporte_SIMCARD.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

}
