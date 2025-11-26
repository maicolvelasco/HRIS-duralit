<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\GroupManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OvertimeController extends Controller
{
    /* ---------- Listar ---------- */
    public function index()
    {
        // Obtener IDs de usuarios bajo el jefe logueado
        $subordinateIds = [];
        $isManager = false;
        
        $manager = GroupManager::where('user_id', auth()->id())->first();
        if ($manager) {
            $isManager = true;
            $subordinateIds = $manager->group->users()->pluck('id')->toArray();
        }

        // Sobretiempos del usuario logueado
        $rows = Overtime::with('user:id,codigo,nombre,apellido')
                        ->where('user_id', auth()->id())
                        ->orderByDesc('fecha')
                        ->get();

        // Sumar solo horas con estado 'Disponible'
        $totalDisponible = $rows->where('estado', 'Aprobado')->sum('contador');

        // Sobretiempos de subordinados (para vista de jefe)
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

    /* ---------- NUEVO: Actualizar estado (solo jefe) ---------- */
    public function updateStatus(Request $request, Overtime $overtime)
    {
        // Verificar que el usuario logueado sea jefe del usuario dueño del registro
        $manager = GroupManager::where('user_id', auth()->id())->first();
        
        if (!$manager || !$manager->group->users()->where('id', $overtime->user_id)->exists()) {
            abort(403, 'No autorizado');
        }

        $request->validate([
            'estado' => 'required|in:Pendiente,Aprobado'
        ]);

        $overtime->update(['estado' => $request->estado]);

        return redirect()->back()->with('success', 'Estado actualizado correctamente.');
    }

    /* ---------- Vista de Sobretiempos del Equipo ---------- */
    public function team()
    {
        // Verificar si el usuario logueado es jefe
        $manager = GroupManager::where('user_id', auth()->id())->first();
        
        if (!$manager) {
            return redirect()->route('overtimes.index')
                ->with('error', 'No tienes permiso para ver esta sección.');
        }

        // IDs de subordinados
        $subordinateIds = $manager->group->users()->pluck('id')->toArray();
        
        // ✅ TRAE TODOS los sobretiempos del equipo (sin filtrar por estado)
        $subordinatesOvertimes = Overtime::with('user:id,codigo,nombre,apellido')
            ->whereIn('user_id', $subordinateIds) // Quita: ->where('estado', 'Pendiente')
            ->orderByRaw("CASE WHEN estado = 'Pendiente' THEN 1 ELSE 2 END") // Pendientes primero
            ->orderByDesc('fecha')
            ->get();

        // Horas disponibles del usuario logueado
        $totalDisponible = Overtime::where('user_id', auth()->id())
            ->where('estado', 'Aprobado')
            ->sum('contador');

        return Inertia::render('Overtime/Team', [
            'totalDisponible' => $totalDisponible,
            'subordinatesOvertimes' => $subordinatesOvertimes,
            'isManager' => true,
        ]);
    }
}