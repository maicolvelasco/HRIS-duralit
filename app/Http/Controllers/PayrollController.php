<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use App\Models\Assistance;
use App\Models\Affirmation;
use App\Models\PermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Permisos
        $canViewPayrolls = $user->canDo('Ver Nóminas');
        $canCreatePayrolls = $user->canDo('Crear Nóminas');
        $canEditPayrolls = $user->canDo('Editar Nóminas');
        $canDeletePayrolls = $user->canDo('Eliminar Nóminas');
        $canApprovePayrolls = $user->canDo('Aprobar Nóminas');
        $canPayPayrolls = $user->canDo('Registrar Pagos');
        $canCalculatePayrolls = $user->canDo('Calcular Nóminas');
        $canGenerateReceipts = $user->canDo('Generar Recibos');

        // 🚨 CORRECCIÓN: NO uses fn() en with(), usa función normal
        $payrolls = Payroll::query()
            ->with(['user' => function($q) {
                $q->select('id', 'nombre', 'apellido', 'codigo', 'salario_base');
            }, 'details'])
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('apellido', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%")
                      ->orWhere('periodo', 'like', "%{$search}%");
                });
            })
            ->orderBy('periodo', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            // 🔴 CORRECIÓN IMPORTANTE: Agrega appends para que todo sea array
            ->withQueryString();

        // Obtén usuarios de forma explícita
        $users = User::select('id', 'nombre', 'apellido', 'codigo', 'salario_base')
                   ->whereNotNull('salario_base')
                   ->orderBy('nombre')
                   ->get();

        return Inertia::render('Payroll/Index', [
            'payrolls' => $payrolls,
            'users' => $users,
            'permissions' => [
                'Ver Nóminas' => $canViewPayrolls,
                'Crear Nóminas' => $canCreatePayrolls,
                'Editar Nóminas' => $canEditPayrolls,
                'Eliminar Nóminas' => $canDeletePayrolls,
                'Aprobar Nóminas' => $canApprovePayrolls,
                'Registrar Pagos' => $canPayPayrolls,
                'Calcular Nóminas' => $canCalculatePayrolls,
                'Generar Recibos' => $canGenerateReceipts,
            ],
        ]);
    }

    public function store(Request $request)
    {
        // 🔴 CORRECCIÓN: Captura la validación primero
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'periodo' => 'required|string|unique:payrolls,periodo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $user = User::findOrFail($validated['user_id']);

        $payroll = Payroll::create([
            'user_id' => $user->id,
            'periodo' => $validated['periodo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'salario_base' => $user->salario_base ?? 0,
            'estado' => 'Borrador',
        ]);

        // 🔴 CORRECCIÓN: Retorna redirect con flash correcto
        return redirect()->route('payroll.index')->with('flash', [
            'type' => 'success',
            'message' => 'Nómina creada exitosamente'
        ]);
    }

    public function edit(Payroll $payroll)
    {
        return response()->json([
            'id' => $payroll->id,
            'user_id' => $payroll->user_id,
            'periodo' => $payroll->periodo,
            'fecha_inicio' => $payroll->fecha_inicio,
            'fecha_fin' => $payroll->fecha_fin,
        ]);
    }

    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'periodo' => ['required', 'string', Rule::unique('payrolls')->ignore($payroll->id)],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $payroll->update($validated);

        return redirect()->route('payroll.index')->with('flash', [
            'type' => 'success',
            'message' => 'Nómina actualizada exitosamente'
        ]);
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->details()->delete();
        $payroll->delete();

        return redirect()->route('payroll.index')->with('flash', [
            'type' => 'success',
            'message' => 'Nómina eliminada exitosamente'
        ]);
    }

    public function calculate(Payroll $payroll)
    {
        $fechaInicio = Carbon::parse($payroll->fecha_inicio);
        $fechaFin = Carbon::parse($payroll->fecha_fin);
        
        $diasLaborables = 0;
        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            if (!$fecha->isWeekend()) {
                $diasLaborables++;
            }
        }

        $asistencias = Assistance::where('user_id', $payroll->user_id)
            ->whereBetween('fecha_entrada', [$fechaInicio, $fechaFin])
            ->where('entrada', true)
            ->where('salida', true)
            ->get();

        $minutosRetraso = 0;
        foreach ($asistencias as $asistencia) {
            $retraso = Affirmation::where('assistance_id', $asistencia->id)
                ->where('retraso', true)
                ->exists();
            
            if ($retraso) {
                $horaEntradaTurno = Carbon::parse('08:00');
                $horaEntradaReal = Carbon::parse($asistencia->hora_entrada);
                
                if ($horaEntradaReal->greaterThan($horaEntradaTurno)) {
                    $minutosRetraso += $horaEntradaTurno->diffInMinutes($horaEntradaReal);
                }
            }
        }

        $diasConAsistencia = $asistencias->pluck('fecha_entrada')->map->format('Y-m-d');
        
        $diasPermiso = PermissionRequest::where('user_id', $payroll->user_id)
            ->where('estado', 'Aprobado')
            ->whereBetween('fecha_inicio', [$fechaInicio, $fechaFin])
            ->pluck('fecha_inicio')
            ->map->format('Y-m-d');

        $diasFalta = 0;
        for ($fecha = $fechaInicio->copy(); $fecha->lte($fechaFin); $fecha->addDay()) {
            if (!$fecha->isWeekend() && 
                !$diasConAsistencia->contains($fecha->format('Y-m-d')) &&
                !$diasPermiso->contains($fecha->format('Y-m-d'))) {
                $diasFalta++;
            }
        }

        $salarioDiario = ($payroll->salario_base / 30);
        $salarioMinuto = ($salarioDiario / 8 / 60);

        $descuentoRetraso = $minutosRetraso * $salarioMinuto;
        $descuentoFalta = $diasFalta * $salarioDiario;

        $payroll->update([
            'dias_laborables' => $diasLaborables,
            'dias_trabajados' => $asistencias->count(),
            'dias_falta' => $diasFalta,
            'minutos_retraso' => $minutosRetraso,
            'total_descuentos_retraso' => round($descuentoRetraso, 2),
            'total_descuentos_falta' => round($descuentoFalta, 2),
            'neto_a_pagar' => round($payroll->salario_base - $descuentoRetraso - $descuentoFalta, 2),
            'estado' => 'Calculado',
        ]);

        $payroll->details()->delete();
        
        if ($minutosRetraso > 0) {
            $payroll->details()->create([
                'concepto' => "Retraso acumulado: {$minutosRetraso} min",
                'cantidad_minutos_dias' => $minutosRetraso,
                'monto_unitario' => round($salarioMinuto, 4),
                'total' => round($descuentoRetraso, 2),
                'fuente' => 'retraso',
            ]);
        }

        if ($diasFalta > 0) {
            $payroll->details()->create([
                'concepto' => "Faltas injustificadas: {$diasFalta} días",
                'cantidad_minutos_dias' => $diasFalta,
                'monto_unitario' => round($salarioDiario, 2),
                'total' => round($descuentoFalta, 2),
                'fuente' => 'falta',
            ]);
        }

        return redirect()->route('payroll.index')->with('flash', [
            'type' => 'success',
            'message' => 'Nómina calculada exitosamente'
        ]);
    }

    public function approve(Payroll $payroll)
    {
        if ($payroll->estado !== 'Calculado') {
            return back()->withErrors(['error' => 'Solo se pueden aprobar nóminas calculadas']);
        }

        $payroll->update(['estado' => 'Aprobado']);

        return redirect()->route('payroll.index')->with('flash', [
            'type' => 'success',
            'message' => 'Nómina aprobada exitosamente'
        ]);
    }

    public function pay(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'metodo_pago' => 'required|in:Efectivo,Transferencia,Cheque',
            'fecha_pago' => 'required|date',
        ]);

        if ($payroll->estado !== 'Aprobado') {
            return back()->withErrors(['error' => 'Solo se pueden pagar nóminas aprobadas']);
        }

        $payroll->update([
            'estado' => 'Pagado',
            'metodo_pago' => $validated['metodo_pago'],
            'fecha_pago' => $validated['fecha_pago'],
        ]);

        return redirect()->route('payroll.index')->with('flash', [
            'type' => 'success',
            'message' => 'Pago registrado exitosamente'
        ]);
    }

    public function generateReceipt(Payroll $payroll)
    {
        $payroll->load(['user', 'details']);

        $pdf = Pdf::loadView('pdf.payroll-receipt', [
            'payroll' => $payroll,
            'generated_at' => now(),
        ])->setPaper('A4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "recibo_{$payroll->user->codigo}_{$payroll->periodo}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}