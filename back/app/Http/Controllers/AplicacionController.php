<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AplicacionController extends Controller
{
    public function index()
    {
        return view('aplicacion.index');
    }
}
