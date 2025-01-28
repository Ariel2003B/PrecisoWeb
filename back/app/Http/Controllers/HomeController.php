<?php

namespace App\Http\Controllers;

use App\Models\PLAN;
use App\Models\SIMCARD;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function inicio()
    {
        return view('home.index');
    }
    public function privacidad()
    {
        return view('home.politica');
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
        // Obtener todos los planes junto con sus características
        $planes = PLAN::with(['c_a_r_a_c_t_e_r_i_s_t_i_c_a_s'])->get();
        return view('home.planes',compact('planes'));
    }
    public function nosotros()
    {
        return view('home.nosotros');
    }
    public function blog()
    {
        return view('home.blog');
    }

}
