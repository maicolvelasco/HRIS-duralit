<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // ← Importar

class Overtime extends Model
{
    use HasFactory, HasUuids;

    public const PENDIENTE = 'Pendiente';
    public const APROBADO  = 'Aprobado';
    public const USADO     = 'Usado';
    public const ESTADOS = [self::PENDIENTE, self::APROBADO, self::USADO];

    protected $table = 'overtimes';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'fecha',
        'desde',
        'hasta',
        'contador',
        'trabajo',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date',
        'desde' => 'datetime:H:i',
        'hasta' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // RELACIÓN NUEVA: Un overtime tiene muchas compensaciones
    public function compensations(): HasMany
    {
        return $this->hasMany(Compensation::class);
    }

    public function scopePendiente($query) { return $query->where('estado', self::PENDIENTE); }
    public function scopeAprobado($query)  { return $query->where('estado', self::APROBADO); }
    public function scopeUsado($query)     { return $query->where('estado', self::USADO); }
}