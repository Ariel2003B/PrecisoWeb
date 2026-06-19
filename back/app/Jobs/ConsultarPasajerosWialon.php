<?php

namespace App\Jobs;

use App\Models\HojaTrabajo;
use App\Services\WialonService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConsultarPasajerosWialon implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30;

    public function __construct(public readonly int $hojaId)
    {
    }

    public function handle(WialonService $wialon): void
    {
        $hoja = HojaTrabajo::with(['unidad', 'ruta', 'producciones'])->find($this->hojaId);

        if (!$hoja) {
            Log::warning('ConsultarPasajerosWialon: hoja no encontrada', ['hoja_id' => $this->hojaId]);
            return;
        }

        $idWialon = $hoja->unidad->idWialon ?? null;
        if (!$idWialon) {
            Log::info('ConsultarPasajerosWialon: unidad sin idWialon, se omite', ['hoja_id' => $this->hojaId]);
            return;
        }

        $valorPasajero = (float)($hoja->ruta->valor_pasajero ?? 0);
        $fecha         = $hoja->fecha; // Y-m-d
        $tz            = new \DateTimeZone('America/Guayaquil');

        $sid = $wialon->login();

        foreach ($hoja->producciones as $prod) {
            try {
                // Toma solo HH:MM sin importar si la BD guarda HH:MM:SS
                $horaSubida = substr($prod->hora_subida, 0, 5);
                $horaBajada = substr($prod->hora_bajada, 0, 5);

                $inicio = Carbon::createFromFormat('Y-m-d H:i', "{$fecha} {$horaSubida}", $tz)->timestamp;
                $fin    = Carbon::createFromFormat('Y-m-d H:i', "{$fecha} {$horaBajada}", $tz)->timestamp;

                // Si la bajada cae al día siguiente (ruta de madrugada)
                if ($fin <= $inicio) {
                    $fin = Carbon::createFromFormat('Y-m-d H:i', "{$fecha} {$prod->hora_bajada}", $tz)
                        ->addDay()
                        ->timestamp;
                }

                $resultado = $wialon->contarPasajeros($sid, (int)$idWialon, $inicio, $fin);

                $prod->update([
                    'pasajeros_subida' => $resultado['upp'],
                    'pasajeros_bajada' => $resultado['downp'],
                    'valor_pasajeros'  => round($resultado['upp'] * $valorPasajero, 2),
                ]);

                Log::info('Pasajeros vuelta actualizados', [
                    'hoja_id'    => $this->hojaId,
                    'prod_id'    => $prod->id_produccion,
                    'nro_vuelta' => $prod->nro_vuelta,
                    'upp'        => $resultado['upp'],
                    'downp'      => $resultado['downp'],
                    'valor'      => round($resultado['upp'] * $valorPasajero, 2),
                ]);
            } catch (\Throwable $e) {
                Log::error('ConsultarPasajerosWialon: error en vuelta', [
                    'hoja_id'    => $this->hojaId,
                    'prod_id'    => $prod->id_produccion ?? null,
                    'nro_vuelta' => $prod->nro_vuelta ?? null,
                    'mensaje'    => $e->getMessage(),
                ]);
                // No relanzamos: si una vuelta falla, seguimos con las demás
            }
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::critical('ConsultarPasajerosWialon: job fallido definitivamente', [
            'hoja_id' => $this->hojaId,
            'mensaje' => $e->getMessage(),
        ]);
    }
}
