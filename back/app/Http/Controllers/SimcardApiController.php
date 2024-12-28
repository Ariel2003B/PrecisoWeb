<?php

namespace App\Http\Controllers;

use App\Models\SIMCARD;
use Illuminate\Http\Request;

class SimcardApiController extends Controller
{
    public function index(Request $request)
    {
        // Crear la consulta base
        $query = SIMCARD::without('v_e_h_i_c_u_l_o');
    
        // Aplicar filtro de búsqueda
        if ($request->filled('search')) {
            $search = $request->input('search');
    
            $query->where(function ($q) use ($search) {
                $q->where('PROPIETARIO', 'like', "%$search%")
                    ->orWhere('CUENTA', 'like', "%$search%")
                    ->orWhere('PLAN', 'like', "%$search%")
                    ->orWhere('TIPOPLAN', 'like', "%$search%")
                    ->orWhere('ICC', 'like', "%$search%")
                    ->orWhere('NUMEROTELEFONO', 'like', "%$search%")
                    ->orWhere('ESTADO', 'like', "%$search%")
                    ->orWhere('GRUPO', 'like', "%$search%")
                    ->orWhere('ASIGNACION', 'like', "%$search%");
                
                // Manejar el caso de "Sin Asignar"
                if (strtolower($search) === 'sin asignar' || strtolower($search) === 'asignar' || strtolower($search) === 'sin') {
                    $q->whereNull('GRUPO')
                        ->orWhereNull('ASIGNACION');
                }
            });
        }
    
        // Ordenar los resultados
        $query->orderBy('ID_SIM', 'desc');
    
        // Aplicar paginación o límite de registros
        if ($request->filled('page') && $request->filled('pageSize')) {
            // Paginación
            $simcards = $query->paginate($request->input('pageSize'));
        } elseif ($request->filled('limit')) {
            // Límite de registros
            $simcards = $query->limit($request->input('limit'))->get();
        } else {
            // Obtener todos los registros sin paginación
            $simcards = $query->get();
        }
    
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
    public function show($id)
    {
        //
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
        //
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
