<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Rol extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'roles'; // ⭐ añade esta línea

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions(){
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'rol_shift', 'rol_id', 'shift_id');
    }
}