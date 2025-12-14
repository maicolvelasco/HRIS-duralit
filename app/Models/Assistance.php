<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assistance extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'fecha_entrada',
        'hora_entrada',
        'fecha_salida',
        'hora_salida',
        'entrada',
        'salida',
    ];

    protected $casts = [
        'entrada' => 'boolean',
        'salida'  => 'boolean',
        'fecha_entrada' => 'date',
        'hora_entrada' => 'datetime:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function affirmation(): HasOne
    {
        return $this->hasOne(Affirmation::class);
    }
}