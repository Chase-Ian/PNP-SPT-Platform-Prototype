<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'score', 'passed', 'answers', 'archived_at'];

    // archived_at: set when a supervisor archives the course's results after a
    // major change. The attempt stays as history but no longer counts.
    protected $casts = ['answers' => 'array', 'passed' => 'boolean', 'archived_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}