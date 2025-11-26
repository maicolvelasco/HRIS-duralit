<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\GroupManager;
use App\Models\Compensation; // ← Importar el modelo
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // ← Importar DB para transacciones
use Inertia\Inertia;

class OvertimeController extends Controller
{
    /* ---------- Listar ---------- */
    public function index()
    {
        $subordinateIds = [];
        $isManager = false;
        
        $manager = GroupManager::where('user_id', auth()->id())->first();
        if ($manager) {
            $isManager = true;
            $subordinateIds = $manager->group->users()->pluck('id')->toArray();
        }

        $rows = Overtime::with('user:id,codigo,nombre,apellido')
                        ->where('user_id', auth()->id())
                        ->orderByDesc('fecha')
                        ->get();

        $totalDisponible = auth()->user()->compensations()->sum('quantity');

        $subordinatesOvertimes = [];
        if ($isManager) {
            $subordinatesOvertimes = Overtime::with('user:id,codigo,nombre,apellido')
                ->whereIn('user_id', $subordinateIds)
                ->where('estado', 'Pendiente')
                ->orderByDesc('fecha')
                ->get();
        }

        return Inertia::render('Overtime/Index', [
            'overtimes' => $rows,
            'totalDisponible' => $totalDisponible,
            'isManager' => $isManager,
            'subordinatesOvertimes' => $subordinatesOvertimes,
        ]);
    }

    /* ---------- Guardar nuevo ---------- */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id'   => 'required|exists:users,id',
            'fecha'     => 'required|date',
            'desde'     => 'required|date_format:H:i',
            'hasta'     => 'required|date_format:H:i|after:desde',
            'contador'  => 'required|numeric|min:0.01',
            'trabajo'   => 'required|string|max:1000',
            'estado'    => 'required|in:Pendiente,Aprobado,Usado',
        ], [
            'hasta.after' => 'La hora hasta debe ser posterior a la hora desde.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['estado']  = 'Pendiente';

        Overtime::create($validated);

        return redirect()->route('overtimes.index')
                         ->with('success', 'Sobretiempo registrado correctamente.');
    }

    /* ---------- Actualizar estado (solo jefe) ---------- */
    public function updateStatus(Request $request, Overtime $overtime)
    {
        // Verificar que el usuario logueado sea jefe del dueño del registro
        $manager = GroupManager::where('user_id', auth()->id())->first();
        
        if (!$manager || !$manager->group->users()->where('id', $overtime->user_id)->exists()) {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'estado' => 'required|in:Pendiente,Aprobado'
        ]);

        // Si ya está aprobado, no hacer nada
        if ($overtime->estado === 'Aprobado') {
            return redirect()->back()->with('info', 'El sobretiempo ya estaba aprobado.');
        }

        // Verificar que no exista compensación previa (evitar duplicados)
        if ($request->estado === 'Aprobado') {
            $existingCompensation = Compensation::where('overtime_id', $overtime->id)->exists();
            
            if ($existingCompensation) {
                return redirect()->back()->with('error', 'Ya existe una compensación para este sobretiempo.');
            }
        }

        // Transacción para asegurar consistencia
        DB::transaction(function () use ($request, $overtime) {
            // Actualizar estado
            $overtime->update(['estado' => $request->estado]);

            // Si se aprueba, crear compensación
            if ($request->estado === 'Aprobado') {
                Compensation::create([
                    'overtime_id' => $overtime->id,
                    'quantity' => $overtime->contador,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }

    /* ---------- Vista de Sobretiempos del Equipo ---------- */
    public function team()
    {
        $manager = GroupManager::where('user_id', auth()->id())->first();
        
        if (!$manager) {
            return redirect()->route('overtimes.index')
                ->with('error', 'No tienes permiso para ver esta sección.');
        }

        $subordinateIds = $manager->group->users()->pluck('id')->toArray();
        
        $subordinatesOvertimes = Overtime::with('user:id,codigo,nombre,apellido')
            ->whereIn('user_id', $subordinateIds)
            ->orderByRaw("CASE WHEN estado = 'Pendiente' THEN 1 ELSE 2 END")
            ->orderByDesc('fecha')
            ->get();

        $totalDisponible = auth()->user()->compensations()->sum('quantity');

        return Inertia::render('Overtime/Team', [
            'totalDisponible' => $totalDisponible,
            'subordinatesOvertimes' => $subordinatesOvertimes,
            'isManager' => true,
        ]);
    }
}