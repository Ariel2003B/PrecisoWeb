<?php

namespace App\Http\Controllers;

use App\Models\CUOTAS;
use App\Models\DETALLE_SIMCARD;
use App\Models\DETALLE_SIMCARD_SERVICIO;
use App\Models\SIMCARD;
use App\Models\SIMCARD_DEPENDENCIAS_LIBERADA;
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
        // Traemos todo lo que el modal necesita
        $simcard->load([
            'usuario',
            'v_e_h_i_c_u_l_o',
            'detalleSimcards.cuotas' => function ($q) {
                $q->orderBy('FECHA_PAGO');
            },
            'servicios',              // ⬅️ AJUSTA al nombre real de tu relación
            'documentosGenerados',
        ]);

        // Contrato vigente (con sus cuotas)
        $vigente = $simcard->detalleSimcards()
            ->vigente()
            ->with('cuotas')
            ->first();

        // Historial de contratos (hardware)
        $historial = $simcard->detalleSimcards()
            ->with('cuotas')
            ->orderByDesc('FECHA_ACTIVACION_RENOVACION')
            ->get();

        // Historial de servicios
        $servicios = $simcard->servicios()
            ->orderByDesc('FECHA_SERVICIO')
            ->get();

        return view('simcard.partials.info', compact(
            'simcard',
            'vigente',
            'historial',
            'servicios'
        ));
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
                    // AL_DIA = NO tener:
                    //  - cuotas pendientes con FECHA_PAGO <= proximo
                    //  - servicios pendientes con FECHA_SERVICIO <= proximo
                    //  - NI un servicio reciente cuya FECHA_SIGUIENTE_PAGO <= proximo
                    $qq->whereDoesntHave('detalleVigente.cuotas', function ($q) use ($cuotasPendientes, $proximo) {
                        $cuotasPendientes($q);
                        $q->where('FECHA_PAGO', '<=', $proximo);
                    })
                        ->whereDoesntHave('detalleSimcards.cuotas', function ($q) use ($cuotasPendientes, $proximo) {
                            $cuotasPendientes($q);
                            $q->where('FECHA_PAGO', '<=', $proximo);
                        })
                        ->whereDoesntHave('servicios', function ($q) use ($proximo) {
                            $q->whereNull('COMPROBANTE')
                                ->where('FECHA_SERVICIO', '<=', $proximo);
                        })
                        ->whereDoesntHave('servicioReciente', function ($q) use ($proximo) {
                            // NUEVO: cortar también por fecha siguiente de pago
                            $q->whereNotNull('FECHA_SIGUIENTE_PAGO')
                                ->where('FECHA_SIGUIENTE_PAGO', '<=', $proximo);
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

                // 2) Servicios pendientes (COMPROBANTE NULL)
                $qq->orWhereHas('servicios', function ($q) use ($estado, $hoy, $proximo) {
                    $q->whereNull('COMPROBANTE');

                    if ($estado === 'PROXIMO') {
                        $q->whereBetween('FECHA_SERVICIO', [$hoy, $proximo]);
                    } elseif ($estado === 'VENCIDO') {
                        $q->where('FECHA_SERVICIO', '<', $hoy);
                    }
                });

                // 3) NUEVO: servicio reciente con FECHA_SIGUIENTE_PAGO vencida o próxima,
                //    aunque ese servicio ya esté pagado.
                $qq->orWhereHas('servicioReciente', function ($q) use ($estado, $hoy, $proximo) {
                    $q->whereNotNull('FECHA_SIGUIENTE_PAGO');

                    if ($estado === 'PROXIMO') {
                        // próximo corte de servicio
                        $q->whereBetween('FECHA_SIGUIENTE_PAGO', [$hoy, $proximo]);
                    } elseif ($estado === 'VENCIDO') {
                        // ya se pasó la fecha y no renovó
                        $q->where('FECHA_SIGUIENTE_PAGO', '<', $hoy);
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
            'EQUIPO' => $request->EQUIPO,
            'IMEI' => $request->IMEI,
            'MODELO_EQUIPO' => $request->MODELO_EQUIPO,
            'MARCA_EQUIPO' => $request->MARCA_EQUIPO,
            'PLATAFORMA' => $request->PLATAFORMA,
            'PROVEEDOR' => $request->PROVEEDOR

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


    public function storeContrato(Request $request, SIMCARD $simcard)
    {
        $accion = $request->input('accion', 'guardar');
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
            'cuotas.*.OBSERVACION' => ['nullable', 'string', 'max:1000'],

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
        $servPagado = $request->boolean('SERV_PAGADO'); // usarlo dentro de la transacción


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

        \DB::transaction(function () use ($simcard, $data, $request, $hasServicio, $hasContrato, $accion, $servPagado) {

            // 5) SERVICIO: update-or-create
            if ($hasServicio) {
                $fecha = \Carbon\Carbon::parse($data['SERV_FECHA']);
                $plazo = (int) $data['SERV_PLAZO'];
                $siguiente = $fecha->copy()->addMonths($plazo);

                // Buscar servicio existente (si viene SERV_ID) o crear uno nuevo
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
                $serv->PLAZO_CONTRATADO = $plazo;
                $serv->VALOR_PAGO = $data['SERV_VALOR'];
                $serv->OBSERVACION = $data['SERV_OBSERVACION'] ?? null;

                // Si vino un comprobante por texto
                if (!empty($data['SERV_COMPROBANTE'])) {
                    $serv->COMPROBANTE = $data['SERV_COMPROBANTE'];
                }

                $serv->save(); // asegura SERV_ID

                // Archivo de comprobante
                if ($request->hasFile('SERV_COMPROBANTE_FILE')) {
                    $stored = $request->file('SERV_COMPROBANTE_FILE')
                        ->store("simcards/{$simcard->ID_SIM}/servicios/{$serv->SERV_ID}", 'public');
                    $serv->COMPROBANTE = $stored;
                    $serv->save();
                }

                // Si se marcó como pagado, exigir comprobante
                if ($servPagado && empty($serv->COMPROBANTE)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'SERV_COMPROBANTE_FILE' => 'Marcaste el servicio como pagado, debes subir un comprobante.',
                    ]);
                }

                // === SOLO SI SE PRESIONÓ "RENOVAR SERVICIO" ===
                if ($accion === 'renovar') {

                    // además, obligamos que esté pagado
                    if (!$servPagado) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'SERV_PAGADO' => 'Para renovar el servicio primero márcalo como pagado.',
                        ]);
                    }

                    // Crear el siguiente período de servicio
                    $nuevo = new \App\Models\DETALLE_SIMCARD_SERVICIO();
                    $nuevo->SIM_ID = $simcard->ID_SIM;
                    $nuevo->FECHA_SERVICIO = $siguiente->toDateString(); // empieza cuando termina el actual
                    $nuevo->FECHA_SIGUIENTE_PAGO = $siguiente->copy()->addMonths($plazo)->toDateString();
                    $nuevo->PLAZO_CONTRATADO = $plazo;
                    $nuevo->VALOR_PAGO = $serv->VALOR_PAGO;
                    $nuevo->OBSERVACION = null; // o $serv->OBSERVACION si quieres copiarla
                    $nuevo->COMPROBANTE = null; // nuevo periodo todavía no pagado
                    $nuevo->save();
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
                    $obs = (isset($c['OBSERVACION']) && $c['OBSERVACION'] !== '') ? $c['OBSERVACION'] : null;

                    // Si marcan pagado y no hay archivo nuevo ni existente => error de validación
                    if ($pagado && !$tieneArchivo && !$teniaArchivoAntes) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "cuotas.$idx.COMPROBANTE_FILE" => 'Comprobante obligatorio cuando la cuota está marcada como pagada.',
                        ]);
                    }

                    $cuoId = $c['CUO_ID'] ?? null;
                    $filaVacia = is_null($fecha) && is_null($valor) && is_null($compTexto) && !$tieneArchivo && is_null($obs);
                    ;
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
                    $cuota->OBSERVACION = $obs;
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
            // 6.bis) NUEVO HARDWARE: crear nuevo contrato solo si TODO está pagado
            if ($accion === 'nuevo_hardware') {
                // Debe existir un contrato trabajado en este submit
                if (!$hasContrato || !isset($detalle)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'VALOR_TOTAL' => 'No hay contrato de hardware para generar uno nuevo.',
                    ]);
                }

                // Refrescar con cuotas ya guardadas
                $detalleRefrescado = \App\Models\DETALLE_SIMCARD::with('cuotas')
                    ->where('DET_ID', $detalle->DET_ID)
                    ->where('SIM_ID', $simcard->ID_SIM)
                    ->first();

                if (!$detalleRefrescado) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'VALOR_TOTAL' => 'No se pudo encontrar el contrato de hardware actual.',
                    ]);
                }

                // Verificar que NO existan cuotas sin comprobante
                $tienePendientes = $detalleRefrescado->cuotas()
                    ->whereNull('COMPROBANTE')
                    ->exists();

                if ($tienePendientes || (float) $detalleRefrescado->SALDO > 0) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'VALOR_TOTAL' => 'Para vender nuevo hardware, todas las cuotas deben estar pagadas con comprobante y el saldo en 0.',
                    ]);
                }

                // Crear un nuevo contrato de hardware "en blanco" para nueva venta
                $nuevo = new \App\Models\DETALLE_SIMCARD();
                $nuevo->SIM_ID = $simcard->ID_SIM;
                $nuevo->FECHA_ACTIVACION_RENOVACION = now()->toDateString(); // puedes dejarla null si prefieres
                $nuevo->FECHA_SIGUIENTE_PAGO = null;
                $nuevo->PLAZO_CONTRATADO = null;
                $nuevo->VALOR_TOTAL = 0;
                $nuevo->VALOR_ABONADO = 0;
                $nuevo->SALDO = 0;
                $nuevo->NUMERO_CUOTAS = null;
                $nuevo->save();
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

        $docs = DB::table('DOCUMENTOS_GENERADOS')
            ->where('SIM_ID', $simcard->ID_SIM)
            ->count();

        $servicios = DETALLE_SIMCARD_SERVICIO::where('SIM_ID', $simcard->ID_SIM)->count();
        // 👉 Si quisieras solo servicios pendientes, podrías usar:
        // $servicios = DETALLE_SIMCARD_SERVICIO::where('SIM_ID', $simcard->ID_SIM)
        //     ->whereNull('COMPROBANTE')
        //     ->count();

        return response()->json([
            'detalles' => $detalles,
            'documentos' => $docs,
            'servicios' => $servicios,
            'has' => ($detalles + $docs + $servicios) > 0,
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

    // public function migrateDependents(Request $request, SIMCARD $simcard)
    // {
    //     $data = $request->validate([
    //         'target_sim_id' => ['required', 'integer', 'different:current_sim_id', 'exists:SIMCARD,ID_SIM'],
    //     ], [], [
    //             'target_sim_id' => 'SIM destino',
    //         ] + ['current_sim_id' => $simcard->ID_SIM]);

    //     $targetId = (int) $data['target_sim_id'];

    //     // 1) Validar que el destino NO tenga detalles
    //     $hasDetalles = DETALLE_SIMCARD::where('SIM_ID', $targetId)->exists();
    //     if ($hasDetalles) {
    //         return response()->json(['ok' => false, 'message' => 'La SIM destino ya tiene detalles asignados.'], 422);
    //     }
    //     DB::transaction(function () use ($simcard, $targetId) {
    //         // a) Mover DETALLE_SIMCARD al destino
    //         DETALLE_SIMCARD::where('SIM_ID', $simcard->ID_SIM)->update(['SIM_ID' => $targetId]);

    //         // b) Mover DOCUMENTOS_GENERADOS al destino (ajusta el nombre de la tabla/campos si difieren)
    //         DB::table('DOCUMENTOS_GENERADOS')->where('SIM_ID', $simcard->ID_SIM)->update(['SIM_ID' => $targetId]);

    //         // c) Migrar también el USUARIO vinculado a la SIM (USU_ID) y, si quieres, el PROPIETARIO (texto)
    //         $sourceUserId = $simcard->USU_ID;
    //         $sourcePropietario = $simcard->PROPIETARIO;

    //         // Sobreescribimos destino con el usuario de origen (comportamiento más práctico para "migrar")
    //         SIMCARD::where('ID_SIM', $targetId)->update([
    //             'USU_ID' => $sourceUserId,
    //             'PROPIETARIO' => $sourcePropietario, // quita esta línea si NO quieres mover el nombre visual
    //         ]);

    //         // Deja la SIM de origen sin usuario (ya que vas a dejarla LIBRE/ELIMINADA)
    //         SIMCARD::where('ID_SIM', $simcard->ID_SIM)->update([
    //             'USU_ID' => null,
    //             // 'PROPIETARIO' => null, // descomenta si quieres limpiar también el texto
    //         ]);
    //     });

    //     return response()->json(['ok' => true, 'message' => 'Dependencias y usuario migrados correctamente.']);
    // }


    // public function migrateDependents(Request $request, SIMCARD $simcard)
    // {
    //     // Ahora este método YA NO MIGRA a otra SIM,
    //     // solo "libera" las dependencias dejándolas huérfanas (SIM_ID = null)

    //     DB::transaction(function () use ($simcard) {

    //         // a) Detalles de contrato / cuotas -> SIM_ID NULL
    //         DETALLE_SIMCARD::where('SIM_ID', $simcard->ID_SIM)
    //             ->update(['SIM_ID' => null]);

    //         // b) Servicios -> SIM_ID NULL
    //         DETALLE_SIMCARD_SERVICIO::where('SIM_ID', $simcard->ID_SIM)
    //             ->update(['SIM_ID' => null]);

    //         // c) Documentos generados -> SIM_ID NULL
    //         DB::table('DOCUMENTOS_GENERADOS')
    //             ->where('SIM_ID', $simcard->ID_SIM)
    //             ->update(['SIM_ID' => null]);

    //         // d) La SIM queda sin usuario (y opcionalmente sin PROPIETARIO)
    //         SIMCARD::where('ID_SIM', $simcard->ID_SIM)->update([
    //             'USU_ID' => null,
    //             // 'PROPIETARIO' => null, // descomenta si quieres limpiar también este texto
    //         ]);
    //     });

    //     return response()->json([
    //         'ok' => true,
    //         'message' => 'Dependencias liberadas correctamente (quedaron huérfanas).',
    //     ]);
    // }


    public function migrateDependents(Request $request, SIMCARD $simcard)
    {
        DB::transaction(function () use ($simcard) {

            $simId = $simcard->ID_SIM;
            $userId = $simcard->USU_ID; // lo vamos a guardar en el historial

            /*
             * 1) Registrar DETALLE_SIMCARD en SIMCARD_DEPENDENCIAS_LIBERADAS
             */
            $detalles = DETALLE_SIMCARD::where('SIM_ID', $simId)->get();

            foreach ($detalles as $detalle) {
                SIMCARD_DEPENDENCIAS_LIBERADA::create([
                    'SIM_ORIGEN_ID' => $simId,
                    'DETALLE_ID' => $detalle->DET_ID,
                    'DETALLE_SERVICIO_ID' => null,
                    'USU_ID' => $userId,
                    'SIM_DESTINO_ID' => null,
                    'ESTADO' => 'PENDIENTE',
                    // 'FECHA_REGISTRO' se llena sola por DEFAULT CURRENT_TIMESTAMP
                ]);
            }

            /*
             * 2) Registrar DETALLE_SIMCARD_SERVICIO en SIMCARD_DEPENDENCIAS_LIBERADAS
             */
            $servicios = DETALLE_SIMCARD_SERVICIO::where('SIM_ID', $simId)->get();

            foreach ($servicios as $servicio) {
                SIMCARD_DEPENDENCIAS_LIBERADA::create([
                    'SIM_ORIGEN_ID' => $simId,
                    'DETALLE_ID' => null,
                    'DETALLE_SERVICIO_ID' => $servicio->SERV_ID,
                    'USU_ID' => $userId,
                    'SIM_DESTINO_ID' => null,
                    'ESTADO' => 'PENDIENTE',
                ]);
            }

            /*
             * 3) (Opcional) Registrar solo el USUARIO aunque no haya detalles/servicios
             *    Esto te sirve para saber que la SIM tenía USU_ID vinculado aunque no tuviera contratos.
             *    Si no lo quieres duplicado, solo lo creas si no hubo filas arriba.
             */
            if ($userId && $detalles->isEmpty() && $servicios->isEmpty()) {
                SIMCARD_DEPENDENCIAS_LIBERADA::create([
                    'SIM_ORIGEN_ID' => $simId,
                    'DETALLE_ID' => null,
                    'DETALLE_SERVICIO_ID' => null,
                    'USU_ID' => $userId,
                    'SIM_DESTINO_ID' => null,
                    'ESTADO' => 'PENDIENTE',
                ]);
            }

            /*
             * 4) Ahora sí: dejar huérfanos los registros reales
             */
            DETALLE_SIMCARD::where('SIM_ID', $simId)->update(['SIM_ID' => null]);

            DETALLE_SIMCARD_SERVICIO::where('SIM_ID', $simId)->update(['SIM_ID' => null]);

            DB::table('DOCUMENTOS_GENERADOS')
                ->where('SIM_ID', $simId)
                ->update(['SIM_ID' => null]);

            SIMCARD::where('ID_SIM', $simId)->update([
                'USU_ID' => null,
                // 'PROPIETARIO' => null, // si también quieres limpiarlo
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Dependencias liberadas y registradas en historial correctamente.',
        ]);
    }
    public function orphansData()
    {
        // Grupos de dependencias pendientes por SIM_ORIGEN_ID
        $pendientes = SIMCARD_DEPENDENCIAS_LIBERADA::with(['simOrigen', 'usuario'])
            ->where('ESTADO', 'PENDIENTE')
            ->get()
            ->groupBy('SIM_ORIGEN_ID');

        $origins = [];

        foreach ($pendientes as $simOrigenId => $group) {
            /** @var \App\Models\SIMCARD|null $sim */
            $sim = $group->first()->simOrigen;
            $user = $group->first()->usuario;

            $numDetalles = $group->whereNotNull('DETALLE_ID')->count();
            $numServicios = $group->whereNotNull('DETALLE_SERVICIO_ID')->count();

            $nombreCliente = $user
                ? trim(($user->APELLIDO ?? '') . ' ' . ($user->NOMBRE ?? ''))
                : 'Sin usuario';

            $origins[] = [
                'id' => $simOrigenId,
                'label' => sprintf(
                    'SIM origen #%s - %s (%d contratos, %d servicios)',
                    $sim?->NUMEROTELEFONO ?? $simOrigenId,
                    $nombreCliente,
                    $numDetalles,
                    $numServicios
                ),
            ];
        }

        // SIMs destino disponibles (puedes ajustar el filtro)
        $targets = SIMCARD::where('ESTADO', '!=', 'ELIMINADA')
            // SIN contratos (DETALLE_SIMCARD)
            ->whereDoesntHave('detalleSimcards')
            // SIN servicios (DETALLE_SIMCARD_SERVICIO)
            ->whereDoesntHave('servicios')
            ->orderBy('NUMEROTELEFONO')
            ->get()
            ->map(function (SIMCARD $s) {
                return [
                    'id' => $s->ID_SIM,
                    'label' => $s->NUMEROTELEFONO . ' - ' . ($s->PLATAFORMA ?? ''),
                ];
            })
            ->values();


        return response()->json([
            'origins' => $origins,
            'targets' => $targets,
        ]);
    }

    public function reassignOrphans(Request $request)
    {
        $data = $request->validate([
            'sim_origen_id' => ['required', 'integer'],
            'sim_destino_id' => ['required', 'integer', 'different:sim_origen_id', 'exists:SIMCARD,ID_SIM'],
        ], [], [
            'sim_origen_id' => 'SIM origen',
            'sim_destino_id' => 'SIM destino',
        ]);

        $simOrigenId = (int) $data['sim_origen_id'];
        $simDestinoId = (int) $data['sim_destino_id'];

        DB::transaction(function () use ($simOrigenId, $simDestinoId) {
            $rows = SIMCARD_DEPENDENCIAS_LIBERADA::where('SIM_ORIGEN_ID', $simOrigenId)
                ->where('ESTADO', 'PENDIENTE')
                ->lockForUpdate()
                ->get();

            if ($rows->isEmpty()) {
                throw new \RuntimeException('No hay dependencias pendientes para esta SIM de origen.');
            }

            $userId = $rows->first()->USU_ID;

            // Reasignar cada contrato / servicio
            foreach ($rows as $row) {
                if ($row->DETALLE_ID) {
                    DETALLE_SIMCARD::where('DET_ID', $row->DETALLE_ID)
                        ->update(['SIM_ID' => $simDestinoId]);
                }
                if ($row->DETALLE_SERVICIO_ID) {
                    DETALLE_SIMCARD_SERVICIO::where('SERV_ID', $row->DETALLE_SERVICIO_ID)
                        ->update(['SIM_ID' => $simDestinoId]);
                }
            }

            // Reasignar también el usuario a la SIM destino
            if ($userId) {
                SIMCARD::where('ID_SIM', $simDestinoId)->update([
                    'USU_ID' => $userId,
                ]);
            }

            // Actualizar historial
            SIMCARD_DEPENDENCIAS_LIBERADA::where('SIM_ORIGEN_ID', $simOrigenId)
                ->where('ESTADO', 'PENDIENTE')
                ->update([
                    'SIM_DESTINO_ID' => $simDestinoId,
                    'ESTADO' => 'REASIGNADO',
                    'FECHA_REASIGNACION' => now(),
                ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Dependencias reasignadas correctamente.',
        ]);
    }
    public function update(Request $request, SIMCARD $simcard)
    {
        $request->validate([
            'PROPIETARIO' => 'required|string|max:255',
            'NUMEROTELEFONO' => 'required|string|unique:SIMCARD,NUMEROTELEFONO,' . $simcard->ID_SIM . ',ID_SIM',
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
            'EQUIPO' => 'nullable|string|in:GPS,MODEM,LECTOR DE QR,COMPUTADOR ABORDO,MOVIL',
            'ASIGNACION' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) use ($request, $simcard) {
                    if (!empty($value) && !empty($request->EQUIPO)) {
                        $prefix = substr($value, 0, 7);

                        $exists = SIMCARD::where('ASIGNACION', 'LIKE', $prefix . '%')
                            ->where('EQUIPO', $request->EQUIPO)
                            ->where('ID_SIM', '<>', $simcard->ID_SIM)
                            ->exists();

                        if ($exists) {
                            $fail("La combinación de asignación '$prefix' y equipo '{$request->EQUIPO}' ya existe en otro registro.");
                        }
                    }
                },
            ],

            // ⬇⬇ Validación del archivo de foto
            'FOTO_FILE' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Datos básicos a actualizar
        $datosUpdate = [
            'CUENTA' => $request->CUENTA,
            'PROPIETARIO' => $request->PROPIETARIO,
            'NUMEROTELEFONO' => $request->NUMEROTELEFONO,
            'TIPOPLAN' => $request->TIPOPLAN,
            'PLAN' => $request->PLAN,
            'ICC' => $request->ICC,
            'ESTADO' => $request->ESTADO,
            'ASIGNACION' => $request->ASIGNACION,
            'GRUPO' => $request->GRUPO,
            'EQUIPO' => $request->EQUIPO,
            'IMEI' => $request->IMEI,
            'MODELO_EQUIPO' => $request->MODELO_EQUIPO,
            'MARCA_EQUIPO' => $request->MARCA_EQUIPO,
            'PLATAFORMA' => $request->PLATAFORMA,
            'PROVEEDOR' => $request->PROVEEDOR,
        ];

        // Si viene una foto nueva, guardarla en storage y actualizar columna FOTO
        if ($request->hasFile('FOTO_SIM_FILE')) {
            $basePath = "simcards/{$simcard->ID_SIM}/foto";
            $stored = $request->file('FOTO_SIM_FILE')->store($basePath, 'public');

            // Borrar archivo anterior si existía y es un path nuestro
            if (!empty($simcard->FOTO) && Str::startsWith($simcard->FOTO, ['simcards/'])) {
                Storage::disk('public')->delete($simcard->FOTO);
            }

            $datosUpdate['FOTO_SIM'] = $stored;
        }

        // Actualizar registro
        $simcard->update($datosUpdate);

        return redirect()
            ->route('simcards.index')
            ->with('success', 'SIM Card actualizada exitosamente.');
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
