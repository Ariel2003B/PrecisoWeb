<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\USUARIO;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    public function index()
    {
        $unidades = Unidad::with('usuario.empresa')->get();
        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        $usuarios = USUARIO::where('ESTADO', 'A')->get(); // Opcional si quieres listar solo activos
        return view('unidades.create', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'numero_habilitacion' => 'required|string|max:255',
            'placa' => 'required|string|max:255',
            'propietario' => 'nullable|string|max:255',
            'anio_fabricacion' => 'nullable|integer',
            'chasis' => 'nullable|string|max:255',
            'carroceria' => 'nullable|string|max:255',
            'tipo_especial' => 'nullable|string|max:255',
            'capacidad_pasajeros' => 'nullable|integer',
            'puertas_ingreso' => 'nullable|integer',
            'puertas_izquierdas' => 'nullable|integer',
            'usu_id' => 'nullable|exists:USUARIO,USU_ID'
        ]);


        Unidad::create($request->all());

        return redirect()->route('unidades.index')->with('success', 'Unidad creada exitosamente.');
    }

    public function edit($id)
    {
        $unidad = Unidad::findOrFail($id);
        $usuarios = USUARIO::where('ESTADO', 'A')->get();
        return view('unidades.edit', compact('unidad', 'usuarios'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'numero_habilitacion' => 'nullable|string|max:255',
            'placa' => 'nullable|string|max:255',
            'propietario' => 'nullable|string|max:255',
            'anio_fabricacion' => 'nullable|integer',
            'chasis' => 'nullable|string|max:255',
            'carroceria' => 'nullable|string|max:255',
            'tipo_especial' => 'nullable|string|max:255',
            'capacidad_pasajeros' => 'nullable|integer',
            'puertas_ingreso' => 'nullable|integer',
            'puertas_izquierdas' => 'nullable|integer',
            'usu_id' => 'nullable|exists:USUARIO,USU_ID'
        ]);

        $unidad = Unidad::findOrFail($id);
        $unidad->update($request->all());

        return redirect()->route('unidades.index')->with('success', 'Unidad actualizada exitosamente.');
    }
}
