<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClearanceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'semester',
        'school_year',
        'status',
        'submitted_at',
        'cleared_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'cleared_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ClearanceApproval::class);
    }
}