<?php

namespace App\Http\Controllers;

use App\Models\SIMCARD;
use App\Models\VEHICULO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Response;

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
        $token = 'c189ef69fcbd980c9f3740cf36824fe05A0DD4256AB0D47CB58F280439E69159216F48AE';
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
}
