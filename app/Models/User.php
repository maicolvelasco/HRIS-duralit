<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'apellido',
        'codigo',
        'password',
        'foto',
        'is_active',
        'branch_id',
        'group_id',
        'section_id',
        'rol_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function rol(){
        return $this->belongsTo(Rol::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'users_permissions', 'user_id', 'permission_id')
                    ->withPivot('granted');
    }

    /**
     *  Devuelve true si el usuario puede ejecutar el permiso.
     *  1) Si existe un registro en users_permissions → respeta el campo granted.
     *  2) Si no existe → hereda del rol.
     */
    public function canDo(string $permissionNombre): bool
    {
        // 1. Verificar permiso directo (users_permissions)
        $directo = $this->permissions()
                        ->where('permissions.nombre', $permissionNombre)
                        ->first();

        if ($directo) {
            return (bool) $directo->pivot->granted;
        }

        // 2. Heredar del rol (solo si tiene rol)
        if ($this->rol_id && $this->rol) {
            return $this->rol->permissions()
                ->where('permissions.nombre', $permissionNombre)
                ->exists();
        }

        // 3. Sin rol = sin permisos heredados
        return false;
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'user_shift', 'user_id', 'shift_id');
    }

    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }

    public function groupManager()
    {
        return $this->hasOne(GroupManager::class, 'user_id');
    }

    /**
     * Relación: si este usuario es el encargado de RRHH.
     */
    public function hrManager()
    {
        return $this->hasOne(HrManager::class, 'user_id');
    }

    public function permissionRequests()
    {
        return $this->hasMany(PermissionRequest::class);
    }
}