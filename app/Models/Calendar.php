<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Calendar extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'nombre',
        'fecha',
        'branch_id',
    ];

    // Relación: Un calendario pertenece a una sucursal
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}