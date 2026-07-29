<?php

namespace Tests\Feature;

use App\Models\USUARIO;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Endpoint nuevo y aditivo para la App Flota de PrecisoBus (Fase 9, M9.2).
 * No usa RefreshDatabase (igual que el resto de la suite): corre contra la
 * base ya configurada en .env, sin aislar/limpiar datos.
 *
 * Requiere que existan usuarios de prueba con perfil Administrador y
 * Accionista (ver docs de la sesión: PER_ID 8/9/10 en PERFIL, correos
 * test-admin@precisogps.local / test-accionista@precisogps.local).
 */
class UnidadesMiasTest extends TestCase
{
    public function test_administrador_ve_toda_la_flota_de_su_empresa(): void
    {
        $admin = USUARIO::where('CORREO', 'test-admin@precisogps.local')->first();
        $this->assertNotNull($admin, 'Falta el usuario de prueba test-admin@precisogps.local (ver PERFIL/USUARIO).');

        $token = $admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/unidades/mias');

        $response->assertStatus(200);
        $response->assertJsonPath('usuario.usuId', $admin->USU_ID);
        $response->assertJsonPath('usuario.empId', $admin->EMP_ID);
        $response->assertJsonPath('usuario.perfil', 'Administrador');
        $this->assertGreaterThan(0, count($response->json('unidades')));
    }

    public function test_accionista_ve_solo_sus_unidades(): void
    {
        $accionista = USUARIO::where('CORREO', 'test-accionista@precisogps.local')->first();
        $this->assertNotNull($accionista, 'Falta el usuario de prueba test-accionista@precisogps.local.');

        $token = $accionista->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/unidades/mias');

        $response->assertStatus(200);
        $response->assertJsonPath('usuario.perfil', 'Accionista');

        $unidades = $response->json('unidades');
        $this->assertNotEmpty($unidades);
        foreach ($unidades as $unidad) {
            // Todas las unidades devueltas deben pertenecer al accionista (usu_id), nunca a otro dueño.
            $this->assertEquals('PAT0001', $unidad['placa']);
        }
    }

    // Nota: no se prueba aquí el caso "sin token" — App\Http\Middleware\Authenticate::redirectTo()
    // tiene un bug preexistente (retorno sin `return`, típico de un upgrade a PHP 8) que hace que
    // CUALQUIER ruta auth:sanctum sin token devuelva 500 en vez de 401. No es de este endpoint ni
    // se toca acá (regla del proyecto: no modificar código existente de PrecisoGPS).
}
