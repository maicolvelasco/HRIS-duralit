<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\Authorization;
use App\Models\Titulation;
use App\Models\GroupManager;
use App\Models\HrManager;
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

        // IMPORTANTE: Cargar la relación 'report' para mostrar quién rechazó
        $query = PermissionRequest::with(['user', 'authorization', 'titulation', 'report'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('motivo', 'like', "%{$search}%");
            });
        }

        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        $permissions = $query->paginate(10)->withQueryString();

        return Inertia::render('Permission/Index', [
            'permissions' => $permissions,
            'filters' => $request->only(['search', 'estado']),
            'authorizations' => Authorization::with('titulations')->get(),
            'titulations' => Titulation::all(),
        ]);
    }

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
        $validated['estado'] = 'Pendiente';

        PermissionRequest::create($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Solicitud de permiso creada exitosamente.');
    }

    public function team()
    {
        $user = Auth::user();
        
        $isHrManager = HrManager::where('user_id', $user->id)->exists();
        $manager = GroupManager::where('user_id', $user->id)->first();
        $isGroupManager = $manager !== null;
        
        if (!$isHrManager && !$isGroupManager) {
            return redirect()->route('permissions.index')
                ->with('error', 'No tienes permiso para ver esta sección.');
        }

        $query = PermissionRequest::with(['user', 'authorization', 'titulation']);

        if ($isHrManager) {
            $query->where('estado', 'Aprobado');
        } else {
            $subordinateIds = $manager->group->users()->pluck('id')->toArray();
            $query->whereIn('user_id', $subordinateIds)
                  ->where('estado', 'Pendiente');
        }

        $permissions = $query->orderByDesc('created_at')->get();

        return Inertia::render('Permission/Team', [
            'permissions' => $permissions,
            'isHrManager' => $isHrManager,
            'isGroupManager' => $isGroupManager,
        ]);
    }

    public function updateStatus(Request $request, PermissionRequest $permission)
    {
        $user = Auth::user();
        
        $isHrManager = HrManager::where('user_id', $user->id)->exists();
        $manager = GroupManager::where('user_id', $user->id)->first();
        $isGroupManager = $manager && $manager->group->users()->where('id', $permission->user_id)->exists();
        
        if (!$isHrManager && !$isGroupManager) {
            abort(403, 'No autorizado');
        }

        $validated = $request->validate([
            'estado' => 'required|in:Aprobado,Rechazado,Completado',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $updateData = ['estado' => $validated['estado']];

        if ($validated['estado'] === 'Rechazado') {
            if (empty($request->observaciones)) {
                return back()->withErrors(['observaciones' => 'El motivo de rechazo es obligatorio']);
            }
            $updateData['observaciones'] = $request->observaciones;
            $updateData['report_id'] = $user->id;
        }

        if ($isHrManager && $validated['estado'] === 'Aprobado') {
            $updateData['estado'] = 'Completado';
        }

        $permission->update($updateData);

        return redirect()->back()
            ->with('success', 'Estado actualizado correctamente.');
    }

    public function edit(PermissionRequest $permission)
    {
        $user = Auth::user();
        
        if ($permission->user_id !== $user->id) {
            abort(403, 'No autorizado');
        }

        if ($permission->estado !== 'Rechazado') {
            abort(403, 'Solo puedes editar permisos rechazados');
        }

        $permission->load(['authorization', 'titulation', 'report' => function($query) {
            $query->select('id', 'nombre', 'apellido', 'codigo');
        }]);

        return response()->json([
            'permission' => $permission,
            'authorizations' => Authorization::with('titulations')->get(),
            'titulations' => Titulation::all(),
        ]);
    }

    public function update(Request $request, PermissionRequest $permission)
    {
        $user = Auth::user();
        
        if ($permission->user_id !== $user->id) {
            abort(403, 'No autorizado');
        }

        if ($permission->estado !== 'Rechazado') {
            abort(403, 'Solo puedes editar permisos rechazados');
        }

        $validated = $request->validate([
            'motivo' => 'required|string|max:500',
            'tipo' => 'required|in:dias,horas',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'hora_inicio' => 'nullable',
            'hora_fin' => 'nullable',
        ]);

        if ($request->tipo === 'dias' && $request->fecha_inicio && $request->fecha_fin) {
            $start = \Carbon\Carbon::parse($request->fecha_inicio);
            $end = \Carbon\Carbon::parse($request->fecha_fin);
            $validated['cantidad_dias'] = $start->diffInDays($end) + 1;
            $validated['cantidad_horas'] = null;
            $validated['hora_inicio'] = null;
            $validated['hora_fin'] = null;
        } elseif ($request->tipo === 'horas' && $request->hora_inicio && $request->hora_fin) {
            $start = \Carbon\Carbon::parse($request->hora_inicio);
            $end = \Carbon\Carbon::parse($request->hora_fin);
            $validated['cantidad_horas'] = $start->diffInHours($end);
            $validated['cantidad_dias'] = null;
            $validated['fecha_fin'] = null;
        }

        $validated['estado'] = 'Pendiente';
        $validated['observaciones'] = null;
        $validated['report_id'] = null;

        $permission->update($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permiso reenviado correctamente.');
    }
}