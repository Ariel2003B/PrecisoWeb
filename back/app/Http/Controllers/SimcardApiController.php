<?php

namespace App\Http\Controllers;

use App\Models\SIMCARD;
use Illuminate\Http\Request;

class SimcardApiController extends Controller
{
    public function index(Request $request)
    {
        // Crear la consulta base con relaciones
        $query = SIMCARD::without('v_e_h_i_c_u_l_o');

        // Aplicar filtro de búsqueda
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('CUENTA', 'like', "%$search%")
                    ->orWhere('PLAN', 'like', "%$search%")
                    ->orWhere('TIPOPLAN', 'like', "%$search%")
                    ->orWhere('ICC', 'like', "%$search%")
                    ->orWhere('NUMEROTELEFONO', 'like', "%$search%")
                    ->orWhere('ESTADO', 'like', "%$search%")
                    ->orWhere('GRUPO', 'like', "%$search%")
                    ->orWhere('ASIGNACION', 'like', "%$search%")
                    ->orWhere('EQUIPO', 'like', "%$search%")
                ;

                // Manejar el caso de "Sin Asignar"
                if (strtolower($search) === 'sin asignar' || strtolower($search) === 'asignar' || strtolower($search) === 'sin') {
                    $q->whereNull('GRUPO')
                        ->orWhereNull('ASIGNACION');
                }
            });
        }

        // Ordenar los resultados
        $query->orderBy('ID_SIM', 'desc');

        // Obtener todos los registros sin paginación
        $simcards = $query->get();

        // Retornar los datos en formato JSON
        return response()->json($simcards);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $id = $request->query('search', ''); // Obtén el parámetro de consulta
        $id = rawurldecode($id); // Decodifica espacios y caracteres especiales
    
        $query = SIMCARD::without('v_e_h_i_c_u_l_o');
        $query->where(function ($q) use ($id) {
            $q->where('CUENTA', 'like', "%$id%")
                ->orWhere('PLAN', 'like', "%$id%")
                ->orWhere('TIPOPLAN', 'like', "%$id%")
                ->orWhere('ICC', 'like', "%$id%")
                ->orWhere('NUMEROTELEFONO', 'like', "%$id%")
                ->orWhere('ESTADO', 'like', "%$id%")
                ->orWhere('GRUPO', 'like', "%$id%")
                ->orWhere('ASIGNACION', 'like', "%$id%")
                ->orWhere('IMEI', 'like', "%$id%");
    
            if (strtolower($id) === 'sin asignar' || strtolower($id) === 'asignar' || strtolower($id) === 'sin') {
                $q->whereNull('GRUPO')
                    ->orWhereNull('ASIGNACION');
            }
        });
    
        $query->orderBy('ID_SIM', 'desc');
        $simcards = $query->get();
    
        if ($simcards->count() > 0) {
            return response()->json($simcards);
        }
    
        return response()->json(['Error' => 'No hay datos']);
    }
    


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validar los datos del JSON
        $request->validate([
            'CUENTA' => 'required|string|max:500',
            'NUMEROTELEFONO' => 'required|string|max:10',
            'TIPOPLAN' => 'required|string|max:500',
            'PLAN' => 'nullable|string|max:500',
            'ICC' => 'nullable|string|max:100',
            'GRUPO' => 'nullable|string|max:1000',
            'ASIGNACION' => 'nullable|string|max:500',
            'EQUIPO' => 'nullable|string|max:1000',
            'IMEI' => 'nullable|string|max:1000',
            'ESTADO' => 'required|in:ACTIVA,ELIMINADA,LIBRE',
        ]);
    
        // Buscar la SIMCARD por ID
        $simcard = SIMCARD::find($id);
    
        if (!$simcard) {
            return response()->json(['error' => 'SIMCARD no encontrada'], 404);
        }
        // Actualizar los datos
        $simcard->CUENTA = $request->input('CUENTA');
        $simcard->NUMEROTELEFONO = $request->input('NUMEROTELEFONO');
        $simcard->TIPOPLAN = $request->input('TIPOPLAN');
        $simcard->PLAN = $request->input('PLAN');
        $simcard->ICC = $request->input('ICC');
        $simcard->GRUPO = $request->input('GRUPO');
        $simcard->ASIGNACION = $request->input('ASIGNACION');
        $simcard->EQUIPO = $request->input('EQUIPO');
        $simcard->IMEI = $request->input('IMEI');
        $simcard->ESTADO = $request->input('ESTADO');
    
        // Si el estado es "ELIMINADA" o "LIBRE", limpiar campos específicos
        if ($simcard->ESTADO === 'ELIMINADA') {
            $simcard->ICC = null;
            $simcard->GRUPO = null;
            $simcard->ASIGNACION = null;
            $simcard->EQUIPO = null;
            $simcard->IMEI = null;
        } elseif ($simcard->ESTADO === 'LIBRE') {
            $simcard->GRUPO = null;
            $simcard->ASIGNACION = null;
            $simcard->EQUIPO = null;
            $simcard->IMEI = null;
        }
    
        // Guardar los cambios en la base de datos
        $simcard->save();
    
        return response()->json(['success' => 'SIMCARD actualizada exitosamente', 'simcard' => $simcard]);
    }
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
