<?php

namespace App\Http\Controllers;

use App\Models\SIMCARD;
use App\Models\VEHICULO;
use Illuminate\Http\Request;

class SimCardController extends Controller
{
    // public function index()
    // {
    //     $simcards = SIMCARD::with('v_e_h_i_c_u_l_o')->paginate(40);
    //     return view('simcard.index', compact('simcards'));

    // }
    public function index(Request $request)
    {
        $query = SIMCARD::with('v_e_h_i_c_u_l_o');

        // Aplicar filtro de búsqueda
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('RUC', 'like', "%$search%")
                    ->orWhere('PROPIETARIO', 'like', "%$search%")
                    ->orWhere('CUENTA', 'like', "%$search%")
                    ->orWhere('PLAN', 'like', "%$search%")
                    ->orWhere('TIPOPLAN', 'like', "%$search%")
                    ->orWhere('ICC', 'like', "%$search%")
                    ->orWhere('NUMEROTELEFONO', 'like', "%$search%");

                // Manejar el caso de "Sin Asignar"
                if (strtolower($search) === 'sin asignar' || strtolower($search) === 'asignar' || strtolower($search) === 'sin') {
                    $q->orWhereNull('VEH_ID'); // Ajusta 'v_e_h_i_c_u_l_o_id' al nombre correcto de tu clave foránea
                } else {
                    $q->orWhereHas('v_e_h_i_c_u_l_o', function ($query) use ($search) {
                        $query->where('TIPO', 'like', "%$search%")
                            ->orWhere('PLACA', 'like', "%$search%");
                    });
                }
            });
        }

        // Paginar los resultados
        $simcards = $query->paginate(10);

        // Retornar la vista con los resultados
        return view('simcard.index', compact('simcards'));
    }



    public function create()
    {
        $vehiculos = VEHICULO::whereDoesntHave('s_i_m_c_a_r_d_s')->get();

        return view('simcard.create', compact('vehiculos'));
    }


    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'PROPIETARIO' => 'required|string|max:255',
            'NUMEROTELEFONO' => 'required|string|max:10|unique:SIMCARD,NUMEROTELEFONO',
            'TIPOPLAN' => 'required|string|max:255',
            'PLAN' => 'nullable|string|max:255',
            'ICC' => 'nullable|string|max:255',
            'ESTADO' => 'required|string|max:2',
            'TIPO' => 'nullable|string|max:255', // Validación para el tipo de vehículo
            'PLACA' => 'nullable|string|max:7|unique:VEHICULO,PLACA', // Validación para la placa
        ]);
        // Insertar el vehículo si se proporcionan los datos
        $vehiculoId = null;
        if ($request->filled('TIPO') && $request->filled('PLACA')) {
            $vehiculo = VEHICULO::create([
                'TIPO' => $request->TIPO,
                'PLACA' => $request->PLACA,
            ]);
            $vehiculoId = $vehiculo->VEH_ID;
        } elseif ($request->filled('VEH_ID')) {
            $vehiculoId = $request->VEH_ID; // Asignar vehículo existente si está seleccionado
        }

        // Crear la SIM Card
        SIMCARD::create([
            'RUC' => $request->RUC,
            'PROPIETARIO' => $request->PROPIETARIO,
            'NUMEROTELEFONO' => $request->NUMEROTELEFONO,
            'TIPOPLAN' => $request->TIPOPLAN,
            'PLAN' => $request->PLAN,
            'ICC' => $request->ICC,
            'ESTADO' => $request->ESTADO,
            'VEH_ID' => $vehiculoId, // Asignar el ID del vehículo creado o existente
        ]);

        return redirect()->route('simcards.index')->with('success', 'SIM Card creada exitosamente.');
    }



    public function edit(SIMCARD $simcard)
    {
        // Obtener vehículos no asignados o incluir el vehículo actualmente asignado a esta SIM card
        $vehiculos = VEHICULO::whereDoesntHave('s_i_m_c_a_r_d_s')
            ->orWhere('VEH_ID', $simcard->VEH_ID)
            ->get();

        return view('simcard.edit', compact('simcard', 'vehiculos'));
    }

    public function bulkUpload(Request $request)
    {
        $file = $request->file('csv_file');
        $csvData = file_get_contents($file);

        // Procesa las filas con el delimitador ";"
        $rows = array_map(function ($row) {
            return str_getcsv($row, ';'); // Especifica ";" como delimitador
        }, explode("\n", $csvData));

        // Extrae y limpia el encabezado
        $header = array_shift($rows);
        $header = array_map(function ($value) {
            return trim(str_replace(' ', '_', $value)); // Reemplaza espacios por "_"
        }, $header);

        foreach ($rows as $row) {
            // Verifica si la fila tiene la misma cantidad de columnas que el encabezado
            if (count($row) !== count($header)) {
                continue; // Salta filas con columnas incompletas
            }

            $data = array_combine($header, $row);

            // Limpia y valida los datos necesarios
            $data['ICC'] = isset($data['ICC']) ? trim($data['ICC'], "'") : null;

            $ruc = isset($data['PROPIETARIO'])
                ? ($data['PROPIETARIO'] === 'PRECISOGPS S.A.S.'
                    ? '1793212253001'
                    : ($data['PROPIETARIO'] === 'VARGAS REINOSO CESAR GIOVANNY'
                        ? '1716024474001'
                        : ($data['RUC'] ?? null)))
                : null;

            // Determina el estado en función de las condiciones dadas
            if (empty($data['PLACA']) && !empty($data['TIPO_VEHICULO'])) {
                $estado = 'L'; // Placa vacía pero Tipo Vehículo lleno
            } elseif (empty($data['TIPO_VEHICULO']) && empty($data['PLACA'])) {
                $estado = 'L'; // Ambos vacíos
            } elseif (!empty($data['TIPO_VEHICULO']) && !empty($data['PLACA'])) {
                $estado = stripos($data['TIPO_VEHICULO'], 'INACTIVA') !== false ? 'I' : 'A'; // Placa y Tipo Vehículo llenos
            } else {
                $estado = 'L'; // Valor por defecto
            }

            // Crear el vehículo si tiene datos completos y el estado es diferente de 'LI'
            $vehiculoId = null;
            if (!empty($data['TIPO_VEHICULO']) && !empty($data['PLACA']) && $estado !== 'L') {
                $vehiculo = VEHICULO::firstOrCreate(
                    ['PLACA' => $data['PLACA']],
                    ['TIPO' => $data['TIPO_VEHICULO']]
                );
                $vehiculoId = $vehiculo->VEH_ID;
            }
            // Crea la SIM Card solo si los datos clave están presentes
            if (!empty($data['NUMERO_TELEFONO']) && !empty($data['PROPIETARIO'])) {
                SIMCARD::create([
                    'RUC' => $ruc,
                    'PROPIETARIO' => $data['PROPIETARIO'],
                    'NUMEROTELEFONO' => $data['NUMERO_TELEFONO'],
                    'TIPOPLAN' => $data['TIPO_PLAN'],
                    'PLAN' => $data['PLAN'] ?? null,
                    'ICC' => $data['ICC'],
                    'CUENTA' => $data['CUENTA'] ?? null, // Nuevo campo CUENTA
                    'ESTADO' => $estado,
                    'VEH_ID' => $vehiculoId,
                ]);
            }
        }

        return redirect()->route('simcards.index')->with('success', 'Datos cargados exitosamente.');
    }


    public function update(Request $request, SIMCARD $simcard)
    {
        $request->validate([
            'RUC' => 'nullable|string|max:13',
            'PROPIETARIO' => 'required|string|max:255',
            'NUMEROTELEFONO' => 'required|string|max:10|unique:SIMCARD,NUMEROTELEFONO,' . $simcard->ID_SIM . ',ID_SIM',
            'TIPOPLAN' => 'required|string|max:255',
            'PLAN' => 'nullable|string|max:255',
            'ICC' => 'nullable|string|max:255',
            'ESTADO' => 'required|string|max:2',
            'VEH_ID' => 'nullable|exists:VEHICULO,VEH_ID',
        ]);

        $simcard->update($request->all());

        return redirect()->route('simcards.index')->with('success', 'SIM Card actualizada exitosamente.');
    }

    public function destroy(SIMCARD $simcard)
    {
        $simcard->delete();

        return redirect()->route('simcards.index')->with('success', 'SIM Card eliminada exitosamente.');
    }
}
