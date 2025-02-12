<?php

// URL del endpoint
$url = "https://nimbus.wialon.com/api/depot/10994/routes";

// Token de autenticación
$token = "79135be629b04b16a9836787d46480a6";

// Configurar cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Token $token",
    "Content-Type: application/json"
]);

// Ejecutar la solicitud y obtener la respuesta
$response = curl_exec($ch);
curl_close($ch);

// Decodificar JSON
$data = json_decode($response, true);

// Definir la zona horaria de Quito
date_default_timezone_set("America/Guayaquil");

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rutas Wialon</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h2>Listado de Rutas</h2>
    <table>
        <thead>
            <tr>
                <th>ID Ruta</th>
                <th>Nombre</th>
                <th>Transporte</th>
                <th>Horarios</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['routes'])): ?>
                <?php foreach ($data['routes'] as $route): ?>
                    <tr>
                        <td><?= $route['id'] ?></td>
                        <td><?= $route['n'] ?></td>
                        <td><?= $route['tp'] ?></td>
                        <td>
                            <?php if (!empty($route['tt'])): ?>
                                <ul>
                                    <?php foreach ($route['tt'] as $timetable): ?>
                                        <li>ID Horario: <?= $timetable['id'] ?>
                                            <ul>
                                                <?php if (!empty($timetable['t'])): ?>
                                                    <?php foreach ($timetable['t'] as $time): ?>
                                                        <li><?= gmdate("H:i:s", $time) ?></li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <li>No hay tiempos disponibles</li>
                                                <?php endif; ?>
                                            </ul>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                No hay horarios disponibles
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No hay rutas disponibles</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>

</html>