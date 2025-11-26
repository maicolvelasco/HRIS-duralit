<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionRequest extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'permission_requests';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'authorization_id',
        'titulation_id',
        'motivo',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'hora_inicio',
        'hora_fin',
        'cantidad_dias',
        'cantidad_horas',
        'estado',
        'observaciones',
        'report_id',
    ];

    protected $casts = [
        'cantidad_dias' => 'decimal:2',
        'cantidad_horas' => 'decimal:2',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function authorization(): BelongsTo
    {
        return $this->belongsTo(Authorization::class);
    }

    public function titulation(): BelongsTo
    {
        return $this->belongsTo(Titulation::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(User::class, 'report_id');
    }
}