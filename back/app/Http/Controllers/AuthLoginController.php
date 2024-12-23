<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\PERFIL;
use App\Models\PERFILPERMISO;
use App\Models\PERMISO;
use App\Models\USUARIO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthLoginController extends Controller
{
    public function showLoginForm()
    {
        // Verificar si no existen usuarios
        if (USUARIO::count() === 0 && PERMISO::count() === 0) {
            DB::transaction(function () {
                // Crear el perfil de administrador
                $perfil = PERFIL::create([
                    'DESCRIPCION' => 'Administrador',
                    'ESTADO' => 'A'
                ]);
                // Crear permisos iniciales
                $permisoUsuarios = PERMISO::create([
                    'DESCRIPCION' => 'USUARIOS',
                    'ESTADO' => 'A'
                ]);
                
                $permisoSimcards = PERMISO::create([
                    'DESCRIPCION' => 'SIMCARDS',
                    'ESTADO' => 'A'
                ]);

                $permisoVehiculos = PERMISO::create([
                    'DESCRIPCION' => 'VEHICULOS',
                    'ESTADO' => 'A'
                ]);
                $permisoVehiculos = PERMISO::create([
                    'DESCRIPCION' => 'PERFILES',
                    'ESTADO' => 'A'
                ]);
                // Asociar permisos al perfil administrador
                PERFILPERMISO::create([
                    'PER_ID' => $perfil->PER_ID,
                    'PRM_ID' => $permisoUsuarios->PRM_ID
                ]);

                PERFILPERMISO::create([
                    'PER_ID' => $perfil->PER_ID,
                    'PRM_ID' => $permisoSimcards->PRM_ID
                ]);

                PERFILPERMISO::create([
                    'PER_ID' => $perfil->PER_ID,
                    'PRM_ID' => $permisoVehiculos->PRM_ID
                ]);

                // Crear el usuario administrador asociado al perfil
                USUARIO::create([
                    'PER_ID' => $perfil->PER_ID,
                    'NOMBRE' => 'Admin',
                    'APELLIDO' => 'Master',
                    'CORREO' => 'elvisguato02@gmail.com',
                    'CLAVE' => 'Ariel2003B', // El setter automáticamente encripta la clave
                    'ESTADO' => 'A'
                ]);
            });
        }
        return view('login.index');

    }


    public function login(Request $request)
    {
        // Validar los campos del formulario
        $request->validate([
            'CORREO' => 'required|email',
            'CLAVE' => 'required'
        ]);

        // Extraer las credenciales
        $credentials = $request->only('CORREO', 'CLAVE');
        // Buscar el usuario manualmente
        $user = USUARIO::where('CORREO', $credentials['CORREO'])->where('ESTADO', 'A')->first();

        if ($user && Hash::check($credentials['CLAVE'], $user->CLAVE)) {
            // Iniciar sesión manualmente
            Auth::login($user);

            return redirect()->route('home.inicio')->with('success', 'Inicio de sesión exitoso.');
        }

        return redirect()->route('login.form')->with('error', 'Correo o contraseña incorrectos.');
    }


    public function logout()
    {
        Auth::logout();
        return redirect()->route('home.inicio')->with('success', 'Sesión cerrada exitosamente.');
    }
}
