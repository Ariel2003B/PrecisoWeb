<?php

namespace App\Http\Controllers;

use App\Models\PERMISO;
use Illuminate\Http\Request;

class PermisoController extends Controller
{
    public function store(Request $request)
    {
        // Crear el permiso
        PERMISO::create([
            'DESCRIPCION' => $request->DESCRIPCION
        ]);

        return redirect()->back()->with('success', 'Permiso creado con éxito.');
    }   
}
