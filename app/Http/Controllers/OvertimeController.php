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
}