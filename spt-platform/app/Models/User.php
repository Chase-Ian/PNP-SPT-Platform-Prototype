<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
        protected $fillable = [
            'name', 'email', 'password',
            'badge_number', 'unit_office', 'region', 'two_factor_verified', 'role', 'poa_user_id',
        ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
    
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')->withPivot('status')->withTimestamps();
    }
}
