<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function inicio()
    {
        return view('home.index');
    }

    public function privacidad()
    {
        return view('home.privacy');
    }
    public function plataformas()
    {
        return view('home.plataformas');
    }
    public function servicios()
    {
        return view('home.servicios');
    }
    public function planes()
    {
        return view('home.planes');
    }
    public function nosotros()
    {
        return view('home.nosotros');
    }

}
