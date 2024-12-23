<?php

namespace App\Http\Controllers;

use App\Models\SIMCARD;
use App\Models\VEHICULO;
use Illuminate\Http\Request;

class SimCardController extends Controller
{
    public function index()
    {
        $simcards = SIMCARD::with('v_e_h_i_c_u_l_o')->get();
        return view('simcard.index', compact('simcards'));
    }

    public function create()
    {
        $vehiculos = VEHICULO::whereDoesntHave('s_i_m_c_a_r_d_s')->get();

        return view('simcard.create', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'RUC' => 'nullable|string|max:13',
            'PROPIETARIO' => 'required|string|max:255',
            'NUMEROTELEFONO' => 'required|string|max:10|unique:SIMCARD,NUMEROTELEFONO',
            'TIPOPLAN' => 'required|string|max:255',
            'PLAN' => 'nullable|string|max:255',
            'ICC' => 'nullable|string|max:255',
            'ESTADO' => 'required|string|max:2',
            'VEH_ID' => 'nullable|exists:VEHICULO,VEH_ID',
        ]);

        SIMCARD::create($request->all());

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
