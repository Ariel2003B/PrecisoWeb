<?php

namespace App\Http\Controllers;

use App\Models\USUARIO;
use App\Models\PERFIL;
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
        return view('usuario.create', compact('perfiles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'APELLIDO' => 'nullable|string|max:255',
            'CORREO' => 'required|email|unique:USUARIO,CORREO',
            'CLAVE' => 'required|min:6',
            'PER_ID' => 'required|exists:PERFIL,PER_ID',
        ]);

        USUARIO::create([
            'NOMBRE' => $request->NOMBRE,
            'APELLIDO' => $request->APELLIDO,
            'CORREO' => $request->CORREO,
            'CLAVE' => $request->CLAVE,
            'ESTADO' => 'A',
            'PER_ID' => $request->PER_ID,
            'TOKEN' => $request->TOKEN,
            'DEPOT' => $request->DEPOT
        ]);

        return redirect()->route('usuario.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(USUARIO $usuario)
    {
        $perfiles = PERFIL::where('ESTADO', 'A')->get();
        return view('usuario.edit', compact('usuario', 'perfiles'));
    }

    public function update(Request $request, USUARIO $usuario)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'APELLIDO' => 'nullable|string|max:255',
            'CORREO' => 'required|email|unique:USUARIO,CORREO,' . $usuario->USU_ID . ',USU_ID',
            'CLAVE' => 'nullable|min:6', // Permite clave nula
            'PER_ID' => 'required|exists:PERFIL,PER_ID',
        ]);

        // Actualización de los datos del usuario
        $updateData = [
            'NOMBRE' => $request->NOMBRE,
            'APELLIDO' => $request->APELLIDO,
            'CORREO' => $request->CORREO,
            'PER_ID' => $request->PER_ID,
            'TOKEN' => $request->TOKEN,
            'DEPOT' => $request->DEPOT
        ];

        // Solo actualizar la clave si se proporciona una nueva
        if ($request->filled('CLAVE')) {
            $updateData['CLAVE'] = $request->CLAVE; // Laravel usará el setter para encriptarla
        }

        // Actualizar el usuario
        $usuario->update($updateData);

        return redirect()->route('usuario.index')->with('success', 'Usuario actualizado exitosamente.');

    }

    public function destroy(USUARIO $usuario)
    {
        $usuario->update(['ESTADO' => 'I']);
        return redirect()->route('usuario.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}
