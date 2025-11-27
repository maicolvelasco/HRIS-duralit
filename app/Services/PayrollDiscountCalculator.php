<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\Assistance;
use App\Models\Affirmation;
use App\Models\PermissionRequest;
use Carbon\Carbon;

class PayrollDiscountCalculator
{
    public function calcular(User $user, Carbon $fechaInicio, Carbon $fechaFin)
    {
        $periodo = $fechaInicio->format('Y-m-') . ($fechaInicio->day <= 15 ? 'Q1' : 'Q2');
        
        // Crear o obtener nómina
        $payroll = Payroll::updateOrCreate(
            ['user_id' => $user->id, 'periodo' => $periodo],
            [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'salario_base' => $user->salario_base ?? 0,
            ]
        );

        // 1. DÍAS LABORABLES
        $diasLaborables = $fechaInicio->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $fechaFin) + 1;
        
        $payroll->update(['dias_laborables' => $diasLaborables]);

        // 2. DÍAS TRABAJADOS y RETRASOS
        $asistencias = Assistance::where('user_id', $user->id)
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->where('entrada', true)
            ->where('salida', true)
            ->get();

        $payroll->update(['dias_trabajados' => $asistencias->count()]);

        // Calcular minutos de retraso
        $minutosRetraso = 0;
        foreach ($asistencias as $asistencia) {
            // Verificar si tiene affirmations con retraso=1
            $retraso = Affirmation::where('assistance_id', $asistencia->id)->where('retraso', true)->exists();
            
            if ($retraso && $asistencia->hora_entrada) {
                // Calcular minutos retraso contra horario del turno
                $turno = $user->currentShift; // Asumiendo relación con turnos
                if ($turno) {
                    $horaEntradaTurno = Carbon::parse($turno->hora_inicio);
                    $horaEntradaReal = Carbon::parse($asistencia->hora_entrada);
                    
                    if ($horaEntradaReal->greaterThan($horaEntradaTurno)) {
                        $minutosRetraso += $horaEntradaTurno->diffInMinutes($horaEntradaReal);
                    }
                }
            }
        }

        // 3. FALTAS (días sin asistencia y sin permiso aprobado)
        $diasConAsistencia = $asistencias->pluck('fecha_entrada')->map->format('Y-m-d');
        
        $diasPermiso = PermissionRequest::where('user_id', $user->id)
            ->where('estado', 'Aprobado')
            ->whereDate('fecha_inicio', '>=', $fechaInicio)
            ->whereDate('fecha_fin', '<=', $fechaFin)
            ->pluck('fecha_inicio')
            ->map->format('Y-m-d');

        // Contar días laborables sin asistencia ni permiso
        $diasFalta = 0;
        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            if (!$fecha->isWeekend() && 
                !$diasConAsistencia->contains($fecha->format('Y-m-d')) &&
                !$diasPermiso->contains($fecha->format('Y-m-d'))) {
                $diasFalta++;
            }
        }

        // 4. CALCULAR DESCUENTOS
        // Valores de referencia (ajusta según tu política)
        $valorMinuto = ($user->salario_base / 30) / 8 / 60; // Salario por minuto
        $valorDia = $user->salario_base / 30; // Salario por día

        $descuentoRetraso = $minutosRetraso * $valorMinuto;
        $descuentoFalta = $diasFalta * $valorDia;

        // 5. ACTUALIZAR NÓMINA
        $payroll->update([
            'minutos_retraso' => $minutosRetraso,
            'dias_falta' => $diasFalta,
            'total_descuentos_retraso' => round($descuentoRetraso, 2),
            'total_descuentos_falta' => round($descuentoFalta, 2),
            'neto_a_pagar' => round($user->salario_base - $descuentoRetraso - $descuentoFalta, 2),
            'estado' => 'Calculado',
        ]);

        // 6. CREAR DETALLES
        $payroll->details()->delete(); // Limpiar anteriores

        if ($minutosRetraso > 0) {
            $payroll->details()->create([
                'concepto' => "Retraso: {$minutosRetraso} minutos",
                'cantidad_minutos_dias' => $minutosRetraso,
                'monto_unitario' => round($valorMinuto, 4),
                'total' => round($descuentoRetraso, 2),
                'fuente' => 'retraso',
            ]);
        }

        if ($diasFalta > 0) {
            $payroll->details()->create([
                'concepto' => "Faltas: {$diasFalta} días",
                'cantidad_minutos_dias' => $diasFalta,
                'monto_unitario' => round($valorDia, 2),
                'total' => round($descuentoFalta, 2),
                'fuente' => 'falta',
            ]);
        }

        return $payroll;
    }
}