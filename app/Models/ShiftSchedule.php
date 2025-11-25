<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ShiftSchedule extends Model
{
    protected $table = 'shift_schedules';

    protected $fillable = [
        'shift_id',
        'hora_inicio',
        'hora_fin',
        'dias',          // json: ['lunes','martes',...]
        'incluye_feriados',
    ];

    protected $casts = [
        'dias' => 'array',
        'incluye_feriados' => 'boolean',
    ];

    // Configuración UUID
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

    /* -------- relaciones -------- */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}