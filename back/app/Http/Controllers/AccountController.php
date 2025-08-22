<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;   // <-- NUEVO
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{
    public function deletionRequest(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'reason'   => 'nullable|string|max:2000',
            'source'   => 'nullable|string|max:50',   // web / app
            'platform' => 'nullable|string|max:50',   // android / ios
        ]);

        // Armamos el payload estándar que enviaremos a TU API CORE
        $payload = [
            'email'    => $data['email'],
            'reason'   => $data['reason'] ?? null,
            'source'   => $data['source'] ?? 'web',
            'platform' => $data['platform'] ?? 'android',
            'ip'       => $request->ip(),
            'ua'       => $request->userAgent(),
            'requested_at' => now()->toIso8601String(),
        ];

        $forwardUrl   = env('ACCOUNT_DELETION_FORWARD_URL');
        $forwardToken = env('ACCOUNT_DELETION_FORWARD_TOKEN');
        $okForward    = false;

        // 1) FORWARD a tu API core (donde realmente procesan la eliminación)
        if (!empty($forwardUrl)) {
            try {
                $http = Http::timeout(15);
                if (!empty($forwardToken)) {
                    $http = $http->withToken($forwardToken);
                }

                $resp = $http->post($forwardUrl, $payload);
                $okForward = $resp->successful();
            } catch (\Throwable $e) {
                $okForward = false;
            }
        }

        // 2) (Opcional) Si falló el forward, alerta por correo para manejo manual
        if (!$okForward) {
            try {
                $to = collect(explode(',', (string) env('ACCOUNT_DELETION_NOTIFY_EMAILS')))
                        ->map(fn ($s) => trim($s))
                        ->filter()
                        ->all();

                if (!empty($to)) {
                    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                    // Mail::raw("Solicitud de eliminación (fallback):\n\n{$json}", function ($m) use ($to) {
                    //     $m->to($to)->subject('Solicitud de eliminación de cuenta (fallback)');
                    // });
                }
            } catch (\Throwable $e) {
                // silenciar
            }
        }

        // 3) Respuesta genérica (sin revelar si el email existe, buena práctica)
        return response()->json([
            'ok'      => true,
            'forward' => $okForward ? 'sent' : 'fallback',
            'message' => 'Hemos recibido tu solicitud. Te contactaremos por correo para confirmar.',
        ], 200);
    }
}
