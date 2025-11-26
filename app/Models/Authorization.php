<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Authorization extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_authorization', 'authorization_id', 'rol_id');
    }

    public function titulations()
    {
        return $this->belongsToMany(Titulation::class, 'authorization_titulation', 'authorization_id', 'titulation_id');
    }

    public function permissionRequests()
    {
        return $this->hasMany(PermissionRequest::class);
    }
}