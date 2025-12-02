<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Assistance;
use App\Models\PermissionRequest;
use App\Models\Payroll;
use App\Models\Overtime;
use App\Models\GroupManager;
use App\Models\HrManager;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Section;
use App\Models\Shift;
use App\Models\Calendar;
use App\Models\Rol;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Determinar nivel de acceso
        $isHrManager = HrManager::where('user_id', $user->id)->exists();
        $manager = GroupManager::where('user_id', $user->id)->first();
        $isGroupManager = $manager !== null;
        
        // IDs de subordinados si es jefe
        $subordinateIds = $isGroupManager 
            ? $manager->group->users()->pluck('id')->toArray() 
            : [];

        // Estadísticas según permisos
        $stats = $this->getStats($user, $isHrManager, $isGroupManager, $subordinateIds);
        
        // Datos para gráficos
        $charts = $this->getCharts($user, $isHrManager, $isGroupManager, $subordinateIds);
        
        // Registros recientes
        $recentData = $this->getRecentData($user, $isHrManager, $isGroupManager, $subordinateIds);
        
        return Inertia::render('Dashboard/Dashboard', [
            'stats' => $stats,
            'charts' => $charts,
            'recentData' => $recentData,
            'filters' => [
                'isHrManager' => $isHrManager,
                'isGroupManager' => $isGroupManager,
                'canViewGlobal' => $user->canDo('Ver Reportes'),
            ]
        ]);
    }

    private function getStats($user, $isHrManager, $isGroupManager, $subordinateIds)
    {
        $stats = [];

        // 🎯 USUARIOS
        if ($user->canDo('Ver Usuarios')) {
            $query = User::query();
            if ($isGroupManager && !$isHrManager) {
                $query->whereIn('id', $subordinateIds);
            }
            
            $stats['users'] = [
                'total' => $query->count(),
                'active' => (clone $query)->where('is_active', true)->count(),
                'new_this_month' => (clone $query)->whereMonth('created_at', Carbon::now()->month)->count(),
            ];
        }

        // 🏢 SUCURSALES
        if ($user->canDo('Ver Sucursales')) {
            $stats['branches'] = [
                'total' => Branch::count(),
                'active' => Branch::whereHas('users')->count(),
            ];
        }

        // 👥 GRUPOS
        if ($user->canDo('Ver Grupos')) {
            $stats['groups'] = [
                'total' => Group::count(),
                'with_users' => Group::whereHas('users')->count(),
            ];
        }

        // 📋 SECCIONES
        if ($user->canDo('Ver Secciones')) {
            $stats['sections'] = [
                'total' => Section::count(),
                'with_users' => Section::whereHas('users')->count(),
            ];
        }

        // 🎭 ROLES
        if ($user->canDo('Ver Roles')) {
            $stats['roles'] = [
                'total' => Rol::count(),
                'with_users' => Rol::whereHas('users')->count(),
            ];
        }

        // 📊 ASISTENCIAS
        if ($user->canDo('Control de Asistencia') || $user->canDo('Control de Asistencia Propio')) {
            $today = Carbon::today();
            $query = Assistance::query();
            
            if ($user->canDo('Control de Asistencia Propio') && !$user->canDo('Control de Asistencia')) {
                $query->where('user_id', $user->id);
            } elseif ($isGroupManager && !$isHrManager) {
                $query->whereIn('user_id', $subordinateIds);
            }
            
            $todayAssists = (clone $query)->whereDate('fecha_entrada', $today);
            
            $stats['attendance'] = [
                'today' => $todayAssists->count(),
                'delays' => $todayAssists->whereHas('affirmation', fn($q) => $q->where('retraso', true))->count(),
                'missing' => $this->calculateMissing($user, $isHrManager, $isGroupManager, $subordinateIds),
            ];
        }

        // 📝 PERMISOS
        if ($user->canDo('Ver Autorizaciones')) {
            $query = PermissionRequest::query();
            if ($isHrManager) {
                $query->where('estado', 'Aprobado');
            } elseif ($isGroupManager) {
                $query->whereIn('user_id', $subordinateIds)->where('estado', 'Pendiente');
            } else {
                $query->where('user_id', $user->id);
            }
            
            $stats['permissions'] = [
                'pending' => (clone $query)->where('estado', 'Pendiente')->count(),
                'approved' => (clone $query)->where('estado', 'Aprobado')->count(),
                'completed' => (clone $query)->where('estado', 'Completado')->count(),
            ];
        }

        // 💰 NÓMINAS
        if ($user->canDo('Ver Nóminas')) {
            $query = Payroll::query();
            if (!$isHrManager && !$isGroupManager) {
                $query->where('user_id', $user->id);
            }
            
            $stats['payrolls'] = [
                'draft' => (clone $query)->where('estado', 'Borrador')->count(),
                'approved' => (clone $query)->where('estado', 'Aprobado')->count(),
                'total_amount' => (clone $query)->whereIn('estado', ['Aprobado', 'Pagado'])
                    ->sum('neto_a_pagar'),
            ];
        }

        // ⏰ HORAS EXTRAS
        if ($user->canDo('Ver Sobretiempos') || $user->canDo('Ver Sobretiempos Propio')) {
            $query = Overtime::query();
            if ($user->canDo('Ver Sobretiempos Propio') && !$user->canDo('Ver Sobretiempos')) {
                $query->where('user_id', $user->id);
            } elseif ($isGroupManager && !$isHrManager) {
                $query->whereIn('user_id', $subordinateIds);
            }
            
            $stats['overtimes'] = [
                'pending' => (clone $query)->where('estado', 'Pendiente')->sum('contador'),
                'approved' => (clone $query)->where('estado', 'Aprobado')->sum('contador'),
                'available' => $this->calculateAvailableOvertime($user->id),
            ];
        }

        // 📅 TURNOS
        if ($user->canDo('Ver Turnos')) {
            $stats['shifts'] = [
                'active' => Shift::where('hasta', '>=', Carbon::today())->count(),
                'expired' => Shift::where('hasta', '<', Carbon::today())->count(),
            ];
        }

        // 🗓️ FERIADOS
        if ($user->canDo('Ver Feriados')) {
            $stats['holidays'] = [
                'this_month' => Calendar::whereMonth('fecha', Carbon::now()->month)->count(),
                'next_month' => Calendar::whereMonth('fecha', Carbon::now()->addMonth()->month)->count(),
            ];
        }

        return $stats;
    }

    private function getCharts($user, $isHrManager, $isGroupManager, $subordinateIds)
    {
        $charts = [];

        // 📈 Asistencias últimos 7 días
        if ($user->canDo('Control de Asistencia') || $user->canDo('Control de Asistencia Propio')) {
            $labels = [];
            $data = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('d/m');
                
                $query = Assistance::whereDate('fecha_entrada', $date);
                if ($user->canDo('Control de Asistencia Propio') && !$user->canDo('Control de Asistencia')) {
                    $query->where('user_id', $user->id);
                } elseif ($isGroupManager && !$isHrManager) {
                    $query->whereIn('user_id', $subordinateIds);
                }
                
                $data[] = $query->count();
            }
            
            $charts['attendanceTrend'] = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Asistencias',
                        'data' => $data,
                        'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                        'borderColor' => 'rgb(59, 130, 246)',
                        'tension' => 0.4,
                    ]
                ]
            ];
        }

        // 🥧 Distribución de usuarios por sucursal
        if ($user->canDo('Ver Usuarios') && $user->canDo('Ver Sucursales')) {
            $query = User::with('branch')->select('branch_id', DB::raw('count(*) as total'));
            
            if ($isGroupManager && !$isHrManager) {
                $query->whereIn('id', $subordinateIds);
            }
            
            $branchData = $query->groupBy('branch_id')->get();
            
            $charts['usersByBranch'] = [
                'labels' => $branchData->pluck('branch.nombre')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Usuarios por Sucursal',
                        'data' => $branchData->pluck('total')->toArray(),
                        'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    ]
                ]
            ];
        }

        // 📊 Estado de nóminas
        if ($user->canDo('Ver Nóminas')) {
            $query = Payroll::select('estado', DB::raw('count(*) as total'));
            
            if (!$isHrManager && !$isGroupManager) {
                $query->where('user_id', $user->id);
            }
            
            $payrollStatus = $query->groupBy('estado')->get();
            
            $charts['payrollStatus'] = [
                'labels' => $payrollStatus->pluck('estado')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Estado de Nóminas',
                        'data' => $payrollStatus->pluck('total')->toArray(),
                        'backgroundColor' => ['#fbbf24', '#10b981', '#3b82f6', '#6b7280'],
                    ]
                ]
            ];
        }

        // 📊 Usuarios por Grupo
        if ($user->canDo('Ver Grupos')) {
            $query = User::with('group')->select('group_id', DB::raw('count(*) as total'));
            if ($isGroupManager && !$isHrManager) $query->whereIn('id', $subordinateIds);
            $groupData = $query->groupBy('group_id')->get();

            $charts['usersByGroup'] = [
                'labels' => $groupData->pluck('group.nombre')->toArray(),
                'datasets' => [[
                    'label' => 'Usuarios por Grupo',
                    'data' => $groupData->pluck('total')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6']
                ]]
            ];
        }

        // 📊 Usuarios por Sección
        if ($user->canDo('Ver Secciones')) {
            $query = User::with('section')->select('section_id', DB::raw('count(*) as total'));
            if ($isGroupManager && !$isHrManager) $query->whereIn('id', $subordinateIds);
            $sectionData = $query->groupBy('section_id')->get();

            $charts['usersBySection'] = [
                'labels' => $sectionData->pluck('section.nombre')->toArray(),
                'datasets' => [[
                    'label' => 'Usuarios por Sección',
                    'data' => $sectionData->pluck('total')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']
                ]]
            ];
        }

        // 📊 Usuarios por Rol
        if ($user->canDo('Ver Roles')) {
            $query = User::with('rol')->select('rol_id', DB::raw('count(*) as total'));
            if ($isGroupManager && !$isHrManager) $query->whereIn('id', $subordinateIds);
            $roleData = $query->groupBy('rol_id')->get();

            $charts['usersByRole'] = [
                'labels' => $roleData->pluck('rol.nombre')->toArray(),
                'datasets' => [[
                    'label' => 'Usuarios por Rol',
                    'data' => $roleData->pluck('total')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899']
                ]]
            ];
        }

        // 💰 Nóminas por mes
        if ($user->canDo('Ver Nóminas')) {
            $months = collect([]);
            $amounts = collect([]);

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months->push($date->format('M Y'));

                $amount = Payroll::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->whereIn('estado', ['Aprobado', 'Pagado']);

                if (!$isHrManager && !$isGroupManager) {
                    $amount->where('user_id', $user->id);
                }

                $amounts->push($amount->sum('neto_a_pagar'));
            }

            $charts['payrollByMonth'] = [
                'labels' => $months->toArray(),
                'datasets' => [[
                    'label' => 'Monto Total (BOB)',
                    'data' => $amounts->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'tension' => 0.4
                ]]
            ];
        }

        // ⏰ Horas extras por mes
        if ($user->canDo('Ver Sobretiempos')) {
            $months = collect([]);
            $hours = collect([]);

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months->push($date->format('M Y'));

                $query = Overtime::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('estado', 'Aprobado');

                if ($isGroupManager && !$isHrManager) {
                    $query->whereIn('user_id', $subordinateIds);
                }

                $hours->push($query->sum('contador'));
            }

            $charts['overtimeByMonth'] = [
                'labels' => $months->toArray(),
                'datasets' => [[
                    'label' => 'Horas Aprobadas',
                    'data' => $hours->toArray(),
                    'backgroundColor' => 'rgba(245, 158, 11, 0.5)',
                    'borderColor' => 'rgb(245, 158, 11)'
                ]]
            ];
        }

        return $charts;
    }

    private function getRecentData($user, $isHrManager, $isGroupManager, $subordinateIds)
    {
        $data = [];

        // Últimas asistencias
        if ($user->canDo('Control de Asistencia') || $user->canDo('Control de Asistencia Propio')) {
            $query = Assistance::with('user:id,nombre,apellido,codigo')
                ->latest()
                ->limit(10);
            
            if ($user->canDo('Control de Asistencia Propio') && !$user->canDo('Control de Asistencia')) {
                $query->where('user_id', $user->id);
            } elseif ($isGroupManager && !$isHrManager) {
                $query->whereIn('user_id', $subordinateIds);
            }
            
            $data['recentAssistances'] = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user' => $item->user->nombre . ' ' . $item->user->apellido,
                    'codigo' => $item->user->codigo,
                    'fecha' => Carbon::parse($item->fecha_entrada)->format('d/m/Y'),
                    'entrada' => $item->hora_entrada,
                    'salida' => $item->hora_salida,
                    'retraso' => $item->affirmation?->retraso ?? false,
                ];
            });
        }

        // Usuarios recientes
        if ($user->canDo('Ver Usuarios')) {
            $query = User::with(['branch:id,nombre', 'group:id,nombre', 'rol:id,nombre'])
                ->select('id', 'nombre', 'apellido', 'codigo', 'branch_id', 'group_id', 'rol_id', 'created_at')
                ->latest()
                ->limit(10);

            if ($isGroupManager && !$isHrManager) {
                $query->whereIn('id', $subordinateIds);
            }

            $data['recentUsers'] = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->codigo,
                    'nombre' => $item->nombre . ' ' . $item->apellido,
                    'sucursal' => $item->branch?->nombre ?? 'Sin sucursal',
                    'grupo' => $item->group?->nombre ?? 'Sin grupo',
                    'rol' => $item->rol?->nombre ?? 'Sin rol',
                    'fecha' => Carbon::parse($item->created_at)->format('d/m/Y'),
                ];
            });
        }

        // Permisos pendientes
        if ($user->canDo('Ver Autorizaciones')) {
            $query = PermissionRequest::with(['user:id,nombre,apellido,codigo', 'authorization'])
                ->where('estado', 'Pendiente')
                ->latest()
                ->limit(5);
            
            if ($isGroupManager && !$isHrManager) {
                $query->whereIn('user_id', $subordinateIds);
            } elseif (!$isHrManager && !$isGroupManager) {
                $query->where('user_id', $user->id);
            }
            
            $data['pendingPermissions'] = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->user->codigo,
                    'user' => $item->user->nombre . ' ' . $item->user->apellido,
                    'tipo' => $item->authorization->nombre,
                    'motivo' => Str::limit($item->motivo, 30),
                    'fecha' => Carbon::parse($item->created_at)->format('d/m/Y'),
                ];
            });
        }

        // Horas extras pendientes
        if ($user->canDo('Ver Sobretiempos')) {
            $query = Overtime::with('user:id,nombre,apellido,codigo')
                ->where('estado', 'Pendiente')
                ->latest()
                ->limit(5);

            if ($isGroupManager && !$isHrManager) {
                $query->whereIn('user_id', $subordinateIds);
            }

            $data['pendingOvertimes'] = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->user->codigo,
                    'user' => $item->user->nombre . ' ' . $item->user->apellido,
                    'fecha' => Carbon::parse($item->fecha)->format('d/m/Y'),
                    'horas' => $item->contador,
                    'estado' => $item->estado,
                ];
            });
        }

        // Nóminas recientes
        if ($user->canDo('Ver Nóminas')) {
            $query = Payroll::with(['user:id,nombre,apellido,codigo'])
                ->latest()
                ->limit(5);

            if (!$isHrManager && !$isGroupManager) {
                $query->where('user_id', $user->id);
            }

            $data['recentPayrolls'] = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->user->codigo,
                    'user' => $item->user->nombre . ' ' . $item->user->apellido,
                    'periodo' => $item->periodo,
                    'neto' => $item->neto_a_pagar,
                    'estado' => $item->estado,
                ];
            });
        }

        // Horas extras aprobadas
        if ($user->canDo('Ver Sobretiempos')) {
            $query = Overtime::with('user:id,nombre,apellido,codigo')
                ->where('estado', 'Aprobado')
                ->latest()
                ->limit(5);

            if ($isGroupManager && !$isHrManager) {
                $query->whereIn('user_id', $subordinateIds);
            }

            $data['recentOvertimes'] = $query->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'codigo' => $item->user->codigo,
                    'user' => $item->user->nombre . ' ' . $item->user->apellido,
                    'fecha' => Carbon::parse($item->fecha)->format('d/m/Y'),
                    'horas' => $item->contador,
                    'monto' => $item->contador * 50, // Ejemplo: 50 BOB por hora
                ];
            });
        }

        return $data;
    }

    private function calculateMissing($user, $isHrManager, $isGroupManager, $subordinateIds)
    {
        // Lógica avanzada para calcular faltas basadas en turnos
        // Esto es un placeholder - implementa según tus reglas
        return 0;
    }

    private function calculateAvailableOvertime($userId)
    {
        return \App\Models\Compensation::whereHas('overtime', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->sum('quantity') ?? 0;
    }
}