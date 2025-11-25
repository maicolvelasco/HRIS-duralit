<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'nombre',
        'departamento',
        'provincia',
        'localidad',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'branch_shift', 'branch_id', 'shift_id');
    }

    public function calendars(): HasMany
    {
        return $this->hasMany(Calendar::class);
    }
}