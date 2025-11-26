<?php

namespace App\Http\Controllers;

use App\Models\HrManager;
use App\Models\User;
use App\Models\GroupManager;
use App\Models\Group;
use App\Models\Branch;
use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HrManagerController extends Controller
{
    public function index()
    {
        $hrUserId = HrManager::with('user')->first()?->user_id;

        // ✅ Jefes actuales
        $managers = GroupManager::with(['user', 'group.users'])
            ->whereHas('user', fn($q) => $q->where('id', '!=', $hrUserId))
            ->get();

        $unassigned = User::whereNull('group_id')
            ->orWhereDoesntHave('groupManager')
            ->where('id', '!=', $hrUserId)
            ->select('id', 'nombre', 'apellido', 'codigo', 'group_id', 'branch_id', 'section_id')
            ->orderBy('nombre')
            ->get();

        // ✅ Todos los usuarios para búsquedas
        $allUsers = User::where('id', '!=', $hrUserId)
            ->select('id', 'nombre', 'apellido', 'codigo', 'group_id', 'branch_id', 'section_id')
            ->orderBy('nombre')
            ->get();

        $groups = Group::select('id', 'nombre')->get();
        $branches = Branch::select('id', 'nombre')->get();
        $sections = Section::select('id', 'nombre')->get();

        return Inertia::render('HrManager/Index', [
            'hrManager' => HrManager::with('user')->first(),
            'users' => $allUsers,
            'managers' => $managers,
            'unassigned' => $unassigned,
            'groups' => $groups,
            'branches' => $branches,
            'sections' => $sections,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id|unique:hr_managers,user_id',
        ]);

        HrManager::truncate();
        HrManager::create(['user_id' => $request->user_id]);

        return redirect()->back()->with('flash', 'Recursos Humanos asignado correctamente.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $hr = HrManager::first();
        if ($hr) {
            $hr->update(['user_id' => $request->user_id]);
        } else {
            HrManager::create(['user_id' => $request->user_id]);
        }

        return redirect()->back()->with('flash', 'Recursos Humanos actualizado.');
    }

    // ✅ NUEVO: Asignar jefe de grupo
    public function assignManager(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'group_id' => 'required|uuid|exists:groups,id',
        ]);

        // Solo un jefe por grupo
        GroupManager::updateOrCreate(
            ['group_id' => $request->group_id],
            ['user_id' => $request->user_id]
        );

        return redirect()->back()->with('flash', 'Jefe asignado correctamente.');
    }

    // ✅ NUEVO: Cambiar jefe (mover usuarios)
    public function changeManager(Request $request)
    {
        $request->validate([
            'old_user_id' => 'nullable|uuid|exists:users,id',
            'new_user_id' => 'required|uuid|exists:users,id',
        ]);

        $old = GroupManager::where('user_id', $request->old_user_id)->first();
        $new = GroupManager::where('user_id', $request->new_user_id)->first();

        if ($old && $new) {
            // Mover usuarios del grupo del viejo al nuevo
            User::where('group_id', $old->group_id)->update(['group_id' => $new->group_id]);
            $old->delete();
        }

        return redirect()->back()->with('flash', 'Jefe cambiado y usuarios trasladados.');
    }

    // ✅ NUEVO: Quitar usuario de grupo
    public function removeUserFromGroup(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        User::where('id', $request->user_id)->update(['group_id' => null]);

        return redirect()->back()->with('flash', 'Usuario removido del grupo.');
    }

    // ✅ NUEVO: Añadir usuarios por filtros
    public function addUsersByFilter(Request $request)
    {
        $request->validate([
            'branch_id' => 'nullable|uuid|exists:branches,id',
            'section_id' => 'nullable|uuid|exists:sections,id',
            'group_id' => 'nullable|uuid|exists:groups,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'uuid|exists:users,id',
        ]);

        $query = User::query();

        if ($request->branch_id) $query->where('branch_id', $request->branch_id);
        if ($request->section_id) $query->where('section_id', $request->section_id);
        if ($request->group_id) $query->where('group_id', $request->group_id);
        if ($request->user_ids) $query->whereIn('id', $request->user_ids);

        $query->update(['group_id' => $request->target_group_id]);

        return redirect()->back()->with('flash', 'Usuarios añadidos al grupo.');
    }
}