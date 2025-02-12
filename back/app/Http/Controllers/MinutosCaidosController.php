<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\USUARIO;
use App\Models\SIMCARD;
use Illuminate\Support\Facades\Auth;

class MinutosCaidosController extends Controller
{
    // public function index()
    // {
    //     // Obtener el usuario autenticado y su depot
    //     $usuario = Auth::user();
    //     if (!$usuario) {
    //         return redirect()->route('login')->with('error', 'Debes iniciar sesión');
    //     }

    //     $token = $usuario->TOKEN;
    //     $depot = $usuario->DEPOT;

    //     // URLs de la API Nimbus con el depot del usuario
    //     $url_routes = "https://nimbus.wialon.com/api/depot/{$depot}/routes";
    //     $url_stops = "https://nimbus.wialon.com/api/depot/{$depot}/stops";
    //     $url_rides = "https://nimbus.wialon.com/api/depot/{$depot}/rides";

    //     // Obtener datos de la API
    //     $routes_data = $this->getApiData($url_routes, $token);
    //     $stops_data = $this->getApiData($url_stops, $token);
    //     $rides_data = $this->getApiData($url_rides, $token);

    //     if (!$routes_data || !isset($routes_data['routes']) || !$stops_data || !isset($stops_data['stops']) || !$rides_data || !isset($rides_data['rides'])) {
    //         return back()->with('error', 'No se pudo obtener la información de Nimbus');
    //     }

    //     // Obtener las unidades y asociarlas con su nombre (ASIGNACION)
    //     $simcards = SIMCARD::pluck('ASIGNACION', 'ID_WIALON');

    //     // Mapeo de paradas
    //     $stops_map = [];
    //     foreach ($stops_data['stops'] as $stop) {
    //         $stops_map[$stop['id']] = $stop['n'];
    //     }

    //     // Mapeo de rutas por ID
    //     $routes_map = [];
    //     foreach ($routes_data['routes'] as $route) {
    //         if (!isset($route['tt']))
    //             continue;
    //         foreach ($route['tt'] as $timetable) {
    //             $tid = $timetable['id'];
    //             $routes_map[$tid] = [
    //                 'route_name' => $route['n'],
    //                 'stops' => $route['st'] ?? []
    //             ];
    //         }
    //     }

    //     // Agrupar viajes por ruta (solo rides activos o en tracking)
    //     $grouped_rides = [];
    //     foreach ($rides_data['rides'] as $ride) {
    //         $tid = $ride['tid'] ?? null;
    //         $flags = $ride['f'] ?? 0;

    //         // Filtrar solo rides con flag 'tracking' o 'active'
    //         if (!($flags & 0x1) && !($flags & 0x08000000)) {
    //             continue;
    //         }

    //         if (!$tid || !isset($routes_map[$tid]))
    //             continue;

    //         $route_name = $routes_map[$tid]['route_name'];
    //         if (!isset($grouped_rides[$route_name])) {
    //             $grouped_rides[$route_name] = [];
    //         }

    //         // Reemplazar el ID de la unidad con su nombre (ASIGNACION)
    //         $unit_name = $simcards[$ride['u']] ?? "Desconocido";
    //         $ride['unit_name'] = $unit_name;

    //         $grouped_rides[$route_name][] = $ride;
    //     }

    //     return view('rutas.index', compact('grouped_rides', 'routes_map', 'stops_map'));
    // }



    public function index()
    {
        // Obtener el usuario autenticado y su depot
        $usuario = Auth::user();
        if (!$usuario) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión');
        }

        $token = $usuario->TOKEN;
        $depot = $usuario->DEPOT;

        // URLs de la API Nimbus con el depot del usuario
        $url_routes = "https://nimbus.wialon.com/api/depot/{$depot}/routes";
        $url_stops = "https://nimbus.wialon.com/api/depot/{$depot}/stops";
        $url_rides = "https://nimbus.wialon.com/api/depot/{$depot}/rides";

        // Obtener datos de la API
        $routes_data = $this->getApiData($url_routes, $token);
        $stops_data = $this->getApiData($url_stops, $token);
        $rides_data = $this->getApiData($url_rides, $token);

        if (!$routes_data || !isset($routes_data['routes']) || !$stops_data || !isset($stops_data['stops']) || !$rides_data || !isset($rides_data['rides'])) {
            return back()->with('error', 'No se pudo obtener la información de Nimbus');
        }

        // Obtener las unidades y asociarlas con su nombre (ASIGNACION)
        $simcards = SIMCARD::pluck('ASIGNACION', 'ID_WIALON');

        // Mapeo de paradas
        $stops_map = [];
        foreach ($stops_data['stops'] as $stop) {
            $stops_map[$stop['id']] = $stop['n'];
        }

        // Mapeo de rutas por ID
        $routes_map = [];
        foreach ($routes_data['routes'] as $route) {
            if (!isset($route['tt']))
                continue;
            foreach ($route['tt'] as $timetable) {
                $tid = $timetable['id'];
                $routes_map[$tid] = [
                    'route_name' => $route['n'],
                    'stops' => $route['st'] ?? []
                ];
            }
        }

        // Agrupar viajes por ruta y guardar en la base de datos
        $grouped_rides = [];
        foreach ($rides_data['rides'] as $ride) {
            $tid = $ride['tid'] ?? null;
            $flags = $ride['f'] ?? 0;

            // Filtrar solo rides con flag 'tracking' o 'active'
            if (!($flags & 0x1) && !($flags & 0x08000000)) {
                continue;
            }

            if (!$tid || !isset($routes_map[$tid]))
                continue;

            $route_name = $routes_map[$tid]['route_name'];
            if (!isset($grouped_rides[$route_name])) {
                $grouped_rides[$route_name] = [];
            }

            // Reemplazar el ID de la unidad con su nombre (ASIGNACION)
            $unit_name = $simcards[$ride['u']] ?? "Desconocido";
            $ride['unit_name'] = $unit_name;

            // Guardar cada parada en la base de datos
            foreach ($routes_map[$tid]['stops'] as $index => $stop) {
                if (!isset($stops_map[$stop['id']]))
                    continue;

                $plan_time = isset($ride['pt'][$index])
                    ? date('H:i', $ride['pt'][$index] - 18000)
                    : null;

                $exec_time = isset($ride['at'][$index]) && $ride['at'][$index]
                    ? date('H:i', $ride['at'][$index] - 18000)
                    : null;

                $diff = null;
                if ($plan_time && $exec_time) {
                    $plan_min = intval(date('i', $ride['pt'][$index]));
                    $exec_min = intval(date('i', $ride['at'][$index]));
                    $diff = $exec_min - $plan_min;
                }

                // 🔹 **Actualizar si ya existe o crear un nuevo registro**
                Ride::updateOrCreate(
                    [
                        'tid' => $tid,
                        'stop_id' => $stop['id'],
                        'unit_name' => $unit_name,
                    ],
                    [
                        'route_name' => $route_name,
                        'stop_name' => $stops_map[$stop['id']],
                        'plan_time' => $plan_time,
                        'exec_time' => $exec_time,
                        'diff' => $diff
                    ]
                );
            }

            $grouped_rides[$route_name][] = $ride;
        }

        return view('rutas.index', compact('grouped_rides', 'routes_map', 'stops_map'));
    }

    private function getApiData($url, $token)
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => "Token $token"
        ])->get($url);

        return $response->successful() ? $response->json() : null;
    }



    public function actualizarTabla()
    {
        try {
            $rides = Ride::orderBy('updated_at', 'desc')->get()->groupBy('route_name');

            if ($rides->isEmpty()) {
                return response()->json([
                    'error' => 'No hay datos disponibles para mostrar.'
                ], 404);
            }

            return response()->json([
                'html' => view('rutas.tabla', compact('rides'))->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }


}
