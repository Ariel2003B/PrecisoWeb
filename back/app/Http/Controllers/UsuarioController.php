<?php

namespace App\Http\Controllers;

use App\Models\USUARIO;
use App\Models\PERFIL;
use App\Models\PERMISO;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = USUARIO::with('p_e_r_f_i_l')->get();
        return view('usuario.index', compact('usuarios'));
    }

    public function create()
    {
        $perfiles = PERFIL::where('ESTADO', 'A')->get();
        $permisos = PERMISO::where('ESTADO', 'A')->get(); // Traemos todos los permisos activos
        return view('usuario.create', compact('perfiles', 'permisos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'APELLIDO' => 'nullable|string|max:255',
            'CORREO' => 'required|email|unique:USUARIO,CORREO',
            'CLAVE' => 'required|min:6',
            'permisos' => 'array' // Aseguramos que sea un array de permisos
        ]);

        $usuario = USUARIO::create([
            'NOMBRE' => $request->NOMBRE,
            'APELLIDO' => $request->APELLIDO,
            'CORREO' => $request->CORREO,
            'CLAVE' => $request->CLAVE,
            'ESTADO' => 'A',
            'TOKEN' => $request->TOKEN,
            'DEPOT' => $request->DEPOT
        ]);

        if ($request->has('permisos')) {
            $usuario->permisos()->sync($request->permisos); // Asignar permisos seleccionados
        }

        return redirect()->route('usuario.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(USUARIO $usuario)
    {
        $perfiles = PERFIL::where('ESTADO', 'A')->get();
        $permisos = PERMISO::where('ESTADO', 'A')->get();
        $usuarioPermisos = $usuario->permisos->pluck('PRM_ID')->toArray(); // Obtener permisos actuales del usuario

        return view('usuario.edit', compact('usuario', 'perfiles', 'permisos', 'usuarioPermisos'));
    }

    public function update(Request $request, USUARIO $usuario)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'APELLIDO' => 'nullable|string|max:255',
            'CORREO' => 'required|email|unique:USUARIO,CORREO,' . $usuario->USU_ID . ',USU_ID',
            'CLAVE' => 'nullable|min:6', // Permite clave nula
            'permisos' => 'array'
        ]);

        $updateData = [
            'NOMBRE' => $request->NOMBRE,
            'APELLIDO' => $request->APELLIDO,
            'CORREO' => $request->CORREO,
            'TOKEN' => $request->TOKEN,
            'DEPOT' => $request->DEPOT
        ];

        if ($request->filled('CLAVE')) {
            $updateData['CLAVE'] = $request->CLAVE;
        }

        $usuario->update($updateData);

        if ($request->has('permisos')) {
            $usuario->permisos()->sync($request->permisos); // Actualizar permisos seleccionados
        }

        return redirect()->route('usuario.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(USUARIO $usuario)
    {
        $usuario->update(['ESTADO' => 'I']);
        return redirect()->route('usuario.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
