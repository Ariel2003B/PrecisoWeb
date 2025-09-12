<?php

namespace App\Http\Controllers;

use App\Models\EMPRESA;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = EMPRESA::all();
        return view('empresa.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:500',
            'RUC' => 'required|string|max:13|unique:EMPRESA,RUC',
            'DIRECCION' => 'nullable|string|max:500',
            'TELEFONO' => 'nullable|string|max:20',
            'CORREO' => 'nullable|email|max:600',
            'ESTADO' => 'required|string|max:1',
            'IMAGEN' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

        ]);

        $data = $request->all();

        if ($request->hasFile('IMAGEN')) {
            $file = $request->file('IMAGEN');
            $ruta = $file->store('empresa', 'public');
            $data['IMAGEN'] = $ruta;
        }

        EMPRESA::create($data);


        return redirect()->route('empresa.index')->with('success', 'Empresa creada exitosamente.');
    }

    public function edit(EMPRESA $empresa)
    {
        return view('empresa.edit', compact('empresa'));
    }

    public function update(Request $request, EMPRESA $empresa)
    {
        $request->validate([
            'NOMBRE' => 'required|string|max:500',
            'RUC' => 'required|string|max:13|unique:EMPRESA,RUC,' . $empresa->EMP_ID . ',EMP_ID',
            'DIRECCION' => 'nullable|string|max:500',
            'TELEFONO' => 'nullable|string|max:20',
            'CORREO' => 'nullable|email|max:600',
            'ESTADO' => 'required|string|max:1',
            'IMAGEN' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
        ]);

        $data = $request->all();

        if ($request->hasFile('IMAGEN')) {
            $file = $request->file('IMAGEN');
            $ruta = $file->store('empresa', 'public');
            $data['IMAGEN'] = $ruta;
        }

        $empresa->update($data);

        return redirect()->route('empresa.index')->with('success', 'Empresa actualizada exitosamente.');
    }

    public function destroy(EMPRESA $empresa)
    {
        $empresa->delete();
        return redirect()->route('empresa.index')->with('success', 'Empresa eliminada exitosamente.');
    }
}
