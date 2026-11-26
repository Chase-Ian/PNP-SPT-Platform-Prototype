<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveTrainee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use BelongsToActiveTrainee, HasFactory;

    protected $fillable = [
        'user_id', 'course_id', 'serial_id',
        'training_ctrl_no', 'verification_hash', 'pdf_path', 'issued_at', 'archived_at',
    ];

    // archived_at: issued for an earlier version of a course whose results a
    // supervisor archived. Still genuine and verifiable, just no longer current.
    protected $casts = ['issued_at' => 'datetime', 'archived_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Returns the public URL that can be used to verify this certificate.
     */
    public function getVerificationUrlAttribute(): string
    {
        return url('/verify/' . $this->serial_id);
    }
}