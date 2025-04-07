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
        $query = HojaTrabajo::with('unidad', 'ruta');

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
                $q->where('placa', 'like', '%' . $request->unidad . '%')
                    ->orWhere('numero_habilitacion', 'like', '%' . $request->unidad . '%');
            });
        }

        // Ordenar por fecha descendente y por número de habilitación ascendente
        $hojas = $query->get()->sortBy([
            ['fecha', 'desc'],
            [
                function ($hoja) {
                    if ($hoja->unidad && $hoja->unidad->numero_habilitacion) {
                        preg_match('/^(\d+)/', $hoja->unidad->numero_habilitacion, $matches);
                        return intval($matches[1] ?? PHP_INT_MAX);
                    }
                    return PHP_INT_MAX;
                }
            ]
        ]);

        return view('reportes.index', compact('hojas'));
    }



    public function create($id)
    {

        $user = Auth::user();
        $permisoLectura = $user->permisos()->where('DESCRIPCION', 'LECTURA')->exists();

        $hoja = HojaTrabajo::with('unidad', 'ruta')->findOrFail($id);

        // Obtener vueltas que ya registró este usuario para esta hoja
        $registros = ProduccionUsuario::where('id_hoja', $id)
            ->where('usu_id', Auth::user()->USU_ID)
            ->orderBy('nro_vuelta')
            ->get();

        // Si hay registros, obtener el último número de vuelta registrado, sino inicia desde 1
        $ultimoNumeroVuelta = $registros->max('nro_vuelta') ?? 0;
        $contador = $ultimoNumeroVuelta + 1; // Este es el próximo número de vuelta disponible

        return view('reportes.create', compact('hoja', 'registros', 'contador', 'permisoLectura'));
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


    public function generarReporteGlobal(Request $request)
    {
        $fecha = $request->input('fecha');

        $hojas = HojaTrabajo::with(['unidad', 'producciones'])
            ->whereDate('fecha', $fecha)
            ->get();

        $produccionPorUnidad = [];
        $totalGlobal = 0;

        foreach ($hojas as $hoja) {
            $unidadKey = $hoja->unidad->placa . ' (' . $hoja->unidad->numero_habilitacion . ')';
            $totalUnidad = $hoja->producciones->sum('valor_vuelta');

            if (!isset($produccionPorUnidad[$unidadKey])) {
                $produccionPorUnidad[$unidadKey] = 0;
            }

            $produccionPorUnidad[$unidadKey] += $totalUnidad;
            $totalGlobal += $totalUnidad;
        }

        $result = view('partials.reporte_global', compact('produccionPorUnidad', 'totalGlobal'))->render();

        return response()->json(['html' => $result]);
    }


}
