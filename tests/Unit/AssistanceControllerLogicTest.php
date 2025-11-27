<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\AssistanceController;
use App\Models\User;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use Carbon\Carbon;
use Mockery;

class AssistanceControllerLogicTest extends TestCase
{
    protected AssistanceController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AssistanceController();
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

    /** Prueba 1: usuario tiene turno en user_shift → encuentra schedule */
    public function test_getUserShiftForDay_encuentra_turno_en_user_shift()
    {
        // 📌 Crear objetos reales (stdClass) en lugar de mocks de modelo
        $schedule = (object)[
            'dias'        => ['lunes'],
            'hora_inicio' => '08:00',
            'hora_fin'    => '16:00',
        ];

        $shift = (object)[
            'desde'     => '2025-01-01',
            'hasta'     => '2025-12-31',
            'schedules' => collect([$schedule]),
        ];

        $user = (object)[
            'shifts'  => collect([$shift]),
            'rol'     => null,
            'branch'  => null,
            'group'   => null,
            'section' => null,
        ];

        $fecha = Carbon::parse('2025-06-16'); // lunes
        $dia   = strtolower($fecha->locale('es')->dayName); // 'lunes'

        $result = $this->invokeMethod($this->controller, 'getUserShiftForDay', [$user, $dia, $fecha]);

        $this->assertSame($schedule, $result);
    }

    /** Prueba 2: usuario sin turnos → devuelve null */
    public function test_getUserShiftForDay_sin_turnos_devuelve_null()
    {
        $user = (object)[
            'shifts'  => collect(),
            'rol'     => null,
            'branch'  => null,
            'group'   => null,
            'section' => null,
        ];

        $fecha = Carbon::parse('2025-06-16');
        $dia   = strtolower($fecha->locale('es')->dayName);

        $result = $this->invokeMethod($this->controller, 'getUserShiftForDay', [$user, $dia, $fecha]);

        $this->assertNull($result);
    }
}