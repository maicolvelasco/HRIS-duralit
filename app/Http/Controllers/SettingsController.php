<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Branch;
use App\Models\Group;
use App\Models\Section;
use App\Models\Rol;
use App\Models\Permission;
use App\Models\Shift;
use App\Models\ShiftSchedule;
use App\Models\Calendar;
use App\Models\Authorization;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class SettingsController extends Controller
{
public function index()
{
    $user = auth()->user();
    
    $canViewUsers = $user->canDo('Ver Usuarios');
    $canCreateUsers = $user->canDo('Crear Usuarios');
    $canEditUsers = $user->canDo('Modificar Usuarios');
    $canViewRoles = $user->canDo('Ver Roles');
    $canCreateRoles = $user->canDo('Crear Roles');
    $canEditRoles = $user->canDo('Modificar Roles');
    $canViewPermisos = $user->canDo('Ver Permisos');
    $canCreatePermisos = $user->canDo('Crear Permisos');
    $canEditPermisos = $user->canDo('Modificar Permisos');
    $canViewPersonal   = $user->canDo('Ver Permisos Personales');
    $canEnablePersonal = $user->canDo('Activar Permisos Personales');
    $canDisablePersonal= $user->canDo('Desactivar Permisos Personales');
    $canViewSucursales = $user->canDo('Ver Sucursales');
    $canCreateSucursales = $user->canDo('Crear Sucursales');
    $canEditSucursales = $user->canDo('Modificar Sucursales');
    $canViewGrupos = $user->canDo('Ver Grupos');
    $canCreateGrupos = $user->canDo('Crear Grupos');
    $canEditGrupos = $user->canDo('Modificar Grupos');
    $canViewSecciones = $user->canDo('Ver Secciones');
    $canCreateSecciones = $user->canDo('Crear Secciones');
    $canEditSecciones = $user->canDo('Modificar Secciones');
    $canViewTurnos = $user->canDo('Ver Turnos');
    $canCreateTurnos = $user->canDo('Crear Turnos');
    $canEditTurnos = $user->canDo('Modificar Turnos');
    $canViewFeriados = $user->canDo('Ver Feriados');
    $canCreateFeriados = $user->canDo('Crear Feriados');
    $canEditFeriados = $user->canDo('Modificar Feriados');
    $canDeleteFeriados = $user->canDo('Eliminar Feriados');
    $canViewAuths   = $user->canDo('Ver Permisos de Trabajo');
    $canCreateAuths = $user->canDo('Crear Permisos de Trabajo');
    $canEditAuths   = $user->canDo('Modificar Permisos de Trabajo');
    $canViewReportes   = $user->canDo('Ver Reportes');
    $candownloadExcel = $user->canDo('Descargar Reportes Excel');
    $candownloadPDF = $user->canDo('Descargar Reportes PDF');

    // Cargar usuarios con permisos DIRECTOS (solo granted = true)
    $usersWithPermissions = [];
    if ($canViewUsers) {
        $usersWithPermissions = User::with(['permissions' => function($query) {
            $query->select('permissions.id', 'permissions.nombre', 'permissions.descripcion')
                  ->wherePivot('granted', true); // Solo permisos activos en la relación
        }])
        ->select('id', 'nombre', 'apellido', 'codigo', 'is_active', 'branch_id', 'group_id', 'section_id', 'rol_id', 'foto')
        ->orderBy('nombre')
        ->get();

        // ✅ NUEVO: Para cada usuario, cargar IDs de permisos NEGADOS (granted = false)
        foreach ($usersWithPermissions as $u) {
            $u->denied_permission_ids = \DB::table('users_permissions')
                ->where('user_id', $u->id)
                ->where('granted', false)
                ->pluck('permission_id')
                ->toArray();
        }
    }

    // ✅ IMPORTANTE: Cargar roles CON sus permisos para verificar herencia
    $roles = Rol::with(['permissions' => function($q) {
        $q->select('permissions.id', 'permissions.nombre');
    }])
    ->select('id', 'nombre', 'descripcion')
    ->orderBy('nombre')
    ->get();

    // ✅ Cargar turnos con TODAS las relaciones necesarias
    $shifts = [];
    if ($canViewTurnos) {
        $shifts = Shift::with([
                'schedules',
                'users',
                'branches',
                'groups',
                'sections',
                'roles'
            ])
            ->orderBy('nombre')
            ->get();
    }

    $locations = $this->getNestedLocations();

    return Inertia::render('Settings', [
        'users' => $canViewUsers ? User::select('id', 'nombre', 'apellido', 'codigo', 'is_active', 'branch_id', 'group_id', 'section_id', 'rol_id', 'foto')
                       ->orderBy('nombre')
                       ->get() : [],
        
        'usersWithPermissions' => $usersWithPermissions,
        'allPermissions' => Permission::select('id', 'nombre', 'descripcion')
                            ->orderBy('nombre')
                            ->get(),
        
        'branches' => Branch::select('id', 'nombre', 'departamento', 'provincia', 'localidad')
                       ->orderBy('nombre')
                       ->get(),
        'groups'   => Group::select('id', 'nombre', 'descripcion')
                       ->orderBy('nombre')
                       ->get(),
        'sections' => Section::select('id', 'nombre', 'descripcion')
                       ->orderBy('nombre')
                       ->get(),
            
        $authorizations = $canViewAuths
        ? Authorization::select('id', 'nombre', 'descripcion')
            ->orderBy('nombre')
            ->get()
        : collect(),

        'shifts'   => $shifts,

        'locations' => $locations,

        'authorizations' => $authorizations,
        
        // ✅ Pasar roles con permisos a la vista
        'roles' => $roles,
        
        'permissionsList' => Permission::with('roles')
              ->select('id', 'nombre', 'descripcion')
              ->orderBy('nombre')
              ->get(),
        
        'permissions' => [
            'Ver Usuarios' => $canViewUsers,
            'Crear Usuarios' => $canCreateUsers,
            'Modificar Usuarios' => $canEditUsers,
            'Ver Roles' => $canViewRoles,
            'Crear Roles' => $canCreateRoles,
            'Modificar Roles' => $canEditRoles,
            'Ver Permisos' => $canViewPermisos,
            'Crear Permisos' => $canCreatePermisos,
            'Modificar Permisos' => $canEditPermisos,
            'Ver Permisos Personales' => $canViewPersonal,
            'Activar Permisos Personales' => $canEnablePersonal,
            'Desactivar Permisos Personales' =>$canDisablePersonal,
            'Ver Sucursales' => $canViewSucursales,
            'Crear Sucursales' => $canCreateSucursales,
            'Modificar Sucursales' => $canEditSucursales,
            'Ver Grupos' => $canViewGrupos,
            'Crear Grupos' => $canCreateGrupos,
            'Modificar Grupos' => $canEditGrupos,
            'Ver Secciones' => $canViewSecciones,
            'Crear Secciones' => $canCreateSecciones,
            'Modificar Secciones' => $canEditSecciones,
            'Ver Turnos' => $canViewTurnos,
            'Crear Turnos' => $canCreateTurnos,
            'Modificar Turnos' => $canEditTurnos,
            'Ver Feriados' => $canViewFeriados,
            'Crear Feriados' => $canCreateFeriados,
            'Modificar Feriados' => $canEditFeriados,
            'Eliminar Feriados' => $canDeleteFeriados,
            'Ver Permisos de Trabajo' => $canViewAuths,
            'Crear Permisos de Trabajo' => $canCreateAuths,
            'Modificar Permisos de Trabajo' => $canEditAuths,
            'Ver Reportes' => $canViewReportes,
            'Descargar Reportes Excel' => $candownloadExcel,
            'Descargar Reportes PDF' => $candownloadPDF,
        ],
        
        'defaultSection' => $canViewUsers ? 'users' : 'sections',
    ]);
}

    /* ========== PERSONAS ========== */

    public function storePerson(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'apellido' => 'required|string|max:120',
            'codigo'   => 'required|string|max:20|unique:users,codigo',
            'password' => 'required|string|min:6',
            'foto'       => 'nullable|image|max:5120',
            'is_active'=> 'boolean',
            'branch_id'  => 'nullable|exists:branches,id',
            'group_id'  => 'nullable|exists:groups,id',
            'section_id' => 'nullable|exists:sections,id',
            'rol_id' => 'nullable|exists:roles,id',
        ]);

        $fotoPath = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $nombreOriginal = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->extension();
                $fotoPath = $file->storeAs('fotos', $nombreOriginal, 'public');
            }

        $user = User::create([
            'nombre'    => $request->nombre,
            'apellido'  => $request->apellido,
            'codigo'    => $request->codigo,
            'password'  => bcrypt($request->password),
            'foto' => 'fotos/' . $nombreOriginal,
            'is_active' => $request->boolean('is_active', true),
            'branch_id' => $request->branch_id !== '' ? $request->branch_id : null,
            'group_id'  => $request->group_id !== '' ? $request->group_id : null,
            'section_id' => $request->section_id !== '' ? $request->section_id : null,
            'rol_id' => $request->rol_id !== '' ? $request->rol_id : null,
        ]);

        // **NUEVO**: Asignar permisos del rol al nuevo usuario
        if ($request->filled('rol_id')) {
            $this->assignRolePermissionsToUser($user->id, $request->rol_id);
        }

        return redirect()->route('settings.index')->with('flash', 'Persona creada correctamente');
    }

    public function getPerson(User $user)
    {
        return response()->json($user->only('id', 'nombre', 'codigo', 'is_active', 'branch_id', 'group_id', 'section_id', 'rol_id'));
    }

    public function updatePerson(Request $request, User $user)
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'apellido' => 'required|string|max:120',
            'codigo'   => ['required','string','max:20', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'foto'       => 'nullable|image|max:5120',
            'is_active'=> 'boolean',
            'branch_id'  => 'nullable|exists:branches,id',
            'group_id'  => 'nullable|exists:groups,id',
            'section_id' => 'nullable|exists:sections,id',
            'rol_id' => 'nullable|exists:roles,id',
        ]);

        // Guardar rol anterior para comparar
        $oldRoleId = $user->rol_id;

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::delete('public/' . $user->foto);
            }
            $file = $request->file('foto');
            $nombreOriginal = \Illuminate\Support\Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->extension();
            $fotoPath = $file->storeAs('fotos', $nombreOriginal, 'public');
            $user->foto = 'fotos/' . $nombreOriginal;
            $user->save();
        }

        $user->update([
            'nombre'    => $request->nombre,
            'apellido'  => $request->apellido,
            'codigo'    => $request->codigo,
            'is_active' => $request->boolean('is_active', true),
            'branch_id' => $request->branch_id !== '' ? $request->branch_id : null,
            'group_id'  => $request->group_id !== '' ? $request->group_id : null,
            'section_id' => $request->section_id !== '' ? $request->section_id : null,
            'rol_id' => $request->rol_id !== '' ? $request->rol_id : null,
            ...( $request->filled('password') ? ['password' => bcrypt($request->password)] : [] ),
        ]);

        // **NUEVO**: Si el rol cambió, sincronizar permisos
        $newRoleId = $request->filled('rol_id') ? $request->rol_id : null;
        
        if ($oldRoleId != $newRoleId) {
            // Eliminar permisos anteriores del usuario
            $this->removeAllUserPermissions($user->id);
            
            // Asignar permisos del nuevo rol
            if ($newRoleId) {
                $this->assignRolePermissionsToUser($user->id, $newRoleId);
            }
        }

        return redirect()->route('settings.index')->with('flash', 'Usuario actualizado');
    }

    /* ========== SUCURSALES ========== */
    public function storeBranch(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'departamento'=> 'required|string|max:120',
            'provincia'   => 'required|string|max:120',
            'localidad'   => 'required|string|max:120',
        ]);

        Branch::create($request->only('nombre','departamento','provincia','localidad'));

        return redirect()->route('settings.index')->with('flash', 'Sucursal creada');
    }

    public function getBranch(Branch $branch)
    {
        return response()->json($branch->only('id','nombre','departamento','provincia','localidad'));
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'departamento'=> 'required|string|max:120',
            'provincia'   => 'required|string|max:120',
            'localidad'   => 'required|string|max:120',
        ]);

        $branch->update($request->only('nombre','departamento','provincia','localidad'));

        return redirect()->route('settings.index')->with('flash', 'Sucursal actualizada');
    }

    /* ---------- GRUPOS ---------- */
    public function storeGroup(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:255',
        ]);
        Group::create($request->only('nombre','descripcion'));
        return redirect()->route('settings.index')->with('flash','Grupo creado');
    }

    public function getGroup(Group $group)
    {
        return response()->json($group->only('id','nombre','descripcion'));
    }

    public function updateGroup(Request $request, Group $group)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $group->update($request->only('nombre','descripcion'));
        return redirect()->route('settings.index')->with('flash','Grupo actualizado');
    }

    /* ---------- SECCIONES ---------- */
    public function storeSection(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:255',
        ]);
        Section::create($request->only('nombre','descripcion'));
        return redirect()->route('settings.index')->with('flash','Sección creada');
    }

    public function getSection(Section $section)
    {
        return response()->json($section->only('id','nombre','descripcion'));
    }

    public function updateSection(Request $request, Section $section)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $section->update($request->only('nombre','descripcion'));
        return redirect()->route('settings.index')->with('flash','Sección actualizada');
    }

    /* ---------- ROLES ---------- */
    public function storeRol(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:255',
        ]);
        Rol::create($request->only('nombre','descripcion'));
        return redirect()->route('settings.index')->with('flash','Rol creado');
    }

    public function getRol(Rol $rol)
    {
        return response()->json($rol->only('id','nombre','descripcion'));
    }

    public function updateRol(Request $request, Rol $rol)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120',
            'descripcion' => 'nullable|string|max:255',
        ]);
        $rol->update($request->only('nombre','descripcion'));
        return redirect()->route('settings.index')->with('flash','Rol actualizado');
    }

    /* ========== PERMISOS ========== */

    public function storePermission(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120|unique:permissions,nombre',
            'descripcion' => 'nullable|string|max:255',
            'roles'       => 'required|array|min:1',
            'roles.*'     => 'exists:roles,id',
        ]);

        // Crear el permiso (el UUID se genera automáticamente)
        $permission = Permission::create($request->only('nombre', 'descripcion'));

        // Asignar roles al permiso (roles_permissions)
        $permission->roles()->sync($request->roles);

        // Sincronizar con users_permissions
        $this->syncUsersPermissions($permission->id, $request->roles, true);

        return redirect()->route('settings.index')->with('flash','Permiso creado correctamente');
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $request->validate([
            'nombre'      => ['required','string','max:120', Rule::unique('permissions')->ignore($permission->id)],
            'descripcion' => 'nullable|string|max:255',
            'roles'       => 'required|array|min:1',
            'roles.*'     => 'exists:roles,id',
        ]);

        // Actualizar el permiso
        $permission->update($request->only('nombre', 'descripcion'));

        // Obtener roles antes de la actualización
        $oldRoles = $permission->roles()->pluck('roles.id')->toArray();
        
        // Asignar nuevos roles al permiso
        $permission->roles()->sync($request->roles);

        // Eliminar el permiso de usuarios que ya no tienen el rol
        $removedRoles = array_diff($oldRoles, $request->roles);
        if (!empty($removedRoles)) {
            $this->removeUsersPermissionsByRoles($permission->id, $removedRoles);
        }

        // Añadir el permiso a usuarios con nuevos roles
        $newRoles = array_diff($request->roles, $oldRoles);
        if (!empty($newRoles)) {
            $this->syncUsersPermissions($permission->id, $newRoles, true);
        }

        return redirect()->route('settings.index')->with('flash','Permiso actualizado correctamente');
    }

    /**
     * Sincroniza el permiso para todos los usuarios que tienen los roles especificados
     * SIN TIMESTAMPS (igual que roles_permissions)
     */
    private function syncUsersPermissions($permissionId, $roleIds, $granted = true)
    {
        // Obtener IDs de usuarios con estos roles
        $userIds = \App\Models\User::whereIn('rol_id', $roleIds)->pluck('id');
        
        // Preparar datos para insertar/actualizar (SIN timestamps)
        $data = [];
        foreach ($userIds as $userId) {
            $data[] = [
                'user_id'       => $userId,
                'permission_id' => $permissionId,
                'granted'       => $granted,
            ];
        }

        // Insertar o actualizar en users_permissions (solo columnas necesarias)
        if (!empty($data)) {
            \DB::table('users_permissions')->upsert(
                $data,
                ['user_id', 'permission_id'], // Clave única
                ['granted']                    // Solo actualizar el campo granted
            );
        }
    }

    /**
     * Elimina un permiso de usuarios que ya no tienen los roles especificados
     */
    private function removeUsersPermissionsByRoles($permissionId, $roleIds)
    {
        // Obtener IDs de usuarios con estos roles
        $userIds = \App\Models\User::whereIn('rol_id', $roleIds)->pluck('id');
        
        // Eliminar las entradas en users_permissions
        \DB::table('users_permissions')
            ->where('permission_id', $permissionId)
            ->whereIn('user_id', $userIds)
            ->delete();
    }

    /**
     * **NUEVO**: Asigna todos los permisos de un rol a un usuario específico
     */
    private function assignRolePermissionsToUser($userId, $roleId)
    {
        if (!$roleId) {
            return;
        }

        // Obtener todos los permisos del rol
        $permissionIds = \DB::table('roles_permissions')
            ->where('rol_id', $roleId)
            ->pluck('permission_id');

        if ($permissionIds->isEmpty()) {
            return;
        }

        // Preparar datos para upsert (sin timestamps)
        $data = [];
        foreach ($permissionIds as $permissionId) {
            $data[] = [
                'user_id'       => $userId,
                'permission_id' => $permissionId,
                'granted'       => true,
            ];
        }

        // Insertar o actualizar en users_permissions
        if (!empty($data)) {
            \DB::table('users_permissions')->upsert(
                $data,
                ['user_id', 'permission_id'], // Clave única
                ['granted']                    // Solo actualizar el campo granted
            );
        }
    }

    /**
     * **NUEVO**: Elimina todos los permisos de un usuario específico
     */
    private function removeAllUserPermissions($userId)
    {
        \DB::table('users_permissions')
            ->where('user_id', $userId)
            ->delete();
    }

    /**
     * Toggle permiso individual para un usuario - SIN PANTALLAZO
     */
    public function toggleUserPermission(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'permission_id' => 'required|exists:permissions,id',
                'granted' => 'required|boolean'
            ]);

            $permissionId = $validated['permission_id'];
            $granted = $validated['granted'];

            // ✅ UPSERT: Inserta o actualiza SIN TIMESTAMPS
            // Si granted=false, inserta/actualiza el registro con granted=false
            \DB::table('users_permissions')->upsert(
                [
                    'user_id'       => $user->id,
                    'permission_id' => $permissionId,
                    'granted'       => $granted, // <-- Puede ser true o false
                ],
                ['user_id', 'permission_id'], // Unique constraint
                ['granted']                    // Columna a actualizar
            );

            // ✅ LIMPIA CACHÉ de permisos del usuario
            cache()->forget("user_permissions_{$user->id}");

            return response('', 204)->header('X-Inertia', 'true');

        } catch (\Exception $e) {
            \Log::error('❌ ERROR TOGGLE PERMISO:', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'permission_id' => $request->permission_id
            ]);
            
            return response()->json(['errors' => ['permission' => $e->getMessage()]], 500);
        }
    }

    /* ---------- TURNOS ---------- */

    public function storeShift(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'jornada'  => 'required|integer|min:1|max:24',
            'semanal'  => 'required|integer|min:1|max:168',
            'desde'    => 'required|date',
            'hasta'    => 'required|date|after_or_equal:desde',
            'user_id'  => 'nullable|exists:users,id',
            'branch_id'=> 'nullable|exists:branches,id',
            'group_id' => 'nullable|exists:groups,id',
            'section_id'=>'nullable|exists:sections,id',
            'rol_id'   => 'nullable|exists:roles,id',
            'schedules' => 'required|array|min:1',
            'schedules.*.hora_inicio' => 'required|date_format:H:i',
            'schedules.*.hora_fin'    => 'required|date_format:H:i|after:schedules.*.hora_inicio',
            'schedules.*.dias'        => 'required|array|min:1',
            'schedules.*.dias.*'      => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'schedules.*.incluye_feriados' => 'boolean',
        ]);

        // Al menos uno de los 5
        if (!($request->user_id || $request->branch_id || $request->group_id || $request->section_id || $request->rol_id)) {
            return back()->withErrors(['general' => 'Debes seleccionar al menos un usuario, sucursal, grupo, sección o rol.']);
        }

        $shift = Shift::create($request->only('nombre','jornada','semanal','desde','hasta'));

        foreach ($request->schedules as $s) {
            $shift->schedules()->create([
                'hora_inicio' => $s['hora_inicio'],
                'hora_fin'    => $s['hora_fin'],
                'dias'        => $s['dias'],
                'incluye_feriados' => $s['incluye_feriados'] ?? false,
            ]);
        }

        // Guardar pivotes (siempre limpiar relaciones no seleccionadas)
        $shift->users()->sync($request->user_id ? [$request->user_id] : []);
        $shift->branches()->sync($request->branch_id ? [$request->branch_id] : []);
        $shift->groups()->sync($request->group_id ? [$request->group_id] : []);
        $shift->sections()->sync($request->section_id ? [$request->section_id] : []);
        $shift->roles()->sync($request->rol_id ? [$request->rol_id] : []);

        return redirect()->route('settings.index')->with('flash','Turno creado');
    }

    public function getShift(Shift $shift)
    {
        // Cargar explícitamente las relaciones que necesitas
        $shift->load([
            'schedules',
            'users',
            'branches',
            'groups',
            'sections',
            'roles'
        ]);

        return response()->json([
            'id' => $shift->id,
            'nombre' => $shift->nombre,
            'jornada' => $shift->jornada,
            'semanal' => $shift->semanal,
            'desde' => $shift->desde,
            'hasta' => $shift->hasta,
            'schedules' => $shift->schedules,
            'users'   => $shift->users,
            'branches'=> $shift->branches,
            'groups'  => $shift->groups,
            'sections'=> $shift->sections,
            'roles'   => $shift->roles,
        ]);
    }

    public function updateShift(Request $request, Shift $shift)
    {
        // mismo validate que storeShift
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'jornada'  => 'required|integer|min:1|max:24',
            'semanal'  => 'required|integer|min:1|max:168',
            'desde'    => 'required|date',
            'hasta'    => 'required|date|after_or_equal:desde',
            'user_id'  => 'nullable|exists:users,id',
            'branch_id'=>'nullable|exists:branches,id',
            'group_id' => 'nullable|exists:groups,id',
            'section_id'=>'nullable|exists:sections,id',
            'rol_id'   => 'nullable|exists:roles,id',
            'schedules' => 'required|array|min:1',
            'schedules.*.hora_inicio' => 'required|date_format:H:i',
            'schedules.*.hora_fin'    => 'required|date_format:H:i|after:schedules.*.hora_inicio',
            'schedules.*.dias'        => 'required|array|min:1',
            'schedules.*.dias.*'      => 'in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'schedules.*.incluye_feriados' => 'boolean',
        ]);

        if (!($request->user_id || $request->branch_id || $request->group_id || $request->section_id || $request->rol_id)) {
            return back()->withErrors(['general' => 'Debes seleccionar al menos un usuario, sucursal, grupo, sección o rol.']);
        }

        $shift->update($request->only('nombre','jornada','semanal','desde','hasta'));
        $shift->schedules()->delete();

        foreach ($request->schedules as $s) {
            $shift->schedules()->create([
                'hora_inicio' => $s['hora_inicio'],
                'hora_fin'    => $s['hora_fin'],
                'dias'        => $s['dias'],
                'incluye_feriados' => $s['incluye_feriados'] ?? false,
            ]);
        }

        // Sincronizar pivotes (siempre limpiar relaciones no seleccionadas)
        $shift->users()->sync($request->user_id ? [$request->user_id] : []);
        $shift->branches()->sync($request->branch_id ? [$request->branch_id] : []);
        $shift->groups()->sync($request->group_id ? [$request->group_id] : []);
        $shift->sections()->sync($request->section_id ? [$request->section_id] : []);
        $shift->roles()->sync($request->rol_id ? [$request->rol_id] : []);

        return redirect()->route('settings.index')->with('flash','Turno actualizado');
    }

    private function getNestedLocations()
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
        
        return $locations;
    }

    /* ---------- PERMISOS DE TRABAJO (authorizations) ---------- */
    public function storeAuthorization(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string|max:120|unique:authorizations,nombre',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Authorization::create($request->only('nombre','descripcion'));

        return redirect()->route('settings.index')
                        ->with('flash','Permiso de trabajo creado');
    }

    public function getAuthorization(Authorization $authorization)
    {
        return response()->json($authorization->only('id','nombre','descripcion'));
    }

    public function updateAuthorization(Request $request, Authorization $authorization)
    {
        $request->validate([
            'nombre'      => ['required','string','max:120', Rule::unique('authorizations')->ignore($authorization->id)],
            'descripcion' => 'nullable|string|max:255',
        ]);

        $authorization->update($request->only('nombre','descripcion'));

        return redirect()->route('settings.index')
                        ->with('flash','Permiso de trabajo actualizado');
    }

   /* ========== REPORTES ========== */
    public function generateReport(Request $request)
    {
        // ✅ AUMENTAR LÍMITES
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'section' => 'required|in:users,roles,permissions,branches,groups,sections,authorizations,shifts,holidays',
            'format' => 'required|in:excel,pdf',
            'date_range' => 'required|in:all,between',
            'start_date' => 'nullable|required_if:date_range,between|date',
            'end_date' => 'nullable|required_if:date_range,between|date|after_or_equal:start_date',
        ]);

        $section = $request->input('section');
        $format = $request->input('format');
        $filters = [
            'date_range' => $request->input('date_range'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        // ✅ NUEVO: Verificar permisos específicos por sección
        $user = auth()->user();
        switch ($section) {
            case 'users':
                if (!$user->canDo('Ver Usuarios')) {
                    abort(403, 'No tienes permiso para ver usuarios.');
                }
                break;
            case 'roles':
                if (!$user->canDo('Ver Roles')) {
                    abort(403, 'No tienes permiso para ver roles.');
                }
                break;
            case 'permissions':
                if (!$user->canDo('Ver Permisos')) {
                    abort(403, 'No tienes permiso para ver permisos.');
                }
                break;
            case 'branches':
                if (!$user->canDo('Ver Sucursales')) {
                    abort(403, 'No tienes permiso para ver sucursales.');
                }
                break;
            case 'groups':
                if (!$user->canDo('Ver Grupos')) {
                    abort(403, 'No tienes permiso para ver grupos.');
                }
                break;
            case 'sections':
                if (!$user->canDo('Ver Secciones')) {
                    abort(403, 'No tienes permiso para ver secciones.');
                }
                break;
            case 'authorizations':
                if (!$user->canDo('Ver Permisos de Trabajo')) {
                    abort(403, 'No tienes permiso para ver permisos de trabajo.');
                }
                break;
            case 'shifts':
                if (!$user->canDo('Ver Turnos')) {
                    abort(403, 'No tienes permiso para ver turnos.');
                }
                break;
            case 'holidays':
                if (!$user->canDo('Ver Feriados')) {
                    abort(403, 'No tienes permiso para ver feriados.');
                }
                break;
        }

        // ✅ También verificar el permiso general de reportes (ya existente)
        if (!$user->canDo('Ver Reportes')) {
            abort(403, 'No tienes permiso para generar reportes.');
        }

        $filename = 'reporte_' . $section . '_' . now()->format('Y-m-d_His');
        $data = $this->getOptimizedReportData($section, $filters);

        if ($format === 'excel') {
            return $this->generateOptimizedExcel($section, $data, $filename, $filters);
        } else {
            return $this->generateOptimizedPDF($section, $data, $filename, $filters);
        }
    }

    // ✅ NUEVO MÉTODO OPTIMIZADO
    private function getOptimizedReportData($section, $filters)
    {
        $dateRange = $filters['date_range'];
        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;

        switch ($section) {
            case 'users':
                $query = User::query();
                if ($dateRange === 'between' && $startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                }
                return $query->with(['branch:id,nombre', 'group:id,nombre', 'section:id,nombre', 'rol:id,nombre'])
                           ->select('id', 'nombre', 'apellido', 'codigo', 'is_active', 'branch_id', 'group_id', 'section_id', 'rol_id', 'foto', 'created_at')
                           ->orderBy('nombre')
                           ->get();
                
            case 'shifts':
                $query = Shift::with([
                    'schedules',
                    'users:id,nombre,apellido,codigo',
                    'branches:id,nombre',
                    'groups:id,nombre',
                    'sections:id,nombre',
                    'roles:id,nombre'
                ]);
                
                if ($dateRange === 'between' && $startDate && $endDate) {
                    $query->where(function($q) use ($startDate, $endDate) {
                        $q->whereBetween('desde', [$startDate, $endDate])
                          ->orWhereBetween('hasta', [$startDate, $endDate]);
                    });
                }
                return $query->select('id', 'nombre', 'jornada', 'semanal', 'desde', 'hasta')->get();
                
            default:
                return $this->getReportData($section, $filters); // Usar método original para otros
        }
    }

    // ✅ MÉTODO EXCEL CON STREAMING
    private function generateOptimizedExcel($section, $data, $filename, $filters = [])
    {
        $exportClass = 'App\\Exports\\Settings\\' . ucfirst($section) . 'Export';
        
        if (!class_exists($exportClass)) {
            return response()->json(['error' => 'Exportador no implementado'], 500);
        }

        return Excel::download(
            new $exportClass($data, $filters), 
            $filename . '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    // ✅ MÉTODO PDF CON STREAMING
    private function generateOptimizedPDF($section, $data, $filename, $filters = [])
    {
        $view = 'pdf.reports.' . $section;
        
        if (!view()->exists($view)) {
            return response()->json(['error' => 'Vista PDF no encontrada'], 500);
        }

        $pdf = Pdf::loadView($view, [
            'data' => $data,
            'section' => $section,
            'filters' => $filters,
            'generated_at' => now()
        ])->setPaper('A4', 'landscape');

        // ✅ STREAM EN VEZ DE DOWNLOAD DIRECTO
        return response()->streamDownload(
            function () use ($pdf) {
                echo $pdf->output();
            },
            $filename . '.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}