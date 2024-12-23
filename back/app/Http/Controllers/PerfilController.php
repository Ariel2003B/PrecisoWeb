<?php

namespace App\Http\Controllers;

use App\Models\PERFIL;
use App\Models\PERMISO;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index()
    {
        $perfiles = PERFIL::where('ESTADO', 'A')->get();
        return view('perfil.list', compact('perfiles'));
    }


    public function create()
    {
        $permisos = PERMISO::where('ESTADO', 'A')->get();
        return view('perfil.create', compact('permisos'));

    }

    public function store(Request $request)
    {

    }
    public function edit(PERFIL $perfil)
    {
        return view('perfil.edit', compact('perfil'));
    }

    public function update(Request $request, PERFIL $empresa)
    {
        

        return redirect()->route('perfil.list');
    }

    public function destroy(PERFIL $perfil)
    {
        $estado = ['ESTADO' => 'I'];
        $perfil->update($estado);
        return redirect()->route('perfil.list');
    }



}
