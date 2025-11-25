<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\ShiftSchedule;
use Illuminate\Support\Str;

class Shift extends Model
{
    use HasUuids;

    protected $fillable = [
        'nombre',
        'jornada',
        'semanal',
        'desde',
        'hasta',
    ];

    // UUID configuration
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /* ---------- relaciones ---------- */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_shift', 'shift_id', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_shift', 'shift_id', 'rol_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_shift', 'shift_id', 'branch_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_shift', 'shift_id', 'group_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'section_shift', 'shift_id', 'section_id');
    }

    public function schedules()
    {
        return $this->hasMany(ShiftSchedule::class);
    }
}