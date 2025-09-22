<?php

namespace App\Http\Controllers;

use App\Models\EMPRESA;
use App\Models\GeoStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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


    /**
     * Muestra formulario con todas las paradas (traídas de tu API) + los valores ya guardados.
     * GET /empresa/{empresa}/stops
     */
    public function stopsForm(EMPRESA $empresa, Request $request)
    {
        // Validación rápida
        if (empty($empresa->TOKEN) || empty($empresa->DEPOT)) {
            return back()->with('error', 'Para configurar geocercas debes guardar TOKEN y DEPOT en la empresa.');
        }

        // Puedes sobreescribir la base por query ?base=http://... si quieres
        $base = rtrim($request->query('base', 'http://159.203.177.210:443'), '/');
        $api = $base . '/api/stops/stops';

        // Llamado a tu API .NET
        $resp = Http::acceptJson()
            ->timeout(30)
            ->post($api, [
                'token' => $empresa->TOKEN,
                'depot' => (int) $empresa->DEPOT,
                'fecha' => '2025-09-16'
            ]);

        if (!$resp->ok()) {
            return back()->with('error', 'No se pudo obtener paradas desde Nimbus (' . $resp->status() . ').');
        }

        $stopsApi = collect($resp->json() ?? [])
            ->filter(fn($s) => isset($s['id'], $s['n']))     // saneo
            ->map(fn($s) => ['id' => (int) $s['id'], 'n' => (string) $s['n']]);

        // Traer lo que ya tengamos guardado para prellenar
        $tarifas = GeoStop::deEmpresa($empresa->EMP_ID)
            ->pluck('VALOR_MINUTO', 'NIMBUS_ID')
            ->map(fn($v) => (float) $v);

        // Merge (orden natural por nombre)
        $rows = $stopsApi
            ->map(function ($s) use ($tarifas) {
                $nid = $s['id'];
                return [
                    'nimbus_id' => $nid,
                    'nombre' => $s['n'],
                    'valor' => $tarifas->get($nid, 0.0), // 0.0 si no hay registro previo
                ];
            })
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();

        return view('empresa.stops', [
            'empresa' => $empresa,
            'rows' => $rows,
            'base' => $base,
        ]);
    }

    /**
     * Guarda los valores por minuto por geocerca usando upsert.
     * POST /empresa/{empresa}/stops/save
     */
    public function stopsSave(Request $request, EMPRESA $empresa)
    {
        $payload = $request->input('stops', []); // stops[nimbusId][valor], stops[nimbusId][nombre]

        if (!is_array($payload) || empty($payload)) {
            return back()->with('error', 'No se recibieron datos para guardar.');
        }

        // 1) Preparar filas e IDs presentes en el formulario
        $rows = [];
        $presentIds = [];

        foreach ($payload as $nimbusId => $row) {
            $nimbusId = (int) $nimbusId;
            $presentIds[] = $nimbusId;

            $valor = isset($row['valor']) ? (float) $row['valor'] : 0.0;
            $nombre = isset($row['nombre']) ? Str::limit((string) $row['nombre'], 180, '') : '';

            if ($valor < 0)
                $valor = 0.0; // sin negativos

            $rows[] = [
                'EMP_ID' => (int) $empresa->EMP_ID,
                'NIMBUS_ID' => $nimbusId,
                'NOMBRE' => $nombre,
                'DEPOT' => (int) $empresa->DEPOT,
                'VALOR_MINUTO' => $valor,
                'ESTADO' => 'A', // las presentes quedan activas
            ];
        }

        // 2) Sincronizar usando transacción
        DB::transaction(function () use ($empresa, $rows, $presentIds) {

            // 2.1) Borrar TODO lo que ya no esté presente en el submit
            //     (si prefieres desactivar en lugar de borrar, ver bloque alternativo más abajo)
            GeoStop::where('EMP_ID', (int) $empresa->EMP_ID)
                ->when(!empty($presentIds), function ($q) use ($presentIds) {
                    $q->whereNotIn('NIMBUS_ID', $presentIds);
                }, function ($q) {
                    // si por algún motivo la lista vino vacía, elimina todos los registros de esa empresa
                    $q->whereRaw('1 = 1');
                })
                ->delete();

            // 2.2) Upsert (insert/update) de las presentes
            if (!empty($rows)) {
                GeoStop::upsert(
                    $rows,
                    ['EMP_ID', 'NIMBUS_ID'],                 // claves únicas/compuestas
                    ['NOMBRE', 'DEPOT', 'VALOR_MINUTO', 'ESTADO'] // columnas a actualizar
                );
            }
        });

        return redirect()
            ->route('empresa.stops.form', $empresa->EMP_ID)
            ->with('success', 'Geocercas sincronizadas y guardadas correctamente.');
    }

}
