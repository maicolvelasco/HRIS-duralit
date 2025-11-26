<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'cantidad_dias' => 'decimal:2',
        'cantidad_horas' => 'decimal:2',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function authorization()
    {
        return $this->belongsTo(Authorization::class);
    }

    public function titulation()
    {
        return $this->belongsTo(Titulation::class);
    }
}