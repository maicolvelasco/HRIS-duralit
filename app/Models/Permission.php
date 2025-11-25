<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Permission extends Model
{
    use HasUuids;
    protected $fillable = ['id', 'nombre', 'descripcion'];

    public $incrementing = false;
    protected $keyType   = 'string';

    /* ---------- relaciones ---------- */
    public function roles(){
        return $this->belongsToMany(Rol::class, 'roles_permissions', 'permission_id', 'rol_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'users_permissions', 'permission_id', 'user_id')
                    ->withPivot('granted');
    }
}