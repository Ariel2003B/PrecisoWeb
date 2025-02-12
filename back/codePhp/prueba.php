<?php

$token = '79135be629b04b16a9836787d46480a6';
$depot_id = 10994;

//  IMPORTANT: Use the correct base URL for the NimBus API
$url = 'https://nimbus.wialon.com/api/depot/' . $depot_id . '/ride';

// Get the current date in the format YYYY-MM-DD. This is what 'd' expects.
$date = date('Y-m-d'); // Example: 2023-10-27

$data = [
    "u" => "401691244",
    "tid" => 1671654, // Include the Trip ID
    "d" => $date,  // Include the date
];

$json_data = json_encode($data);

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Token ' . $token, // Correct Authorization header
    ],
    CURLOPT_POSTFIELDS => $json_data,
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    echo $response;
}

curl_close($ch);

?>