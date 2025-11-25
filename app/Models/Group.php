<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
   
    public function shifts()
    {
        return $this->belongsToMany(Shift::class, 'group_shift', 'group_id', 'shift_id');
    }
}