<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WialonService
{
    private const URL = 'https://hst-api.wialon.com/wialon/ajax.html';

    private function call(string $svc, array $params = [], ?string $sid = null): array
    {
        $query = [
            'svc'    => $svc,
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
        if ($sid !== null) {
            $query['sid'] = $sid;
        }

        $ch = curl_init(self::URL . '?' . http_build_query($query));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("cURL error Wialon: $err");
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if ($data === null) {
            throw new \RuntimeException("JSON inválido de Wialon: $response");
        }
        if (isset($data['error'])) {
            throw new \RuntimeException("Wialon error {$data['error']} " . ($data['reason'] ?? ''));
        }

        return $data;
    }

    public function login(): string
    {
        $token = config('services.wialon.token');
        $resp  = $this->call('token/login', ['token' => $token, 'fl' => 1]);

        if (empty($resp['eid'])) {
            throw new \RuntimeException('Login Wialon fallido (token inválido o expirado)');
        }

        return $resp['eid'];
    }

    /**
     * Suma los eventos de pasajeros upp/downp dentro del rango dado.
     *
     * @param  string $sid     Session ID de Wialon
     * @param  int    $unitId  ID de la unidad en Wialon
     * @param  int    $inicio  Unix timestamp UTC (inicio del rango)
     * @param  int    $fin     Unix timestamp UTC (fin del rango)
     * @return array{upp: int, downp: int}
     */
    public function contarPasajeros(string $sid, int $unitId, int $inicio, int $fin): array
    {
        $resp = $this->call('messages/load_interval', [
            'itemId'    => $unitId,
            'timeFrom'  => $inicio,
            'timeTo'    => $fin,
            'flags'     => 0,
            'flagsMask' => 0,
            'loadCount' => 10000,
        ], $sid);

        $upp   = 0;
        $downp = 0;

        foreach ($resp['messages'] ?? [] as $m) {
            $t = (int)($m['t'] ?? 0);
            if ($t < $inicio || $t > $fin) {
                continue;
            }
            $p = $m['p'] ?? [];
            if (isset($p['upp']) && is_numeric($p['upp'])) {
                $upp += (int)$p['upp'];
            }
            if (isset($p['downp']) && is_numeric($p['downp'])) {
                $downp += (int)$p['downp'];
            }
        }

        Log::info('Wialon contarPasajeros', [
            'unit_id' => $unitId,
            'inicio'  => $inicio,
            'fin'     => $fin,
            'upp'     => $upp,
            'downp'   => $downp,
        ]);

        return ['upp' => $upp, 'downp' => $downp];
    }
}
