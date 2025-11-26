<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OvertimeController extends Controller
{
    /* ---------- Listar ---------- */
    public function index()
    {
        $rows = Overtime::with('user:id,codigo,nombre,apellido')
                        ->where('user_id', auth()->id())
                        ->orderByDesc('fecha')
                        ->get();

        return Inertia::render('Overtime/Index', [
            'overtimes' => $rows,
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

        // Doble seguridad: forzar el usuario logueado
        $validated['user_id'] = auth()->id();
        $validated['estado']  = 'Pendiente';

        Overtime::create($validated);

        return redirect()->route('overtimes.index')
                         ->with('success', 'Sobretiempo registrado correctamente.');
    }
}