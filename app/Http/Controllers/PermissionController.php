<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\Authorization;
use App\Models\Titulation;
use App\Models\GroupManager;
use App\Models\HrManager;
use App\Models\Compensation; // ← Importar
use App\Models\Overtime; // ← Importar
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // ← Importar
use Illuminate\Validation\ValidationException; // ← Importar

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->canDo('Ver Autorizaciones')) {
            abort(403, 'No tienes permiso para ver las solicitudes de permiso.');
        }

        // Calcular horas disponibles para compensaciones
        $horasDisponibles = $this->calcularHorasDisponibles($user->id);

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
            'horasDisponibles' => $horasDisponibles, // ← Pasar a la vista
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

        // Calcular cantidad solicitada
        $cantidadSolicitada = 0;
        if ($request->tipo === 'dias' && $request->fecha_inicio && $request->fecha_fin) {
            $start = \Carbon\Carbon::parse($request->fecha_inicio);
            $end = \Carbon\Carbon::parse($request->fecha_fin);
            $cantidadSolicitada = $start->diffInDays($end) + 1;
            $validated['cantidad_dias'] = $cantidadSolicitada;
            $validated['cantidad_horas'] = null;
        } elseif ($request->tipo === 'horas' && $request->hora_inicio && $request->hora_fin) {
            $start = \Carbon\Carbon::parse($request->hora_inicio);
            $end = \Carbon\Carbon::parse($request->hora_fin);
            $cantidadSolicitada = $start->diffInHours($end);
            $validated['cantidad_horas'] = $cantidadSolicitada;
            $validated['cantidad_dias'] = null;
        }

        $validated['user_id'] = $user->id;
        $validated['estado'] = 'Pendiente';

        // VERIFICAR SI ES COMPENSACION
        $authorization = Authorization::find($request->authorization_id);
        if ($authorization && $authorization->nombre === 'Compensacion') {
            // Solo permite tipo 'horas'
            if ($request->tipo !== 'horas') {
                throw ValidationException::withMessages([
                    'tipo' => 'Las compensaciones solo pueden ser por horas'
                ]);
            }

            // Calcular horas disponibles
            $horasDisponibles = $this->calcularHorasDisponibles($user->id);

            // Validar que tenga suficientes horas
            if ($cantidadSolicitada > $horasDisponibles) {
                throw ValidationException::withMessages([
                    'cantidad_horas' => "No tienes suficientes horas disponibles. Tienes {$horasDisponibles} hrs, solicitas {$cantidadSolicitada} hrs."
                ]);
            }

            // Realizar la resta de compensaciones y crear el permiso en transacción
            DB::transaction(function () use ($user, $cantidadSolicitada, $validated) {
                $this->restarCompensaciones($user->id, $cantidadSolicitada);
                PermissionRequest::create($validated);
            });

            return redirect()->route('permissions.index')
                ->with('success', 'Permiso de compensación creado exitosamente. Horas restadas de tus compensaciones.');
        }

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

        // Detectar si es compensación ANTES de actualizar
        $isCompensacion = $permission->authorization && $permission->authorization->nombre === 'Compensacion';
        $isRechazando = $validated['estado'] === 'Rechazado';

        $updateData = ['estado' => $validated['estado']];

        if ($isRechazando) {
            if (empty($request->observaciones)) {
                return back()->withErrors(['observaciones' => 'El motivo de rechazo es obligatorio']);
            }
            $updateData['observaciones'] = $request->observaciones;
            $updateData['report_id'] = $user->id;
        }

        if ($isHrManager && $validated['estado'] === 'Aprobado') {
            $updateData['estado'] = 'Completado';
        }

        // ✅ CORREGIDO: Usar $permission->user_id (el solicitante)
        if ($isCompensacion && $isRechazando && $permission->cantidad_horas > 0) {
            DB::transaction(function () use ($permission, $updateData) {
                // ✅ ESTA ES LA LÍNEA CRÍTICA CORREGIDA
                $this->devolverCompensaciones($permission->user_id, $permission->cantidad_horas);
                
                // Actualizar el permiso
                $permission->update($updateData);
            });

            return redirect()->back()
                ->with('success', 'Permiso rechazado y horas de compensación devueltas exitosamente.');
        }

        // Para otros casos, actualizar normalmente
        $permission->update($updateData);

        $mensajeExito = $isRechazando ? 'Permiso rechazado correctamente.' : 'Estado actualizado correctamente.';
        
        return redirect()->back()
            ->with('success', $mensajeExito);
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

        // Calcular nueva cantidad solicitada
        $cantidadSolicitada = 0;
        if ($request->tipo === 'dias' && $request->fecha_inicio && $request->fecha_fin) {
            $start = \Carbon\Carbon::parse($request->fecha_inicio);
            $end = \Carbon\Carbon::parse($request->fecha_fin);
            $cantidadSolicitada = $start->diffInDays($end) + 1;
            $validated['cantidad_dias'] = $cantidadSolicitada;
            $validated['cantidad_horas'] = null;
        } elseif ($request->tipo === 'horas' && $request->hora_inicio && $request->hora_fin) {
            $start = \Carbon\Carbon::parse($request->hora_inicio);
            $end = \Carbon\Carbon::parse($request->hora_fin);
            $cantidadSolicitada = $start->diffInHours($end);
            $validated['cantidad_horas'] = $cantidadSolicitada;
            $validated['cantidad_dias'] = null;
        }

        $validated['estado'] = 'Pendiente';
        $validated['observaciones'] = null;
        $validated['report_id'] = null;

        // ✅ SOLO RESTAR - IGUAL QUE CREATE
        if ($permission->authorization->nombre === 'Compensacion') {
            // Solo permite tipo 'horas'
            if ($request->tipo !== 'horas') {
                throw ValidationException::withMessages([
                    'tipo' => 'Las compensaciones solo pueden ser por horas'
                ]);
            }

            // Calcular horas disponibles (YA INCLUYE las devueltas al rechazar)
            $horasDisponibles = $this->calcularHorasDisponibles($user->id);

            // Validar que tenga suficientes horas
            if ($cantidadSolicitada > $horasDisponibles) {
                throw ValidationException::withMessages([
                    'cantidad_horas' => "No tienes suficientes horas disponibles. Tienes {$horasDisponibles} hrs, solicitas {$cantidadSolicitada} hrs."
                ]);
            }

            // ✅ RESTAR DIRECTAMENTE (sin devolver primero)
            DB::transaction(function () use ($user, $cantidadSolicitada, $validated, $permission) {
                $this->restarCompensaciones($user->id, $cantidadSolicitada);
                $permission->update($validated);
            });

            return redirect()->route('permissions.index')
                ->with('success', 'Permiso de compensación reenviado exitosamente. Horas restadas de tus compensaciones.');
        }

        $permission->update($validated);

        return redirect()->route('permissions.index')
            ->with('success', 'Permiso reenviado correctamente.');
    }

    /* ---------- MÉTODOS AUXILIARES (sin Service) ---------- */

    /**
     * Calcular horas disponibles de compensaciones para un usuario
     */
    private function calcularHorasDisponibles($userId): float
    {
        return Compensation::whereHas('overtime', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->sum('quantity') ?? 0;
    }

    private function restarCompensaciones($userId, $horasARestar): void
    {
        $compensations = Compensation::whereHas('overtime', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orderBy('quantity', 'desc')->get();

        $restantes = $horasARestar;

        foreach ($compensations as $compensation) {
            if ($restantes <= 0) break;

            if ($compensation->quantity <= $restantes) {
                // Se usa toda la compensación
                $restantes -= $compensation->quantity;
                
                // ✅ Marcar overtime como USADO (siempre)
                $compensation->overtime->update(['estado' => Overtime::USADO]);
                
                // ✅ ACTUALIZAR a 0 en lugar de eliminar
                $compensation->update(['quantity' => 0]);
            } else {
                // Solo se usa parte
                $compensation->update(['quantity' => $compensation->quantity - $restantes]);
                $restantes = 0;
            }
        }
    }

    /**
     * Devolver horas a compensaciones (para rechazo de permisos)
     * Incrementa quantity y vuelve overtime de USADO a APROBADO
     */
    private function devolverCompensaciones($userId, $horasADevolver): void
    {
        // Buscar compensaciones del usuario
        $compensation = Compensation::whereHas('overtime', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orderBy('quantity', 'asc')->first();

        if ($compensation) {
            $compensation->increment('quantity', $horasADevolver);
            
            // Volver overtime de USADO a APROBADO
            if ($compensation->overtime->estado === Overtime::USADO) {
                $compensation->overtime->update(['estado' => Overtime::APROBADO]);
            }
        } else {
            // Si no hay compensaciones, buscar un overtime USADO para reactivar
            $overtime = Overtime::where('user_id', $userId)
                ->where('estado', Overtime::USADO)
                ->latest()
                ->first();
            
            if ($overtime) {
                Compensation::create([
                    'overtime_id' => $overtime->id,
                    'quantity' => $horasADevolver,
                ]);
                $overtime->update(['estado' => Overtime::APROBADO]);
            }
        }
    }
}