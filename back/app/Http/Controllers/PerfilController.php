<?php

namespace App\Http\Controllers;

use App\Models\PERFIL;
use App\Models\PERMISO;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    // Mostrar todos los perfiles
    public function index()
    {
        $perfiles = PERFIL::with('p_e_r_m_i_s_o_s')->get(); // Incluye permisos
        return view('perfil.index', compact('perfiles'));
    }

    // Mostrar formulario de creación
    public function create()
    {
        $permisos = PERMISO::where('ESTADO', 'A')->get(); // Permisos activos
        //dd($permisos);
        return view('perfil.create', compact('permisos'));
    }

    // Guardar nuevo perfil
    public function store(Request $request)
    {
        $request->validate([
            'DESCRIPCION' => 'required|max:255',
            'PERMISOS' => 'array',
        ]);

        $perfil = PERFIL::create([
            'DESCRIPCION' => $request->DESCRIPCION,
            'ESTADO' => 'A',
        ]);

        if ($request->has('PERMISOS')) {
            $perfil->p_e_r_m_i_s_o_s()->sync($request->PERMISOS);
        }

        return redirect()->route('perfil.index')->with('success', 'Perfil creado exitosamente.');
    }

    // Mostrar formulario de edición
    public function edit($id)
    {
        $perfil = PERFIL::with('p_e_r_m_i_s_o_s')->findOrFail($id);
        $permisos = PERMISO::where('ESTADO', 'A')->get();
        return view('perfil.edit', compact('perfil', 'permisos'));
    }

    // Actualizar perfil
    public function update(Request $request, $id)
    {
        $request->validate([
            'DESCRIPCION' => 'required|max:255',
            'PERMISOS' => 'array',
        ]);
        $perfil = PERFIL::findOrFail($id);
        $perfil->update(['DESCRIPCION' => $request->DESCRIPCION]);

        if ($request->has('PERMISOS')) {
            $perfil->p_e_r_m_i_s_o_s()->sync($request->PERMISOS);
        } else {
            $perfil->p_e_r_m_i_s_o_s()->detach();
        }

        return redirect()->route('perfil.index')->with('success', 'Perfil actualizado exitosamente.');
    }

    // Eliminar perfil
    public function destroy($id)
    {
        $perfil = PERFIL::findOrFail($id);
        $perfil->update(['ESTADO' => 'I']); // Eliminación lógica
        return redirect()->route('perfil.index')->with('success', 'Perfil eliminado exitosamente.');
    }



}
