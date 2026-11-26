<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use App\Notifications\CustomResetPassword;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, Prunable, SoftDeletes, TwoFactorAuthenticatable;
    
    protected $fillable = [
        'first_name', 'last_name', 'rank', 'email', 'password',
        'unit_office', 'region', 'two_factor_verified', 'role', 'poa_user_id',
        'is_locked', 'locked_at', 
    ];

    // auth.user is shared with every Inertia page, so 2FA secrets and
    // recovery codes must never be serialized.
    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    // The database column still defaults to the retired 'officer' role from
    // the first migration, so set the default here for any account created
    // without an explicit role.
    protected $attributes = ['role' => 'trainee'];

    // Makes ->name available in PHP AND included in Inertia's JSON serialization
    protected $appends = ['name'];

    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function moduleCompletions()
    {
        return $this->hasMany(ModuleCompletion::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function examAttempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }
    
    /**
     * A deleted account can be reinstated for a while; after
     * config('security.deleted_account_days') days the daily `model:prune`
     * erases it, with its training records and certificates.
     */
    public function prunable(): Builder
    {
        return static::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(config('security.deleted_account_days', 30)));
    }

    protected static function booted(): void
    {
        // Erasing an account also clears data that isn't linked to it by a
        // foreign key (everything else goes by cascade).
        static::forceDeleted(function (User $user) {
            $user->notifications()->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
        });
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')->withPivot('status')->withTimestamps();
    }

    public function legalAcceptances()
    {
        return $this->hasMany(LegalAcceptance::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPassword($token));
    }
}
