<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\DB;
use Mockery;

class SettingsControllerLogicTest extends TestCase
{
    protected SettingsController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SettingsController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /* ------------------------------------------------------------------
     |  Helper para invocar métodos privados
     * ------------------------------------------------------------------ */
    protected function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /* ==================================================================
       PRUEBAS
       ================================================================== */

    public function test_assignRolePermissionsToUser_asigna_todos_los_permisos()
    {
        // 📌 Mockear User::whereIn(...)->pluck() (por si se usa para validar)
        $userMock = Mockery::mock('alias:App\Models\User');
        $userMock->shouldReceive('whereIn->pluck')
                ->andReturn(collect([7]));

        // 📌 Mockear DB::table('roles_permissions')->where()->pluck()
        DB::shouldReceive('table->where->pluck')
            ->andReturn(collect([100, 101]));

        // 📌 Mockear DB::table('users_permissions')->upsert()
        DB::shouldReceive('table->upsert')
            ->once()
            ->with(
                [
                    ['user_id' => 7, 'permission_id' => 100, 'granted' => true],
                    ['user_id' => 7, 'permission_id' => 101, 'granted' => true],
                ],
                ['user_id', 'permission_id'],
                ['granted']
            );

        // 📌 Ejecutar método privado
        $this->invokeMethod($this->controller, 'assignRolePermissionsToUser', [7, 3]);

        // ✅ Aserción explícita para evitar "risky"
        $this->assertTrue(true);
    }
}