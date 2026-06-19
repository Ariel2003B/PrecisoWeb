<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EMPRESA;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\Ruta;

class RutaController extends Controller
{
    public function rutasPorEmpresa(Request $request)
    {
        // 1. Obtener token del header
        $authHeader = $request->header('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Token no proporcionado'], 401);
        }

        // 2. Buscar token en base de datos
        $token = substr($authHeader, 7);
        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            return response()->json(['error' => 'Token inválido'], 401);
        }

        // 3. Obtener usuario autenticado desde el token
        $user = $accessToken->tokenable;

        // 4. Verificar si tiene empresa asociada
        if (!$user->EMP_ID) {
            return response()->json(['error' => 'Usuario no tiene empresa asociada'], 404);
        }

        // 5. Obtener rutas de la empresa
        $rutas = Ruta::where('EMP_ID', $user->EMP_ID)->get(['id_ruta', 'descripcion', 'valor_pasajero']);

        // 6. Devolver las rutas
        return response()->json($rutas);
    }


    public function index()
    {
        $rutas = Ruta::with('empresa')->get();
        return view('rutasapp.index', compact('rutas'));
    }

    public function create()
    {
        $empresas = EMPRESA::orderBy('NOMBRE')->get();
        return view('rutasapp.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion'    => 'required|string|max:255',
            'EMP_ID'         => 'nullable|exists:EMPRESA,EMP_ID',
            'valor_pasajero' => 'nullable|numeric|min:0',
        ]);

        Ruta::create($request->only(['descripcion', 'EMP_ID', 'valor_pasajero']));

        return redirect()->route('rutasapp.index')->with('success', 'Ruta creada exitosamente.');
    }

    public function edit($id)
    {
        $ruta = Ruta::findOrFail($id);
        $empresas = EMPRESA::orderBy('NOMBRE')->get();
        return view('rutasapp.edit', compact('ruta', 'empresas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'descripcion'    => 'required|string|max:255',
            'EMP_ID'         => 'nullable|exists:EMPRESA,EMP_ID',
            'valor_pasajero' => 'nullable|numeric|min:0',
        ]);

        $ruta = Ruta::findOrFail($id);
        $ruta->update($request->only(['descripcion', 'EMP_ID', 'valor_pasajero']));

        return redirect()->route('rutasapp.index')->with('success', 'Ruta actualizada exitosamente.');
    }


}
