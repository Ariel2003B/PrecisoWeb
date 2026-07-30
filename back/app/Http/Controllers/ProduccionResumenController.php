<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HojaTrabajo;
use App\Models\Unidad;
use App\Models\TicketTipo;

/**
 * Endpoint nuevo y aditivo para la App Flota de PrecisoBus (Fase 9).
 * No modifica ReporteProduccionController ni HojaTrabajoController.
 *
 * Reproduce, para una sola unidad + fecha, el mismo cálculo que ya usa el
 * panel web en reportes/recaudo.blade.php (Prod. Conductor vs Prod. Tickets,
 * diferencias, tarifa promedio) más los gastos de hoja_trabajo/ver.blade.php,
 * que el panel web no expone combinados en un único endpoint.
 */
class ProduccionResumenController extends Controller
{
    public function resumen(Request $request)
    {
        $request->validate([
            'placa' => 'required|string',
            'fecha' => 'required|date',
        ]);

        $user = $request->user();

        $unidad = Unidad::where('placa', $request->input('placa'))->first();
        if (!$unidad) {
            return response()->json(['message' => 'Unidad no encontrada'], 404);
        }

        $hojas = HojaTrabajo::with(['producciones.tickets.ticketTipo', 'gastos'])
            ->where('id_unidad', $unidad->id_unidad)
            ->where('fecha', $request->input('fecha'))
            ->get();

        $ticketTipos = TicketTipo::where('EMP_ID', $user->EMP_ID)
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();
        $hayTickets = $ticketTipos->count() > 0;

        $ticketsPorTipo = [];
        foreach ($ticketTipos as $tt) {
            $ticketsPorTipo[$tt->id] = [
                'tipoId' => $tt->id,
                'nombre' => $tt->nombre,
                'valorUnitario' => (float) $tt->valor,
                'cantidad' => 0,
                'subtotal' => 0.0,
            ];
        }

        $totalVueltas = 0;
        $produccionConductor = 0.0;
        $contPasajeros = 0;
        $ticketsFisicos = 0;
        $produccionTickets = 0.0;

        $gastosPorTipo = [];
        $totalGastos = 0.0;

        foreach ($hojas as $hoja) {
            foreach ($hoja->producciones as $produccion) {
                $totalVueltas++;
                $produccionConductor += (float) $produccion->valor_vuelta;
                $contPasajeros += (int) ($produccion->pasajeros_subida ?? 0);

                foreach ($produccion->tickets as $pt) {
                    $tipoId = $pt->id_ticket_tipo;
                    if (!isset($ticketsPorTipo[$tipoId])) continue;
                    $cantidad = max(0, $pt->numero_fin - $pt->numero_inicio + 1);
                    $valor = $cantidad * ($ticketsPorTipo[$tipoId]['valorUnitario'] ?? 0);
                    $ticketsPorTipo[$tipoId]['cantidad'] += $cantidad;
                    $ticketsPorTipo[$tipoId]['subtotal'] += $valor;
                    $ticketsFisicos += $cantidad;
                    $produccionTickets += $valor;
                }
            }

            foreach ($hoja->gastos as $gasto) {
                $tipo = $gasto->tipo_gasto;
                $gastosPorTipo[$tipo] = ($gastosPorTipo[$tipo] ?? 0) + (float) $gasto->valor;
                $totalGastos += (float) $gasto->valor;
            }
        }

        $difPasajeros = $contPasajeros - $ticketsFisicos;
        $difDinero    = $produccionTickets - $produccionConductor;
        $tarifaPromedio = $contPasajeros > 0 ? $produccionConductor / $contPasajeros : 0;

        return response()->json([
            'placa' => $unidad->placa,
            'fecha' => $request->input('fecha'),
            'totalVueltas' => $totalVueltas,
            'produccionConductor' => round($produccionConductor, 2),
            'hayTickets' => $hayTickets,
            'produccionTickets' => round($produccionTickets, 2),
            'ticketsFisicos' => $ticketsFisicos,
            'contPasajeros' => $contPasajeros,
            'difPasajeros' => $difPasajeros,
            'difDinero' => round($difDinero, 2),
            'tarifaPromedio' => round($tarifaPromedio, 2),
            'ticketsPorTipo' => array_values(array_map(fn($t) => [
                'tipoId' => $t['tipoId'],
                'nombre' => $t['nombre'],
                'valorUnitario' => round($t['valorUnitario'], 2),
                'cantidad' => $t['cantidad'],
                'subtotal' => round($t['subtotal'], 2),
            ], array_filter($ticketsPorTipo, fn($t) => $t['cantidad'] > 0))),
            'gastos' => collect($gastosPorTipo)->map(fn($valor, $tipo) => [
                'tipo' => $tipo,
                'valor' => round($valor, 2),
            ])->values(),
            'totalGastos' => round($totalGastos, 2),
            'totalADepositar' => round($produccionConductor - $totalGastos, 2),
        ]);
    }
}
