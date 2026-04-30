<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClearanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'semester',
        'school_year',
        'status',
        'submitted_at',
        'cleared_at',
        'receipt_number',
        'verification_code',
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