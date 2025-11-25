<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Titulation extends Model
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
        return $this->belongsToMany(Rol::class, 'rol_titulation', 'titulation_id', 'rol_id');
    }

    public function authorizations()
    {
        return $this->belongsToMany(Authorization::class, 'authorization_titulation', 'titulation_id', 'authorization_id');
    }
}