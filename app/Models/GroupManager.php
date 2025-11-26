<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupManager extends Model
{
    use HasFactory;

    protected $primaryKey = 'group_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'group_id',
        'user_id',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}