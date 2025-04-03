<?php

namespace App\Http\Controllers;

use App\Models\EMPRESA;
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
        $permisos = PERMISO::where('ESTADO', 'A')->get();
        $empresas = EMPRESA::where('ESTADO', 'A')->get(); // Traer todas las empresas activas

        return view('usuario.create', compact('perfiles', 'permisos', 'empresas'));
    }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'NOMBRE' => 'required|string|max:255',
    //         'APELLIDO' => 'nullable|string|max:255',
    //         'CORREO' => 'required|email|unique:USUARIO,CORREO',
    //         'CLAVE' => 'required|min:6',
    //         'GENERO' => 'required|string',
    //         'CEDULA' => 'required|string|max:13',
    //         'EMP_ID' => 'integer',
    //         'permisos' => 'array'
    //     ]);

    //     $usuario = USUARIO::create([
    //         'NOMBRE' => $request->NOMBRE,
    //         'APELLIDO' => $request->APELLIDO,
    //         'CORREO' => $request->CORREO,
    //         'CLAVE' => $request->CLAVE,
    //         'ESTADO' => 'A',
    //         'TOKEN' => $request->TOKEN,
    //         'DEPOT' => $request->DEPOT,
    //         'GENERO' => $request->GENERO,
    //         'CEDULA' => $request->CEDULA,
    //         'EMP_ID' => $request->EMP_ID
    //     ]);

    //     if ($request->has('permisos')) {
    //         $usuario->permisos()->sync($request->permisos);
    //     }

    //     return redirect()->route('usuario.index')->with('success', 'Usuario creado exitosamente.');
    // }


    public function store(Request $request)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'APELLIDO' => 'nullable|string|max:255',
            'CORREO' => 'required|email|unique:USUARIO,CORREO',
            'CLAVE' => 'required|min:6',
            'GENERO' => 'required|string',
            'CEDULA' => 'required|string|max:13',
            'permisos' => 'array',
            'TELEFONO'=>'required|string'
        ]);

        $usuario = USUARIO::create([
            'NOMBRE' => $request->NOMBRE,
            'APELLIDO' => $request->APELLIDO,
            'CORREO' => $request->CORREO,
            'CLAVE' => $request->CLAVE,
            'ESTADO' => 'A',
            'TOKEN' => $request->TOKEN,
            'DEPOT' => $request->DEPOT,
            'GENERO' => $request->GENERO,
            'CEDULA' => $request->CEDULA,
            'EMP_ID' => $request->EMP_ID,
            'TELEFONO' => $request->TELEFONO
        ]);

        $permisos = $request->has('permisos') ? PERMISO::whereIn('PRM_ID', $request->permisos)->pluck('DESCRIPCION')->toArray() : [];
        $listaPermisos = implode(", ", $permisos);

        if ($request->has('permisos')) {
            $usuario->permisos()->sync($request->permisos);
        }

        // Datos para el correo
        $para = $request->CORREO;
        $asunto = 'Bienvenido a PrecisoGPS - Credenciales de acceso';

        $logoUrl = "https://precisogps.com/img/Precisogps.png";

        $mensaje = "
            <html>
            <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #ddd;'>
                    <div style='text-align: center;'>
                        <img src='{$logoUrl}' alt='PrecisoGPS' style='width: 200px; margin-bottom: 20px;'>
                    </div>
                    <h2 style='text-align: center; color: #007bff;'>Bienvenido a PrecisoGPS</h2>
                    <p>Hola, <strong>{$request->NOMBRE} {$request->APELLIDO}</strong>,</p>
                    <p>Gracias por registrarte en nuestra plataforma. A continuación te proporcionamos tus credenciales de acceso:</p>
                    <table style='width: 100%; margin-bottom: 20px; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px; background-color: #f0f0f0;'>Usuario:</td>
                            <td style='padding: 8px;'>{$request->CEDULA}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; background-color: #f0f0f0;'>Contraseña:</td>
                            <td style='padding: 8px;'>{$request->CLAVE}</td>
                        </tr>
                    </table>
                    <h3>Tus servicios activos son:</h3>
                    <ul style='padding-left: 20px;'>";

        foreach ($permisos as $permiso) {
            $mensaje .= "<li>{$permiso}</li>";
        }

        $mensaje .= "
                    </ul>
                    <p style='color: #555;'>Si tienes alguna duda o necesitas asistencia, no dudes en contactarnos.</p>
                    <p style='text-align: center;'><strong>¡Te damos la bienvenida!</strong></p>
                    <div style='background-color: #000; color: #fff; padding: 20px; margin-top: 20px; border-radius: 8px;'>
                        <h3 style='margin: 0; text-align: center;'>PrecisoGPS</h3>
                        <p style='margin: 0; text-align: center;'>E16 N53-209 y de los Cholanes<br>Quito, 170514</p>
                        <p style='margin: 0; text-align: center;'><strong>Celular:</strong> +593 99 045 3275</p>
                        <p style='margin: 0; text-align: center;'><strong>Correo:</strong> ventas@precisogps.com</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $cabeceras = "MIME-Version: 1.0\r\n";
        $cabeceras .= "Content-type: text/html; charset=UTF-8\r\n";
        $cabeceras .= "From: suscripciones@soporte.precisogps.com\r\n";
        $cabeceras .= "Reply-To: suscripciones@soporte.precisogps.com\r\n";

        // Enviar el correo
        mail($para, $asunto, $mensaje, $cabeceras);

        return redirect()->route('usuario.index')->with('success', 'Usuario creado exitosamente. Correo enviado.');
    }




    public function edit(USUARIO $usuario)
    {
        $perfiles = PERFIL::where('ESTADO', 'A')->get();
        $permisos = PERMISO::where('ESTADO', 'A')->get();
        $empresas = EMPRESA::where('ESTADO', 'A')->get(); // Traer todas las empresas activas
        $usuarioPermisos = $usuario->permisos->pluck('PRM_ID')->toArray(); // Obtener permisos actuales del usuario

        return view('usuario.edit', compact('usuario', 'perfiles', 'permisos', 'usuarioPermisos', 'empresas'));
    }

    public function update(Request $request, USUARIO $usuario)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'APELLIDO' => 'nullable|string|max:255',
            'CORREO' => 'required|email|unique:USUARIO,CORREO,' . $usuario->USU_ID . ',USU_ID',
            'CLAVE' => 'nullable|min:6', // Permite clave nula
            'GENERO' => 'required|string',
            'CEDULA' => 'required|string|max:13',
            'permisos' => 'array'
        ]);

        $updateData = [
            'NOMBRE' => $request->NOMBRE,
            'APELLIDO' => $request->APELLIDO,
            'CORREO' => $request->CORREO,
            'TOKEN' => $request->TOKEN,
            'DEPOT' => $request->DEPOT,
            'GENERO' => $request->GENERO,
            'CEDULA' => $request->CEDULA,
            'EMP_ID' => $request->EMP_ID,
            'TELEFONO' => $request->TELEFONO
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
