<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login.index');
    }


    public function login(Request $request)
    {
        $credentials = $request->only('CORREO', 'CLAVE');

        if (Auth::attempt(['CORREO' => $credentials['CORREO'], 'password' => $credentials['CLAVE']])) {
            return redirect()->route('home.inicio')->with('success', 'Inicio de sesión exitoso.');
        }

        return redirect()->route('login.form')->with('error', 'Correo o contraseña incorrectos.');
    }


    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Sesión cerrada']);
    }
}
