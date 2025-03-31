<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HojaTrabajo;
use App\Models\ProduccionUsuario;
use Illuminate\Support\Facades\Auth;

class ReporteProduccionController extends Controller
{
    public function index(Request $request)
    {
        $query = HojaTrabajo::with('unidad', 'ruta')->orderBy('fecha', 'desc');
    
        if ($request->filled('fecha')) {
            $query->where('fecha', $request->fecha);
        }
    
        if ($request->filled('ruta')) {
            $query->whereHas('ruta', function ($q) use ($request) {
                $q->where('descripcion', 'like', '%' . $request->ruta . '%');
            });
        }
    
        if ($request->filled('unidad')) {
            $query->whereHas('unidad', function ($q) use ($request) {
                $q->where('placa', 'like', '%' . $request->unidad . '%');
            });
        }
    
        $hojas = $query->get();
    
        return view('reportes.index', compact('hojas'));
    }
    
    public function create($id)
    {
        $hoja = HojaTrabajo::with('unidad', 'ruta')->findOrFail($id);
    
        // Obtener vueltas que ya registró este usuario para esta hoja
        $registros = ProduccionUsuario::where('id_hoja', $id)
            ->where('usu_id', Auth::user()->USU_ID)
            ->orderBy('nro_vuelta')
            ->get();
    
        // Calcular el contador inicial basándose en el último número de vuelta registrado
        $contador = $registros->isEmpty() ? 1 : $registros->max('nro_vuelta') + 1;
    
        return view('reportes.create', compact('hoja', 'registros', 'contador'));
    }
    
    
    public function store(Request $request)
    {
        $request->validate([
            'id_hoja' => 'required|exists:hojas_trabajo,id_hoja',
            'reportes' => 'required|array|min:1',
            'reportes.*.nro_vuelta' => 'required|integer|min:1',
            'reportes.*.pasaje_completo' => 'required|integer|min:0',
            'reportes.*.pasaje_medio' => 'required|integer|min:0',
        ]);

        foreach ($request->reportes as $reporte) {
            $valor = ($reporte['pasaje_completo'] * 0.35) + ($reporte['pasaje_medio'] * 0.17);
        
            ProduccionUsuario::updateOrCreate(
                [
                    'id_hoja' => $request->id_hoja,
                    'nro_vuelta' => $reporte['nro_vuelta'],
                    'usu_id' => Auth::user()->USU_ID
                ],
                [
                    'pasaje_completo' => $reporte['pasaje_completo'],
                    'pasaje_medio' => $reporte['pasaje_medio'],
                    'valor_vuelta' => $valor
                ]
            );
        }
        

        return redirect()->route('reportes.index')->with('success', 'Reporte guardado con éxito.');
    }
}
