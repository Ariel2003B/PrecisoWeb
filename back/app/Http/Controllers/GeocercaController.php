<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\SimcardController;

class GeocercaController extends Controller
{
    public function index()
    {
        $sidWialon = (new SimcardController())->getWialonSid();
    
        $url = "https://hst-api.wialon.com/wialon/ajax.html?svc=core/search_items&params=" . urlencode(json_encode([
            "spec" => [
                "itemsType" => "avl_resource",
                "propName" => "sys_name",
                "propValueMask" => "*",
                "sortType" => "sys_name"
            ],
            "force" => 1,
            "flags" => 8193,
            "from" => 0,
            "to" => 0
        ])) . "&sid=$sidWialon";
    
        $response = \Http::get($url);
    
        if ($response->failed()) {
            $recursos = [];
        } else {
            $items = $response->json()['items'];
            $recursos = [];
            foreach ($items as $item) {
                $recursos[] = [
                    'id' => $item['id'],
                    'nm' => $item['nm']
                ];
            }
        }
    
        return view('geofences.index', compact('recursos'));
    }
    
    public function crear(Request $request)
    {
        $sidWialon = (new SimcardController())->getWialonSid();
        // Recibir variables dinámicas
        $tokenNimbus = $request->input('token_nimbus');
        $depotId = $request->input('depot_id');
        $itemId = $request->input('item_id');
        $grupos = $request->input('grupos');

        // Obtener paradas desde Nimbus
        $apiNimbusUrl = "https://nimbus.wialon.com/api/depot/$depotId/stops";

        $nimbusResponse = $this->curlGet($apiNimbusUrl, [
            "Authorization: Token $tokenNimbus",
            "Content-Type: application/json"
        ]);

        $stops = json_decode($nimbusResponse, true)['stops'];

        // 1️⃣ **Crear Grupos en Wialon**
        $grupoIds = [];
        $coloresGrupo = [];
        $baseColors = [
            0x80FF0000, // Rojo
            0x8000FF00, // Verde
            0x800000FF, // Azul
            0x80FFFF00, // Amarillo
            0x80FF00FF, // Magenta
            0x8000FFFF  // Cyan
        ];
        $contadorColor = 0;

        foreach ($grupos as $grupo) {
            $paramsGrupo = [
                "itemId" => (int) $itemId,
                "id" => 0,
                "callMode" => "create",
                "n" => $grupo['nombre'],
                "d" => "Grupo creado dinámicamente",
                "zns" => [],
                "f" => 0
            ];

            $grupoResponse = $this->curlGet("https://hst-api.wialon.com/wialon/ajax.html?svc=resource/update_zones_group&params=" . urlencode(json_encode($paramsGrupo)) . "&sid=$sidWialon");
            $grupoResponseData = json_decode($grupoResponse, true);

            if (isset($grupoResponseData[1]['id'])) {
                $grupoIds[$grupo['identificador']] = $grupoResponseData[1]['id'];
                $grupoNombres[$grupoResponseData[1]['id']] = $grupo['nombre']; // Guardar nombre original                
                $coloresGrupo[$grupoIds[$grupo['identificador']]] = $baseColors[$contadorColor % count($baseColors)];
                $contadorColor++;
            }
        }

        // 2️⃣ **Crear Geocercas y Asociar al Grupo**
        $geofencesByGroup = [];

        foreach ($stops as $stop) {
            $name = $stop['n'];
            $description = $stop['d'];
            $shape = $stop['sh'];
            $coordinates = $stop['p'];

            // Validación para omitir '1. Nombre'
            // Determinar identificador
            $identificador = 'plain';
            if (preg_match('/^\d/', $name)) {
                $identificador = 'number';
            } elseif (preg_match('/^[a-z]/', $name)) {
                $identificador = 'letter';
            }

            // Verificar si existe el grupo
            if (!isset($grupoIds[$identificador])) {
                continue;
            }

            $groupId = $grupoIds[$identificador];
            $color = $coloresGrupo[$groupId];

            // Crear la geocerca en Wialon
            $paramsGeocerca = [
                "itemId" => (int) $itemId,
                "id" => 0,
                "callMode" => "create",
                "n" => $name,
                "d" => $description,
                "t" => ($shape == 0) ? 3 : 2,
                "f" => 33,
                "c" => $color,
                "w" => ($shape == 0) ? ($coordinates[0]['r'] ?? 20) : 0
            ];

            if ($shape == 0) {
                $paramsGeocerca["p"] = [
                    [
                        "x" => $coordinates[0]['x'],
                        "y" => $coordinates[0]['y'],
                        "r" => $coordinates[0]['r'] ?? 20
                    ]
                ];
            } else {
                $paramsGeocerca["p"] = array_map(function ($coord) {
                    return [
                        "x" => $coord['x'],
                        "y" => $coord['y'],
                        "r" => 0
                    ];
                }, $coordinates);
            }

            $geoResponse = $this->curlGet("https://hst-api.wialon.com/wialon/ajax.html?svc=resource/update_zone&params=" . urlencode(json_encode($paramsGeocerca)) . "&sid=$sidWialon");

            $geoResult = json_decode($geoResponse, true);

            if (isset($geoResult[1]['id'])) {
                $geoId = $geoResult[1]['id'];
                $geofencesByGroup[$groupId][] = $geoId;
            }
        }

        // 3️⃣ **Asignar geocercas a sus grupos**
        foreach ($geofencesByGroup as $groupId => $geofences) {
            $paramsGrupoUpdate = [
                "itemId" => (int) $itemId,
                "id" => $groupId,
                "callMode" => "update",
                "n" => $grupoNombres[$groupId],
                "d" => "Grupo actualizado con geocercas",
                "zns" => $geofences
            ];

            $this->curlGet("https://hst-api.wialon.com/wialon/ajax.html?svc=resource/update_zones_group&params=" . urlencode(json_encode($paramsGrupoUpdate)) . "&sid=$sidWialon");
        }

        return 'Proceso completado';
    }

    private function curlGet($url, $headers = [])
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        return curl_exec($ch);
    }


    public function obtenerDepots(Request $request)
    {
        $tokenNimbus = $request->input('token_nimbus');
        $url = "https://nimbus.wialon.com/api/depots";

        $response = \Http::withHeaders([
            'Authorization' => "Token $tokenNimbus",
            'Content-Type' => 'application/json',
        ])->get($url);

        if ($response->failed()) {
            return response()->json(['error' => 'No se pudo obtener los depots. Verifique el token.'], 400);
        }

        return response()->json($response->json()['depots']);
    }


}
