<?php

namespace App\Http\Controllers;

use App\Models\HojaTrabajo;
use App\Models\Produccion;
use App\Models\Unidad;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HojaChoferController extends Controller
{
    // 1. Buscar hoja del día por unidad (desde QR)
    public function buscarPorUnidad($id_unidad)
    {
        $fecha = Carbon::now('America/Guayaquil')->format('Y-m-d');
        $hoja = HojaTrabajo::with(['ruta', 'conductor', 'gastos', 'producciones'])
            ->where('id_unidad', $id_unidad)
            ->where('fecha', $fecha)
            ->first();

        if (!$hoja) {
            // Obtener el último número de hoja no nulo y sumarle 1
            // $ultimoNumeroHoja = HojaTrabajo::whereNotNull('numero_hoja')
            //     ->orderBy('numero_hoja', 'desc')
            //     ->value('numero_hoja');
            // 🔗 Obtener EMP_ID a través de unidad → usuario → empresa
            $unidad = Unidad::with('usuario.empresa')->findOrFail($id_unidad);
            $empresaId = optional(optional($unidad->usuario)->empresa)->EMP_ID;

            // 🔄 Calcular el número de hoja de forma escalada por empresa
            $ultimoNumeroHoja = HojaTrabajo::whereNotNull('numero_hoja')
                ->whereHas('unidad.usuario', function ($q) use ($empresaId) {
                    $q->where('EMP_ID', $empresaId);
                })
                ->orderByDesc('numero_hoja')
                ->value('numero_hoja');

            $nuevoNumeroHoja = ($ultimoNumeroHoja ?? 0) + 1;

            // Si no existe, crear nueva hoja
            $hoja = HojaTrabajo::create([
                'fecha' => $fecha,
                'tipo_dia' => $this->getTipoDia(),
                'id_unidad' => $id_unidad,
                'id_conductor' => null,
                'id_ruta' => null,
                'ayudante_nombre' => null,
                'numero_hoja' => $nuevoNumeroHoja,
            ]);
        }

        return response()->json($hoja);
    }

    // 2. Actualizar producción (el chofer solo puede actualizar vueltas)
    public function actualizarProduccion(Request $request, $id)
    {
        foreach ($request->produccion as $vuelta) {
            Produccion::updateOrCreate(
                ['id_hoja' => $id, 'nro_vuelta' => $vuelta['nro_vuelta']],
                [
                    'hora_subida' => $vuelta['hora_subida'],
                    'hora_bajada' => $vuelta['hora_bajada'],
                    'valor_vuelta' => $vuelta['valor_vuelta'],
                ]

            );
        }
        return response()->json(['message' => 'Producción actualizada correctamente']);
    }

    // Detectar tipo de día automáticamente (opcional)
    private function getTipoDia()
    {
        $fechaActual = Carbon::now('America/Guayaquil')->format('Y-m-d');
        $anio = Carbon::now()->year;

        try {
            $response = Http::get("https://date.nager.at/api/v3/PublicHolidays/{$anio}/EC");

            if ($response->successful()) {
                $feriados = $response->json();

                foreach ($feriados as $feriado) {
                    if ($feriado['date'] === $fechaActual) {
                        return 'FERIADO';
                    }
                }
                $dia = Carbon::now('America/Guayaquil')->dayOfWeek;
                if ($dia === 0)
                    return 'DOMINGO';
                if ($dia === 6)
                    return 'SABADO';
                return 'LABORABLE';

            } else {
                // Si falla la API, usar lógica local
                return $this->tipoDiaFallback();
            }

        } catch (\Exception $e) {
            // En caso de error en la conexión
            return $this->tipoDiaFallback();
        }
    }

    private function tipoDiaFallback()
    {
        $dia = Carbon::now()->dayOfWeek;
        if ($dia === 0)
            return 'DOMINGO';
        if ($dia === 6)
            return 'SABADO';
        return 'LABORABLE';
    }
}
