<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Assistance;
use App\Models\Affirmation;
use Illuminate\Console\Command;
use Jmrashed\Zkteco\Lib\ZKTeco;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SyncBiometricAttendance extends Command
{
    protected $signature = 'biometric:sync';
    protected $description = 'Sincronizar asistencias desde biométrico cada 5 minutos';

    public function handle()
    {
        $deviceIp = config('biometric.ip', '192.168.1.100');
        $devicePort = config('biometric.port', 4370);

        $this->info("🔌 Conectando al biométrico {$deviceIp}:{$devicePort}...");

        $zk = new ZKTeco($deviceIp, $devicePort);
        if (!$zk->connect()) {
            $this->error('❌ No se pudo conectar al dispositivo');
            return 1;
        }

        $zk->disableDevice();
        $logs = $zk->getAttendance();
        $zk->enableDevice();
        $zk->disconnect();

        $this->info("📥 Registros encontrados: " . count($logs));

        $procesados = 0;
        foreach ($logs as $log) {
            if ($this->processLog($log)) {
                $procesados++;
            }
        }

        $this->info("✅ Registros procesados: {$procesados}");
        return 0;
    }

    private function processLog($log)
    {
        $user = User::where('codigo', $log['id'])->first();
        if (!$user) {
            $this->warn("⚠️ Usuario no encontrado: {$log['id']}");
            return false;
        }

        $timestamp = Carbon::parse($log['timestamp']);
        $fecha = $timestamp->format('Y-m-d');
        $hora = $timestamp->format('H:i:s');

        $asistencia = Assistance::where('user_id', $user->id)
            ->where('fecha_entrada', $fecha)
            ->first();

        if (!$asistencia) {
            return $this->registrarEntrada($user, $fecha, $hora, $timestamp);
        } else {
            return $this->registrarSalida($asistencia, $fecha, $hora, $timestamp);
        }
    }

    private function registrarEntrada($user, $fecha, $hora, $timestamp)
    {
        $diaSemana = strtolower($timestamp->locale('es')->dayName);
        $turno = $this->getUserShiftForDay($user, $diaSemana, $timestamp);

        if (!$turno) {
            $this->warn("🚫 Sin turno asignado para {$user->nombre} el {$fecha}");
            return false;
        }

        $horaEntradaTurno = Carbon::parse($turno->hora_inicio);
        $esRetraso = $timestamp->greaterThan($horaEntradaTurno);

        $asistencia = Assistance::create([
            'id'             => Str::uuid(),
            'user_id'        => $user->id,
            'fecha_entrada'  => $fecha,
            'hora_entrada'   => $hora,
            'entrada'        => true,
        ]);

        Affirmation::create([
            'id'            => Str::uuid(),
            'assistance_id' => $asistencia->id,
            'retraso'       => $esRetraso,
        ]);

        $this->info("✅ ENTRADA: {$user->nombre} {$user->apellido} - {$fecha} {$hora}" . ($esRetraso ? ' (RETRASO)' : ''));
        return true;
    }

    private function registrarSalida($asistencia, $fecha, $hora, $timestamp)
    {
        if ($asistencia->salida) return false;

        $horaEntrada = Carbon::parse($asistencia->hora_entrada);
        $minutosDiferencia = $horaEntrada->diffInMinutes($timestamp);

        // ❌ Si pasó menos de 5 minutos, no es salida válida
        if ($minutosDiferencia < 5) {
            $this->info("⏳ Marca ignorada (menos de 5 minutos después de entrada): {$hora}");
            return false;
        }

        // ✅ Procesar como salida válida
        $user = User::find($asistencia->user_id);
        $diaSemana = strtolower($timestamp->locale('es')->dayName);
        $turno = $this->getUserShiftForDay($user, $diaSemana, $timestamp);

        $cumplido = true;
        if ($turno) {
            $horaSalidaTurno = Carbon::parse($turno->hora_fin);
            $minutosEsperados = $horaEntrada->diffInMinutes($horaSalidaTurno);
            $cumplido = $minutosDiferencia >= $minutosEsperados;
        }

        $asistencia->update([
            'fecha_salida' => $fecha,
            'hora_salida'  => $hora,
            'salida'       => true,
        ]);

        $afirmacion = Affirmation::where('assistance_id', $asistencia->id)->first();
        if ($afirmacion) {
            $afirmacion->update(['retraso' => !$cumplido]);
        }

        $this->info("🚪 SALIDA: {$user->nombre} {$user->apellido} - {$fecha} {$hora}" . (!$cumplido ? ' (NO CUMPLIÓ)' : ''));
        return true;
    }

    // ===== MISMA LÓGICA QUE EN EL CONTROLADOR =====

    private function getUserShiftForDay($user, $diaSemana, $fecha)
    {
        if ($user->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->shifts, $diaSemana, $fecha);
        }
        if ($user->rol && $user->rol->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->rol->shifts, $diaSemana, $fecha);
        }
        if ($user->branch && $user->branch->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->branch->shifts, $diaSemana, $fecha);
        }
        if ($user->group && $user->group->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->group->shifts, $diaSemana, $fecha);
        }
        if ($user->section && $user->section->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->section->shifts, $diaSemana, $fecha);
        }
        return null;
    }

    private function buscarTurnoEnColeccion($shifts, $diaSemana, $fecha)
    {
        foreach ($shifts as $shift) {
            foreach ($shift->schedules as $schedule) {
                $dias = $schedule->dias;
                if (is_array($dias) && in_array($diaSemana, $dias)) {
                    $desde = Carbon::parse($shift->desde);
                    $hasta = Carbon::parse($shift->hasta);
                    if ($fecha->between($desde, $hasta)) {
                        return $schedule;
                    }
                }
            }
        }
        return null;
    }
}