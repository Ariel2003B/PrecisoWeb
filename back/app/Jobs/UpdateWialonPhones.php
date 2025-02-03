<?php

namespace App\Jobs;

use App\Models\SIMCARD;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateWialonPhones implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Configuración inicial del Job.
     */
    public function __construct()
    {
        //
    }

    /**
     * Ejecutar el Job en segundo plano.
     */
    public function handle()
    {
        $wialon_api_url = "https://hst-api.wialon.com/wialon/ajax.html";
        $token = "a21e2472955b1cb0847730f34edcf3e804692BDC51F76DAA7CC69358123221016F111F39";

        // 1. Obtener la SID (autenticación con Wialon)
        $authResponse = Http::get("$wialon_api_url?svc=token/login&params=" . urlencode(json_encode(["token" => $token])));
        $authData = $authResponse->json();
        if (!isset($authData['eid'])) {
            Log::error("❌ Error autenticando en Wialon.");
            return;
        }
        $sid = $authData['eid']; // Guardamos la sesión activa

        // 2. Obtener todas las unidades con UID desde Wialon
        $params = json_encode([
            "spec" => [
                "itemsType" => "avl_unit",
                "propName" => "sys_name",
                "propValueMask" => "*",
                "sortType" => "sys_name"
            ],
            "force" => 1,
            "flags" => 256,
            "from" => 0,
            "to" => 0
        ]);

        $response = Http::get("$wialon_api_url?svc=core/search_items&params=" . urlencode($params) . "&sid=$sid");
        $data = $response->json();

        if (!isset($data["items"]) || empty($data["items"])) {
            Log::error("❌ No se encontraron unidades en Wialon.");
            return;
        }

        // 3. Obtener IMEIs desde la base de datos
        $simcards = SIMCARD::select("IMEI", "NUMEROTELEFONO")->get();
        $imei_phone_map = $simcards->pluck("NUMEROTELEFONO", "IMEI")->toArray();

        // 4. Buscar coincidencias y obtener itemId
        foreach ($data["items"] as $unit) {
            if (!isset($unit["uid"]) || empty($unit["uid"])) {
                continue;
            }

            $imei = $unit["uid"];
            if (isset($imei_phone_map[$imei])) {
                $new_phone = "+593" . $imei_phone_map[$imei];

                // Obtener itemId desde Wialon
                $params_item = json_encode([
                    "spec" => [
                        "itemsType" => "avl_unit",
                        "propName" => "sys_unique_id",
                        "propValueMask" => $imei,
                        "sortType" => "sys_name"
                    ],
                    "force" => 1,
                    "flags" => 1,
                    "from" => 0,
                    "to" => 0
                ]);

                $item_response = Http::get("$wialon_api_url?svc=core/search_items&params=" . urlencode($params_item) . "&sid=$sid");
                $item_data = $item_response->json();

                if (!isset($item_data["items"][0]["id"])) {
                    Log::warning("⚠️ No se encontró itemId para IMEI '$imei'. Se omite.");
                    continue;
                }

                $item_id = $item_data["items"][0]["id"];

                // 5. Actualizar el número de teléfono en Wialon
                $params_update = json_encode([
                    "itemId" => $item_id,
                    "phoneNumber" => $new_phone
                ]);

                $update_response = Http::get("$wialon_api_url?svc=unit/update_phone&params=" . urlencode($params_update) . "&sid=$sid");
                $update_data = $update_response->json();

                if (isset($update_data["error"])) {
                    Log::error("❌ Error actualizando teléfono para IMEI '$imei'. Código: " . $update_data["error"]);
                } else {
                    Log::info("✅ Teléfono actualizado para IMEI '$imei' (Número: $new_phone).");
                }
            }
        }
    }
}
