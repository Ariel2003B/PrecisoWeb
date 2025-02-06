<?php

namespace App\Http\Controllers;

use App\Models\BLOG;
use App\Models\EQUIPO_ACCESORIO;
use App\Models\PLAN;
use App\Models\RESPUESTUM;
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
    public function equipos()
    {
        $equipos = EQUIPO_ACCESORIO::all();
        return view('home.equipos',compact('equipos'));
    }
    public function servicios()
    {
        return view('home.servicios');
    }
    public function planes()
    {
        // Obtener todos los planes junto con sus características
        $planes = PLAN::with(['c_a_r_a_c_t_e_r_i_s_t_i_c_a_s'])->get();
        return view('home.planes', compact('planes'));
    }
    public function nosotros()
    {
        return view('home.nosotros');
    }
    public function blog()
    {
        $blogs = BLOG::orderBy('FECHACREACION', 'desc')->paginate(6); // Paginamos 6 blogs por página
        return view('home.blog', compact('blogs'));
    }

    public function detailsBlog($id)
    {
        $blog = BLOG::with('s_u_b_t_i_t_u_l_o_s', 'r_e_s_p_u_e_s_t_a')->findOrFail($id);
        $comentarios = RESPUESTUM::where('BLO_ID', $id)->orderBy('FECHACREACION', 'desc')->get();
        $recientes = BLOG::orderBy('FECHACREACION', 'desc')->limit(5)->get();

        return view('home.blog-details', compact('blog', 'comentarios', 'recientes'));
    }




}
