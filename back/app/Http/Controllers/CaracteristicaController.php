<?php

namespace App\Http\Controllers;

use App\Models\CARACTERISTICA;
use Illuminate\Http\Request;

class CaracteristicaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'DESCRIPCION' => 'required|string',
        ]);

        CARACTERISTICA::create([
            'DESCRIPCION' => $request->input('DESCRIPCION'),
        ]);

        return redirect()->back()->with('success', 'Característica registrada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        $caracteristica = CARACTERISTICA::findOrFail($id);
        $previousUrl = url()->previous();
        return view('caracteristicas.edit', compact('caracteristica', 'previousUrl'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'DESCRIPCION' => 'required|string',
        ]);

        $caracteristica = CARACTERISTICA::findOrFail($id);
        $caracteristica->update([
            'DESCRIPCION' => $request->input('DESCRIPCION'),
        ]);

        // Redirigir a la URL desde la que se accedió al formulario de edición
        return redirect($request->input('previous_url'))->with('success', 'Característica actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
