<?php

namespace App\Http\Controllers;

use App\Models\VEHICULO;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = VEHICULO::all();
        return view('vehiculo.index', compact('vehiculos'));
    }

    public function create()
    {
        return view('vehiculo.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'TIPO' => 'required|string|max:255',
            'PLACA' => 'required|string|unique:VEHICULO,PLACA|max:10'
            
        ]);

        VEHICULO::create($request->all());

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo creado exitosamente.');
    }

    public function edit(VEHICULO $vehiculo)
    {
        return view('vehiculo.edit', compact('vehiculo'));
    }

    public function update(Request $request, VEHICULO $vehiculo)
    {
        $request->validate([
            'TIPO' => 'required|string|max:255',
            'PLACA' => 'required|string|max:10|unique:VEHICULO,PLACA,' . $vehiculo->VEH_ID . ',VEH_ID'
        ]);

        $vehiculo->update($request->all());

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo actualizado exitosamente.');
    }

    public function destroy(VEHICULO $vehiculo)
    {
        $vehiculo->delete();

        return redirect()->route('vehiculos.index')->with('success', 'Vehículo eliminado exitosamente.');
    }
}
