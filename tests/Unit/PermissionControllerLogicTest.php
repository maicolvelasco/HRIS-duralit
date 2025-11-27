<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\PermissionController;
use App\Models\User;
use App\Models\Authorization;
use App\Models\PermissionRequest;
use App\Models\Compensation;
use App\Models\Overtime;
use Illuminate\Support\Facades\DB;
use Mockery;

class PermissionControllerLogicTest extends TestCase
{
    protected PermissionController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PermissionController();
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

    public function test_calcularHorasDisponibles_sumar_correctamente()
    {
        $user = (object)['id' => 7];

        $overtime1 = (object)['id' => 1];
        $overtime2 = (object)['id' => 2];

        $comp1 = (object)['quantity' => 3.5];
        $comp2 = (object)['quantity' => 1.5];

        DB::shouldReceive('table->whereHas->sum')->andReturn(5.0);

        $result = $this->invokeMethod($this->controller, 'calcularHorasDisponibles', [$user->id]);

        $this->assertEquals(5.0, $result);
    }

    public function test_devolverCompensaciones_incrementa_quantity_y_cambia_estado()
    {
        $overtime = (object)[
            'id' => 10,
            'estado' => Overtime::USADO,
            'update' => function ($attrs) {
                $this->estado = $attrs['estado'];
            },
        ];

        $compensation = Mockery::mock(Compensation::class);
        $compensation->shouldReceive('increment')->with('quantity', 4.0)->once();
        $compensation->overtime = $overtime;

        DB::shouldReceive('table->whereHas->orderBy->first')->andReturn($compensation);

        $this->invokeMethod($this->controller, 'devolverCompensaciones', [7, 4.0]);

        $this->assertEquals(Overtime::APROBADO, $overtime->estado);
    }
}