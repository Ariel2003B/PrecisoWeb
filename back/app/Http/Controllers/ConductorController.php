<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use Illuminate\Http\Request;

class ConductorController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'cedula' => 'required|string|max:20|unique:personal,cedula',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100'
        ]);

        $conductor = Personal::create([
            'nombre' => $request->nombre,
            'tipo' => 'CONDUCTOR',
            'cedula' => $request->cedula,
            'telefono' => $request->telefono,
            'correo' => $request->correo,
        ]);

        return response()->json($conductor, 201);
    }

    public function index()
    {
        return response()->json(
            Personal::where('tipo', 'CONDUCTOR')->get()
        );
    }
    public function show($id)
    {
        $conductor = Personal::where('tipo', 'CONDUCTOR')->findOrFail($id);
        return response()->json($conductor);
    }

    public function update(Request $request, $id)
    {
        $conductor = Personal::where('tipo', 'CONDUCTOR')->findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
            'cedula' => 'sometimes|required|string|max:20|unique:personal,cedula,' . $id . ',id_personal',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:100'
        ]);

        $conductor->update($request->only(['nombre', 'cedula', 'telefono', 'correo']));

        return response()->json($conductor);
    }

    public function destroy($id)
    {
        $conductor = Personal::where('tipo', 'CONDUCTOR')->findOrFail($id);
        $conductor->delete();

        return response()->json(['message' => 'Conductor eliminado correctamente']);
    }


}
