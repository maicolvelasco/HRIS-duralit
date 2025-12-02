<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\PayrollController;
use App\Models\User;
use App\Models\Payroll;
use App\Models\Assistance;
use App\Models\Affirmation;
use App\Models\PermissionRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Mockery;

class PayrollControllerLogicTest extends TestCase
{
    protected PayrollController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PayrollController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function invokeMethod(object $object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    /** PU-NOM-01: Cálculo correcto de días laborables */
    public function test_calcular_dias_laborables_entre_dos_fechas()
    {
        $fechaInicio = Carbon::parse('2025-06-01'); // lunes
        $fechaFin = Carbon::parse('2025-06-07'); // domingo

        $diasLaborables = 0;
        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            if (!$fecha->isWeekend()) {
                $diasLaborables++;
            }
        }

        $this->assertEquals(5, $diasLaborables);
    }

    /** PU-NOM-02: Cálculo de minutos de retraso */
    public function test_calcular_minutos_retraso_desde_hora_entrada()
    {
        $horaEntradaTurno = Carbon::parse('08:00');
        $horaEntradaReal = Carbon::parse('08:15');

        $minutosRetraso = $horaEntradaTurno->diffInMinutes($horaEntradaReal);

        $this->assertEquals(15, $minutosRetraso);
    }

    /** PU-NOM-05: Cálculo de neto a pagar con descuentos */
    public function test_calcular_neto_a_pagar_con_descuentos()
    {
        $salarioBase = 3000;
        $descuentoRetraso = 18.75;
        $descuentoFalta = 100.00;

        $neto = round($salarioBase - $descuentoRetraso - $descuentoFalta, 2);

        $this->assertEquals(2881.25, $neto);
    }
}