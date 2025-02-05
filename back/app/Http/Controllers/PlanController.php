<?php

namespace App\Http\Controllers;

use App\Models\CARACTERISTICA;
use App\Models\PERFIL;
use App\Models\PLAN;
use App\Models\PLANCARACTERISTICA;
use App\Models\USUARIO;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        // Obtener todos los planes junto con sus características
        $planes = PLAN::with(['c_a_r_a_c_t_e_r_i_s_t_i_c_a_s'])->get();

        // Pasar los datos a la vista
        return view('plan.index', compact('planes'));
    }

    public function create()
    {
        // Obtener todas las características disponibles desde la base de datos
        $caracteristicas = CARACTERISTICA::all();
        // Retornar la vista de creación de planes con las características
        return view('plan.create', compact('caracteristicas'));
    }


    public function store(Request $request)
    {
        // Validar los datos básicos del plan
        $validated = $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'DESCRIPCION' => 'required|string',
            'PRECIO' => 'required|numeric|min:0',
            'TIEMPO' => 'required|string',
        ]);

        // Crear el plan
        $plan = PLAN::create([
            'NOMBRE' => $validated['NOMBRE'],
            'DESCRIPCION' => $validated['DESCRIPCION'],
            'PRECIO' => $validated['PRECIO'],
            'TIEMPO' => $validated['TIEMPO'],
        ]);

        // Asociar características seleccionadas con su orden
        if ($request->has('caracteristicas')) {
            foreach ($request->input('caracteristicas') as $caracteristicaId => $value) {
                PLANCARACTERISTICA::create([
                    'PLA_ID' => $plan->PLA_ID,
                    'CAR_ID' => $caracteristicaId,
                    'POSEE' => $request->input("posee.$caracteristicaId") == 1, // True si el switch está activado
                    'ORDEN' => $request->input("orden.$caracteristicaId") ?? 999, // Si no ingresa orden, poner 999 por defecto
                ]);
            }
        }

        return redirect()->route('plan.index')->with('success', 'Plan creado exitosamente.');
    }

    public function edit($id)
    {
        // Obtener el plan junto con sus características ordenadas por ORDEN
        $plan = PLAN::with([
            'c_a_r_a_c_t_e_r_i_s_t_i_c_a_s' => function ($query) {
                $query->orderBy('PLANCARACTERISTICA.ORDEN', 'asc');
            }
        ])->findOrFail($id);

        // Obtener todas las características disponibles
        $caracteristicas = CARACTERISTICA::all();

        // Retornar la vista con el plan y las características
        return view('plan.edit', compact('plan', 'caracteristicas'));
    }


    public function update(Request $request, $id)
    {
        // Validar los datos del formulario
        $request->validate([
            'NOMBRE' => 'required|string|max:255',
            'DESCRIPCION' => 'required|string',
            'PRECIO' => 'required|numeric|min:0',
            'TIEMPO' => 'required|integer|min:1',
        ]);

        // Buscar el plan
        $plan = PLAN::findOrFail($id);

        // Actualizar los datos del plan
        $plan->update([
            'NOMBRE' => $request->input('NOMBRE'),
            'DESCRIPCION' => $request->input('DESCRIPCION'),
            'PRECIO' => $request->input('PRECIO'),
            'TIEMPO' => $request->input('TIEMPO'),
        ]);

        // Actualizar las características del plan
        $plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s()->detach(); // Eliminar las existentes

        if ($request->has('caracteristicas')) {
            foreach ($request->input('caracteristicas') as $caracteristicaId => $value) {
                $plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s()->attach($caracteristicaId, [
                    'POSEE' => $request->input("posee.$caracteristicaId") == 1, // True si el switch está activado
                    'ORDEN' => $request->input("orden.$caracteristicaId") ?? 999, // Si no ingresa orden, poner 999 por defecto
                ]);
            }
        }

        return redirect()->route('plan.index')->with('success', 'Plan actualizado exitosamente.');
    }


    public function destroy($id)
    {
        // Buscar el plan por su ID
        $plan = PLAN::findOrFail($id);

        // Eliminar las relaciones con características
        $plan->c_a_r_a_c_t_e_r_i_s_t_i_c_a_s()->detach();

        // Eliminar el plan
        $plan->delete();

        // Redirigir con un mensaje de éxito
        return redirect()->route('plan.index')->with('success', 'Plan eliminado exitosamente.');
    }


}
