<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable([
    'student_id',
    'first_name',
    'last_name',
    'name',
    'year_level',
    'email',
    'course_id',
    'office_id',
    'role',
    'is_active',
    'deactivated_at',
    'password',
])]
#[Hidden([
    'password',
    'two_factor_secret',
    'two_factor_recovery_codes',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function clearanceRequests(): HasMany
    {
        return $this->hasMany(ClearanceRequest::class);
    }

    public function clearanceApprovals(): HasMany
    {
        return $this->hasMany(ClearanceApproval::class, 'approved_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function getFormattedNameAttribute(): string
    {
        if ($this->last_name && $this->first_name) {
            return "{$this->last_name}, {$this->first_name}";
        }

        if ($this->last_name) {
            return $this->last_name;
        }

        if ($this->name) {
            if (str_contains($this->name, ',')) {
                return $this->name;
            }

            $parts = preg_split('/\s+/', trim($this->name));

            if (count($parts) > 1) {
                $lastName = array_pop($parts);
                $firstName = implode(' ', $parts);

                return "{$lastName}, {$firstName}";
            }

            return $this->name;
        }

        return 'N/A';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }
}
