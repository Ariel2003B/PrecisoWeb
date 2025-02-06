<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EQUIPO_ACCESORIO;

class EquipoAccesorioController extends Controller
{
    /**
     * Muestra la lista de equipos y accesorios.
     */
    public function index()
    {
        $equipos = EQUIPO_ACCESORIO::all();
        return view('equipos.index', compact('equipos'));
    }

    /**
     * Muestra el formulario para crear un nuevo equipo/accesorio.
     */
    public function create()
    {
        return view('equipos.create');
    }

    /**
     * Almacena un nuevo equipo/accesorio en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'EQU_NOMBRE' => 'required|string|max:255',
            'EQU_PRECIO' => 'required|numeric|min:0',
            'EQU_ICONO' => 'required|string|max:255'
        ]);

        EQUIPO_ACCESORIO::create([
            'EQU_NOMBRE' => $request->EQU_NOMBRE,
            'EQU_PRECIO' => $request->EQU_PRECIO,
            'EQU_ICONO' => $request->EQU_ICONO
        ]);

        return redirect()->route('equipos.index')->with('success', 'Equipo/Accesorio creado correctamente.');
    }

    /**
     * Muestra el formulario de edición de un equipo/accesorio específico.
     */
    public function edit($id)
    {
        $equipo = EQUIPO_ACCESORIO::findOrFail($id);
        return view('equipos.edit', compact('equipo'));
    }

    /**
     * Actualiza los datos de un equipo/accesorio en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'EQU_NOMBRE' => 'required|string|max:255',
            'EQU_PRECIO' => 'required|numeric|min:0',
            'EQU_ICONO' => 'required|string|max:255'
        ]);

        $equipo = EQUIPO_ACCESORIO::findOrFail($id);
        $equipo->update([
            'EQU_NOMBRE' => $request->EQU_NOMBRE,
            'EQU_PRECIO' => $request->EQU_PRECIO,
            'EQU_ICONO' => $request->EQU_ICONO
        ]);

        return redirect()->route('equipos.index')->with('success', 'Equipo/Accesorio actualizado correctamente.');
    }

    /**
     * Elimina un equipo/accesorio de la base de datos.
     */
    public function destroy($id)
    {
        $equipo = EQUIPO_ACCESORIO::findOrFail($id);
        $equipo->delete();

        return redirect()->route('equipos.index')->with('success', 'Equipo/Accesorio eliminado correctamente.');
    }
}
