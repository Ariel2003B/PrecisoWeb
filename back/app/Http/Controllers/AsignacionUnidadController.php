<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use App\Models\USUARIO;
use Illuminate\Http\Request;

class AsignacionUnidadController extends Controller
{
    public function index()
    {
        $usuarios = USUARIO::orderBy('NOMBRE')->get();
        $unidades = Unidad::whereNull('usu_id')->orderBy('numero_habilitacion')->get();

        return view('asignacion.index', compact('usuarios', 'unidades'));
    }

    public function asignar(Request $request)
    {
        $request->validate([
            'usu_id' => 'required|exists:USUARIO,USU_ID',
            'unidad_id' => 'required|exists:unidades,id_unidad',
        ]);

        $unidad = Unidad::findOrFail($request->unidad_id);
        $unidad->usu_id = $request->usu_id;
        $unidad->save();

        return redirect()->route('asignacion.index')->with('success', 'Unidad asignada correctamente.');
    }
}
