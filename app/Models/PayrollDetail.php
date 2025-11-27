<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'payroll_id', 'concepto', 'cantidad_minutos_dias', 'monto_unitario', 'total', 'fuente'
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }
}