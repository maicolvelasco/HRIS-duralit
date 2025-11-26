<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\Authorization;
use App\Models\Titulation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canDo('Ver Autorizaciones')) {
            abort(403, 'No tienes permiso para ver las solicitudes de permiso.');
        }

        // Cargar permisos con relaciones
        $query = PermissionRequest::with(['user', 'authorization', 'titulation'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('motivo', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('apellido', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        $permissions = $query->paginate(10)->withQueryString();

        return Inertia::render('Permission/Index', [
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'estado']),
            'authorizations' => Authorization::with('titulations')->get(), // Cargar relación
            'titulations' => Titulation::all(),
        ]);
    }

    // NUEVO MÉTODO: Obtener titulaciones por autorización
    public function getTitulationsByAuthorization(Authorization $authorization)
    {
        return response()->json([
            'titulations' => $authorization->titulations ?? []
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'authorization_id' => 'required|exists:authorizations,id',
            'titulation_id' => 'nullable|exists:titulations,id',
            'motivo' => 'required|string|max:500',
            'tipo' => 'required|in:dias,horas',
            'fecha_inicio' => 'nullable|required_if:tipo,dias|date',
            'fecha_fin' => 'nullable|required_if:tipo,dias|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'nullable|required_if:tipo,horas',
            'hora_fin' => 'nullable|required_if:tipo,horas',
        ]);

        // Calcular cantidades AUTOMÁTICAMENTE
        if ($request->tipo === 'dias' && $request->fecha_inicio && $request->fecha_fin) {
            $start = \Carbon\Carbon::parse($request->fecha_inicio);
            $end = \Carbon\Carbon::parse($request->fecha_fin);
            $validated['cantidad_dias'] = $start->diffInDays($end) + 1;
            $validated['cantidad_horas'] = null;
        } elseif ($request->tipo === 'horas' && $request->fecha_inicio && $request->hora_inicio && $request->hora_fin) {
            $start = \Carbon\Carbon::parse($request->hora_inicio);
            $end = \Carbon\Carbon::parse($request->hora_fin);
            $validated['cantidad_horas'] = $start->diffInHours($end);
            $validated['cantidad_dias'] = null;
        }

        $validated['user_id'] = $user->id;
        $validated['estado'] = 'Pendiente'; // ESTADO AUTOMÁTICO

        PermissionRequest::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Solicitud de permiso creada exitosamente.');
    }

    public function update(Request $request, PermissionRequest $permission)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'authorization_id' => 'required|exists:authorizations,id',
            'titulation_id' => 'nullable|exists:titulations,id',
            'motivo' => 'required|string|max:500',
            'tipo' => 'required|in:dias,horas',
            'fecha_inicio' => 'nullable|required_if:tipo,dias|date',
            'fecha_fin' => 'nullable|required_if:tipo,dias|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'nullable|required_if:tipo,horas',
            'hora_fin' => 'nullable|required_if:tipo,horas',
            'cantidad_dias' => 'nullable|numeric|min:0.01|max:365',
            'cantidad_horas' => 'nullable|numeric|min:0.01|max:24',
        ]);

        $permission->update($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Solicitud de permiso actualizada exitosamente.');
    }

    public function destroy(PermissionRequest $permission)
    {
        $user = Auth::user();

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Solicitud de permiso eliminada exitosamente.');
    }

    public function updateStatus(Request $request, PermissionRequest $permission)
    {
        $user = Auth::user();

        $request->validate([
            'estado' => 'required|in:Aprobado,Rechazado,Completado',
            'observaciones' => 'nullable|string',
        ]);

        $permission->update([
            'estado' => $request->estado,
            'observaciones' => $request->observaciones,
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Estado actualizado exitosamente.');
    }
}