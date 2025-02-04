<?php

namespace App\Http\Controllers;

use App\Models\SIMCARD;
use App\Models\VEHICULO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SimCardController extends Controller
{
    public function index(Request $request)
    {
        $query = SIMCARD::with('v_e_h_i_c_u_l_o');

        // Obtener opciones únicas para los desplegables
        $cuentas = SIMCARD::select('CUENTA')->distinct()->pluck('CUENTA');
        $planes = SIMCARD::select('PLAN')->distinct()->pluck('PLAN');
        $tiposPlan = SIMCARD::select('TIPOPLAN')->distinct()->pluck('TIPOPLAN');

        // Aplicar filtro de búsqueda existente
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
                    ->orWhere('IMEI', 'like', "%$search%");
            });
        }

        // Aplicar filtros adicionales desde los dropdowns
        if ($request->filled('CUENTA')) {
            $query->where('CUENTA', $request->input('CUENTA'));
        }
        if ($request->filled('PLAN')) {
            $query->where('PLAN', $request->input('PLAN'));
        }
        if ($request->filled('TIPOPLAN')) {
            $query->where('TIPOPLAN', $request->input('TIPOPLAN'));
        }

        // Ordenar los resultados del más reciente al más antiguo
        $query->orderBy('ID_SIM', 'desc');

        // Paginar los resultados
        $simcards = $query->paginate(20);

        // Retornar la vista con los resultados y las opciones de filtro
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
        ]);

        return redirect()->route('simcards.index')->with('success', 'SIM Card actualizada exitosamente.');
    }



    public function destroy(SIMCARD $simcard)
    {
        $simcard->delete();

        return redirect()->route('simcards.index')->with('success', 'SIM Card eliminada exitosamente.');
    }



    public function updateWialonPhones(Request $request)
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
        try {
            $html = view('pdf.reporteactualizacion', ['updatedSimcards' => $updatedSimcards])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);

            $pdf = new Dompdf($options);
            $pdf->loadHtml($html);
            $pdf->setPaper('A4');
            $pdf->render();

            // Guardar el PDF en storage/app/public/pdf/
            $pdfPath = storage_path('app/public/pdf/actualizacion_numeros.pdf');
            file_put_contents($pdfPath, $pdf->output());

            // Verificar si el archivo se guardó correctamente


            // Enviar el PDF por correo
            Mail::send([], [], function ($message) use ($pdfPath) {
                $message->to("cesar.vargas@gmail.com")
                    ->subject("Reporte de Actualización en Wialon")
                    ->attach($pdfPath, [
                        'as' => 'reporte_actualizacion.pdf',
                        'mime' => 'application/pdf',
                    ]);
            });

            return response()->json(["message" => "Actualización completada. Se enviaron " . count($updatedSimcards) . " cambios."]);

        } catch (\Exception $th) {
            return response()->json(["message" => "Error generando PDF: " . $th->getMessage()], 500);
        }


        //return response()->json(["message" => "Actualización completada. Se enviaron " . count($updatedSimcards) . " cambios."]);
    }
}
