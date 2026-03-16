<?php
session_start();
date_default_timezone_set('America/Guayaquil');
/*
|--------------------------------------------------------------------------
| CONFIGURACIÓN
|--------------------------------------------------------------------------
*/
const LOGIN_URL = 'https://bwsae-uat.transporteinteligentequito.com/api/v1/Auth/Login';
const DATA_URL = 'https://bwsae-uat.transporteinteligentequito.com/api/v1/SIU/Vehiculos/real-time';

const LOGIN_EMAIL = 'sirenita_expres2017@hotmail.com';
const LOGIN_PASSWORD = 'Cu10~z5P|>Oj';

// OJO: aunque pongas la key aquí, en el navegador se verá igual.
// Restringe esta key por dominio en Google Cloud Console.
const GOOGLE_MAPS_API_KEY = 'AIzaSyA-djChziSZM0U0piJrUrJY3B08U08RBPw';

// Archivo temporal para cachear el token
const TOKEN_CACHE_FILE = __DIR__ . '/token_cache_bwsae.json';

// Cada cuánto refrescar desde frontend (milisegundos)
const REFRESH_MS = 15000;


/*
|--------------------------------------------------------------------------
| FUNCIONES
|--------------------------------------------------------------------------
*/
function jsonResponse($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function curlRequest(string $url, string $method = 'GET', array $headers = [], ?array $body = null): array
{
    $ch = curl_init($url);

    $defaultHeaders = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    $allHeaders = array_merge($defaultHeaders, $headers);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $allHeaders,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($response === false || !empty($error)) {
        return [
            'ok' => false,
            'status' => $httpCode,
            'error' => $error ?: 'Error desconocido en cURL',
            'raw' => null,
            'json' => null
        ];
    }

    $decoded = json_decode($response, true);

    return [
        'ok' => $httpCode >= 200 && $httpCode < 300,
        'status' => $httpCode,
        'error' => null,
        'raw' => $response,
        'json' => $decoded
    ];
}

function readTokenCache(): ?array
{
    if (!file_exists(TOKEN_CACHE_FILE)) {
        return null;
    }

    $content = @file_get_contents(TOKEN_CACHE_FILE);
    if ($content === false || trim($content) === '') {
        return null;
    }

    $json = json_decode($content, true);
    if (!is_array($json)) {
        return null;
    }

    return $json;
}

function saveTokenCache(string $token, int $expiresAt): void
{
    $data = [
        'token' => $token,
        'expires_at' => $expiresAt
    ];

    @file_put_contents(TOKEN_CACHE_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getBearerToken(bool $forceRefresh = false): string
{
    if (!$forceRefresh) {
        $cache = readTokenCache();

        if (
            $cache &&
            !empty($cache['token']) &&
            !empty($cache['expires_at']) &&
            time() < ((int) $cache['expires_at'] - 60)
        ) {
            return $cache['token'];
        }
    }

    $loginPayload = [
        'email' => LOGIN_EMAIL,
        'password' => LOGIN_PASSWORD
    ];

    $login = curlRequest(LOGIN_URL, 'POST', [], $loginPayload);

    if (!$login['ok']) {
        throw new Exception('No se pudo hacer login. HTTP ' . $login['status'] . '. ' . ($login['error'] ?? $login['raw']));
    }

    $json = $login['json'];

    if (
        !is_array($json) ||
        empty($json['isSuccess']) ||
        empty($json['objectResp']['token'])
    ) {
        throw new Exception('La respuesta de login no contiene un token válido.');
    }

    $token = $json['objectResp']['token'];

    // Tu JWT dura aprox. 30 min; lo guardamos con 29 min por seguridad.
    $expiresAt = time() + (29 * 60);

    saveTokenCache($token, $expiresAt);

    return $token;
}

function fetchVehicles(): array
{
    $token = getBearerToken(false);

    $response = curlRequest(DATA_URL, 'GET', [
        'Authorization: Bearer ' . $token
    ]);

    // Si el token expiró o el backend devolvió 401/403, refrescamos y reintentamos una vez
    if (!$response['ok'] && in_array($response['status'], [401, 403], true)) {
        $token = getBearerToken(true);

        $response = curlRequest(DATA_URL, 'GET', [
            'Authorization: Bearer ' . $token
        ]);
    }

    if (!$response['ok']) {
        throw new Exception('No se pudo consultar vehículos. HTTP ' . $response['status'] . '. ' . ($response['error'] ?? $response['raw']));
    }

    $json = $response['json'];

    if (!is_array($json) || empty($json['isSuccess'])) {
        throw new Exception('La respuesta del endpoint de vehículos no es válida.');
    }

    $vehicles = [];

    if (!empty($json['objectResp']) && is_array($json['objectResp'])) {
        foreach ($json['objectResp'] as $group) {
            if (!is_array($group)) {
                continue;
            }

            foreach ($group as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $lat = isset($item['latitude']) ? (float) $item['latitude'] : null;
                $lng = isset($item['longitude']) ? (float) $item['longitude'] : null;

                if ($lat === null || $lng === null) {
                    continue;
                }

                $vehicles[] = [
                    'vehicle' => $item['vehicle'] ?? 'SIN_PLACA',
                    'isConnected' => (bool) ($item['isConnected'] ?? false),
                    'lastActionDate' => $item['lastActionDate'] ?? '',
                    'routeId' => $item['routeId'] ?? '',
                    'tripId' => $item['tripId'] ?? '',
                    'driverCode' => $item['driverCode'] ?? '',
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'stopId' => $item['stopId'] ?? '',
                    'speed' => isset($item['speed']) ? (float) $item['speed'] : 0,
                    'bearing' => isset($item['bearing']) ? (float) $item['bearing'] : 0,
                    'odometer' => isset($item['odometer']) ? (float) $item['odometer'] : 0,
                    'serviceCode' => $item['serviceCode'] ?? '',
                    'startTime' => $item['startTime'] ?? '',
                    'startDate' => $item['startDate'] ?? ''
                ];
            }
        }
    }

    return $vehicles;
}


/*
|--------------------------------------------------------------------------
| API INTERNA DEL MISMO ARCHIVO
|--------------------------------------------------------------------------
*/
if (isset($_GET['action']) && $_GET['action'] === 'vehicles') {
    try {
        $vehicles = fetchVehicles();

        jsonResponse([
            'ok' => true,
            'serverTime' => date('Y-m-d H:i:s'),
            'count' => count($vehicles),
            'vehicles' => $vehicles
        ]);
    } catch (Throwable $e) {
        jsonResponse([
            'ok' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Monitoreo Vehículos BW SAE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html,
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: Arial, sans-serif;
        }

        #topbar {
            height: 60px;
            background: #1f2937;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            box-sizing: border-box;
        }

        #map {
            width: 100%;
            height: calc(100% - 60px);
        }

        .status {
            font-size: 14px;
        }

        .status span {
            margin-right: 15px;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .ok {
            background: #16a34a;
            color: white;
        }

        .error {
            background: #dc2626;
            color: white;
        }
    </style>
</head>

<body>
    <div id="topbar">
        <div><strong>Monitoreo de Vehículos en Tiempo Real (Data de SM) PRECISOGPS TU MEJOR OPCIÓN "DONDE PISA UN CUY
                NINGUNA RATA BORRA LA HUELLA PAPI"</strong></div>
        <div class="status">
            <span id="vehicleCount">Vehículos: 0</span>
            <span id="lastUpdate">Última actualización: --</span>
            <span id="apiStatus" class="badge ok">Conectando...</span>
        </div>
    </div>

    <div id="map"></div>

    <script>
        let map;
        let infoWindow;
        let markers = {};
        let firstFitDone = false;

        async function loadVehicles() {
            try {
                const response = await fetch('?action=vehicles&_=' + Date.now());
                const data = await response.json();

                if (!data.ok) {
                    setApiStatus(false, data.message || 'Error desconocido');
                    return;
                }

                setApiStatus(true, 'OK');
                updateMap(data.vehicles || []);

                document.getElementById('vehicleCount').textContent = 'Vehículos: ' + (data.count || 0);
                document.getElementById('lastUpdate').textContent = 'Última actualización: ' + (data.serverTime || '--');
            } catch (error) {
                setApiStatus(false, error.message || 'Error de red');
            }
        }

        function setApiStatus(ok, message) {
            const el = document.getElementById('apiStatus');
            el.textContent = ok ? 'Conectado' : 'Error';
            el.className = 'badge ' + (ok ? 'ok' : 'error');

            if (!ok) {
                console.error('API Error:', message);
            }
        }
        function getCarIcon(bearing = 0) {
            const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 64 64">
            <g>
                <rect x="14" y="10" width="36" height="30" rx="8" ry="8" fill="#2563eb" stroke="#1e3a8a" stroke-width="2"/>
                <rect x="19" y="15" width="26" height="10" rx="2" fill="#bfdbfe" stroke="#1e3a8a" stroke-width="1.5"/>
                <rect x="18" y="28" width="8" height="7" rx="1.5" fill="#e5e7eb"/>
                <rect x="28" y="28" width="8" height="7" rx="1.5" fill="#e5e7eb"/>
                <rect x="38" y="28" width="8" height="7" rx="1.5" fill="#e5e7eb"/>
                <circle cx="22" cy="44" r="5" fill="#111827"/>
                <circle cx="42" cy="44" r="5" fill="#111827"/>
                <circle cx="22" cy="44" r="2" fill="#9ca3af"/>
                <circle cx="42" cy="44" r="2" fill="#9ca3af"/>
                <rect x="24" y="38" width="16" height="3" rx="1.5" fill="#f59e0b"/>
            </g>
        </svg>
    `;

            return {
                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                scaledSize: new google.maps.Size(36, 36),
                anchor: new google.maps.Point(18, 18)
            };
        }
        function updateMap(vehicles) {
            const activeVehicles = new Set();
            const bounds = new google.maps.LatLngBounds();

            vehicles.forEach(v => {
                const id = v.vehicle || ('veh_' + Math.random());
                activeVehicles.add(id);

                const position = { lat: Number(v.latitude), lng: Number(v.longitude) };

                const contentHtml = `
                    <div style="min-width:220px">
                        <h3 style="margin:0 0 8px 0;">${escapeHtml(v.vehicle)}</h3>
                        <div><strong>Conectado:</strong> ${v.isConnected ? 'Sí' : 'No'}</div>
                        <div><strong>Chofer:</strong> ${escapeHtml(v.driverCode || '')}</div>
                        <div><strong>Ruta:</strong> ${escapeHtml(v.routeId || '')}</div>
                        <div><strong>Trip:</strong> ${escapeHtml(v.tripId || '')}</div>
                        <div><strong>Stop:</strong> ${escapeHtml(v.stopId || '')}</div>
                        <div><strong>Codigo Servicio:</strong> ${escapeHtml(v.serviceCode || '')}</div>
                        <div><strong>Velocidad:</strong> ${Number(v.speed || 0).toFixed(1)} km/h</div>
                        <div><strong>Rumbo:</strong> ${Number(v.bearing || 0).toFixed(1)}°</div>
                        <div><strong>Última acción:</strong> ${escapeHtml(v.lastActionDate || '')}</div>
                        <div><strong>Lat:</strong> ${Number(v.latitude).toFixed(6)}</div>
                        <div><strong>Lng:</strong> ${Number(v.longitude).toFixed(6)}</div>
                    </div>
                `;

                if (markers[id]) {
                    markers[id].setPosition(position);
                    markers[id].setIcon(getCarIcon(v.bearing));
                    markers[id].meta = contentHtml;
                } else {
                    const marker = new google.maps.Marker({
                        position,
                        map,
                        title: v.vehicle || 'Vehículo',
                        icon: getCarIcon(v.bearing)
                    });

                    marker.meta = contentHtml;

                    marker.addListener('click', () => {
                        infoWindow.setContent(marker.meta);
                        infoWindow.open(map, marker);
                    });

                    markers[id] = marker;
                }

                bounds.extend(position);
            });

            Object.keys(markers).forEach(id => {
                if (!activeVehicles.has(id)) {
                    markers[id].setMap(null);
                    delete markers[id];
                }
            });

            if (!firstFitDone && vehicles.length > 0) {
                map.fitBounds(bounds);
                firstFitDone = true;
            }
        }

        function escapeHtml(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: -0.180653, lng: -78.467834 },
                zoom: 11,
                mapTypeId: 'roadmap'
            });

            infoWindow = new google.maps.InfoWindow();

            loadVehicles();
            setInterval(loadVehicles, <?= REFRESH_MS ?>);
        }

        window.initMap = initMap;
    </script>

    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars(GOOGLE_MAPS_API_KEY, ENT_QUOTES, 'UTF-8') ?>&callback=initMap">
        </script>
</body>

</html>