<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\USUARIO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function Auth(Request $request)
    {
        // Validar entrada
        $request->validate([
            'correo' => 'required',
            'clave' => 'required',
        ]);
        // Buscar el usuario por correo
        $usuario = USUARIO::where('CORREO', $request->input('correo'))->first();

        // Verificar si existe el usuario y si la clave coincide
        if (!$usuario || !Hash::check($request->input('clave'), $usuario->CLAVE)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        // Generar token
        $token = $usuario->createToken('auth_token')->plainTextToken;

        // Construir respuesta con perfil y permisos
        $perfilDescripcion = $usuario->p_e_r_f_i_l?->DESCRIPCION;
        $permisos = $usuario->p_e_r_f_i_l?->p_e_r_m_i_s_o_s?->pluck('DESCRIPCION') ?? [];

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'user' => [
                'id' => $usuario->USU_ID,
                'nombre' => $usuario->NOMBRE,
                'tokenNimbus' => $usuario->TOKEN,
                'depot' => $usuario->DEPOT,
                'perfil'=>$perfilDescripcion
            ]
        ]);
    }


    public function user()
    {
        $usuario = Auth::user();

        if (!$usuario) {
            return response()->json(['message' => 'Token inválido o expirado'], 401);
        }
        $perfilDescripcion = $usuario->p_e_r_f_i_l?->DESCRIPCION;
        return response()->json([
            'message' => 'Token valido',
            'user' => [
                'id' => $usuario->USU_ID,
                'nombre' => $usuario->NOMBRE,
                'tokenNimbus' => $usuario->TOKEN,
                'depot' => $usuario->DEPOT,
                'perfil'=>$perfilDescripcion
            ]
        ]);
    }

}
