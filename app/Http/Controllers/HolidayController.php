<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolidayController extends Controller
{
    // ✅ OPCIÓN 1: Comentar el constructor temporalmente para probar
    // public function __construct()
    // {
    //     $this->middleware('can:Ver Feriados')->only('index');
    //     $this->middleware('can:Crear Feriados')->only('store');
    //     $this->middleware('can:Modificar Feriados')->only('update');
    //     $this->middleware('can:Eliminar Feriados')->only('destroy');
    // }

    public function index(Request $request)
    {
        // Verificar permiso manualmente
        $user = auth()->user();
        if (!$user->canDo('Ver Feriados')) {
            abort(403, 'No tienes permiso para ver feriados');
        }

        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $holidays = Calendar::with('branch')
            ->whereMonth('fecha', $month)
            ->whereYear('fecha', $year)
            ->get()
            ->map(function ($holiday) {
                return [
                    'id' => $holiday->id,
                    'nombre' => $holiday->nombre,
                    'fecha' => $holiday->fecha,
                    'branch' => $holiday->branch,
                    'branch_id' => $holiday->branch_id,
                ];
            });

        return response()->json($holidays);
    }

    public function store(Request $request)
    {
        // Verificar permiso manualmente
        $user = auth()->user();
        if (!$user->canDo('Crear Feriados')) {
            abort(403, 'No tienes permiso para crear feriados');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $holiday = Calendar::create([
            'nombre' => $request->nombre,
            'fecha' => $request->fecha,
            'branch_id' => $request->branch_id,
        ]);

        $holiday->load('branch');

        return back()->with([
            'flash' => 'Feriado creado correctamente',
            'holiday' => [
                'id' => $holiday->id,
                'nombre' => $holiday->nombre,
                'fecha' => $holiday->fecha,
                'branch' => $holiday->branch,
                'branch_id' => $holiday->branch_id,
            ]
        ]);
    }

    public function update(Request $request, Calendar $holiday)
    {
        // Verificar permiso manualmente
        $user = auth()->user();
        if (!$user->canDo('Modificar Feriados')) {
            abort(403, 'No tienes permiso para modificar feriados');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'branch_id' => 'required|exists:branches,id',
        ]);

        $holiday->update([
            'nombre' => $request->nombre,
            'fecha' => $request->fecha,
            'branch_id' => $request->branch_id,
        ]);

        $holiday->load('branch');

        return back()->with([
            'flash' => 'Feriado actualizado correctamente',
            'holiday' => [
                'id' => $holiday->id,
                'nombre' => $holiday->nombre,
                'fecha' => $holiday->fecha,
                'branch' => $holiday->branch,
                'branch_id' => $holiday->branch_id,
            ]
        ]);
    }

    public function destroy(Calendar $holiday)
    {
        // Verificar permiso manualmente
        $user = auth()->user();
        if (!$user->canDo('Eliminar Feriados')) {
            abort(403, 'No tienes permiso para eliminar feriados');
        }

        $holiday->delete();
        return back()->with('flash', 'Feriado eliminado correctamente');
    }

    public function getLocations()
    {
        $branches = Branch::select('departamento', 'provincia', 'localidad', 'id', 'nombre')->get();
        
        $locations = [];
        
        foreach ($branches as $branch) {
            $dept = $branch->departamento;
            $prov = $branch->provincia;
            $loc = $branch->localidad;
            
            if (!isset($locations[$dept])) {
                $locations[$dept] = [];
            }
            if (!isset($locations[$dept][$prov])) {
                $locations[$dept][$prov] = [];
            }
            $locations[$dept][$prov][] = [
                'localidad' => $loc,
                'id' => $branch->id,
                'nombre' => $branch->nombre,
            ];
        }

        return response()->json($locations);
    }
}