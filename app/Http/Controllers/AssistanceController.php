<?php

namespace App\Http\Controllers;

use App\Models\Assistance;
use App\Models\Affirmation;
use App\Models\User;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssistanceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // ✅ Control de Asistencia Propio: solo sus registros
        if ($user->canDo('Control de Asistencia Propio')) {
            $assistances = Assistance::with(['user', 'affirmation'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(20);
        }
        // ✅ Control de Asistencia: todos los registros
        elseif ($user->canDo('Control de Asistencia')) {
            $assistances = Assistance::with(['user', 'affirmation'])
                ->latest()
                ->paginate(20);
        }
        // ❌ Sin permiso: tabla vacía
        else {
            $assistances = Assistance::with(['user', 'affirmation'])
                ->whereRaw('1 = 0')
                ->paginate(20);
        }

        return Inertia::render('Assistance/Index', [
            'assistances' => $assistances,
            'users' => $user->canDo('Control de Asistencia') ? User::select('id', 'nombre', 'apellido', 'codigo')->get() : [],
            'permissions' => [
                'Control de Asistencia' => $user->canDo('Control de Asistencia'),
                'Registro de Entrada' => $user->canDo('Registro de Entrada'),
                'Registro de Salida' => $user->canDo('Registro de Salida'),
                'Control de Asistencia Propio' => $user->canDo('Control de Asistencia Propio'),
            ],
        ]);
    }

    public function storeEntradaManual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'fecha'   => 'required|date',
            'hora'    => 'required|date_format:H:i',
        ]);

        $user = User::with([
            'shifts.schedules',
            'rol.shifts.schedules',
            'branch.shifts.schedules',
            'group.shifts.schedules',
            'section.shifts.schedules'
        ])->findOrFail($request->user_id);

        $fecha = Carbon::parse($request->fecha);
        $horaReal = Carbon::parse($request->hora);
        $diaSemana = strtolower($fecha->locale('es')->dayName);

        $turno = $this->getUserShiftForDay($user, $diaSemana, $fecha);

        if (!$turno) {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'El usuario no tiene un turno asignado para este día.',
            ]);
        }

        $horaEntradaTurno = Carbon::parse($turno->hora_inicio);
        $esRetraso = $horaReal->greaterThan($horaEntradaTurno);

        $existe = Assistance::where('user_id', $request->user_id)
            ->where('fecha_entrada', $request->fecha)
            ->exists();

        if ($existe) {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'El usuario ya tiene entrada ese día.',
            ]);
        }

        $asistencia = Assistance::create([
            'user_id'        => $request->user_id,
            'fecha_entrada'  => $request->fecha,
            'hora_entrada'   => $request->hora,
            'entrada'        => true,
        ]);

        Affirmation::create([
            'assistance_id' => $asistencia->id,
            'retraso'       => $esRetraso,
        ]);

        return redirect()->back()->with('flash', [
            'type' => 'success',
            'message' => 'Entrada registrada correctamente.',
        ]);
    }

    public function storeSalidaManual(Request $request, Assistance $assistance)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora'  => 'required|date_format:H:i',
        ]);

        if ($assistance->salida) {
            return redirect()->back()->with('flash', ['type' => 'error', 'message' => 'Ya tiene salida.']);
        }

        $user = User::with([
            'shifts.schedules',
            'rol.shifts.schedules',
            'branch.shifts.schedules',
            'group.shifts.schedules',
            'section.shifts.schedules'
        ])->findOrFail($assistance->user_id);

        $fecha = Carbon::parse($request->fecha);
        $diaSemana = strtolower($fecha->locale('es')->dayName);
        $turno = $this->getUserShiftForDay($user, $diaSemana, $fecha);

        if (!$turno) {
            return redirect()->back()->with('flash', [
                'type' => 'error',
                'message' => 'No se encontró un turno válido para calcular la jornada.',
            ]);
        }

        $horaEntrada = Carbon::parse($assistance->hora_entrada);
        $horaSalidaReal = Carbon::parse($request->hora);
        $horaSalidaTurno = Carbon::parse($turno->hora_fin);

        $minutosTrabajados = $horaEntrada->diffInMinutes($horaSalidaReal);
        $minutosEsperados = $horaEntrada->diffInMinutes($horaSalidaTurno);

        $cumplido = $minutosTrabajados >= $minutosEsperados;

        $assistance->update([
            'fecha_salida' => $request->fecha,
            'hora_salida'  => $request->hora,
            'salida'       => true,
        ]);

        $afirmacion = Affirmation::where('assistance_id', $assistance->id)->first();
        if ($afirmacion) {
            $afirmacion->update(['retraso' => !$cumplido]);
        }

        return redirect()->back()->with('flash', ['type' => 'success', 'message' => 'Salida registrada.']);
    }

    private function getUserShiftForDay($user, $diaSemana, $fecha)
    {
        // Prioridad 1: user_shift
        if ($user->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->shifts, $diaSemana, $fecha);
        }

        // Prioridad 2: rol
        if ($user->rol && $user->rol->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->rol->shifts, $diaSemana, $fecha);
        }

        // Prioridad 3: branch
        if ($user->branch && $user->branch->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->branch->shifts, $diaSemana, $fecha);
        }

        // Prioridad 4: group
        if ($user->group && $user->group->shifts->isNotEmpty()) {
            return $this->buscarTurnoEnColeccion($user->group->shifts, $diaSemana, $fecha);
        }

        // Prioridad 5: section
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
                if (in_array($diaSemana, $dias)) {
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