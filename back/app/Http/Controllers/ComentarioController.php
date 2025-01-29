<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RESPUESTUM;

class ComentarioController extends Controller
{
    public function store(Request $request)
    {
        // Validar los datos
        $request->validate([
            'BLO_ID' => 'required|exists:BLOG,BLO_ID',
            'RES_RES_ID' => 'nullable|exists:RESPUESTA,RES_ID', // Permitir respuestas anidadas
            'AUTOR' => 'required|string|max:255',
            'DESCRIPCION' => 'required|string',
        ]);

        // Guardar el comentario o respuesta
        RESPUESTUM::create([
            'BLO_ID' => $request->BLO_ID,
            'RES_RES_ID' => $request->RES_RES_ID, // Puede ser NULL si es un comentario nuevo
            'AUTOR' => $request->AUTOR,
            'DESCRIPCION' => $request->DESCRIPCION,
            'FECHACREACION' => now()
        ]);

        return redirect()->back()->with('success', 'Comentario agregado correctamente.');
    }
}
