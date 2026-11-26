<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveTrainee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use BelongsToActiveTrainee, HasFactory;

    protected $fillable = ['user_id', 'course_id', 'score', 'total_questions', 'passed', 'answers', 'archived_at'];

    // archived_at: set when a supervisor archives the course's results after a
    // major change. The attempt stays as history but no longer counts.
    protected $casts = ['answers' => 'array', 'passed' => 'boolean', 'archived_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Number of questions the exam had when this attempt was taken. */
    public function questionCount(): int
    {
        if ($this->total_questions !== null) {
            return (int) $this->total_questions;
        }

        // Attempts from before total_questions was stored: best estimate.
        $course = $this->course;

        return $course ? ($course->relationLoaded('examQuestions') ? $course->examQuestions->count() : $course->examQuestions()->count()) : 0;
    }

    public function percent(): int
    {
        $total = $this->questionCount();

        return $total > 0 ? (int) min(100, round($this->score / $total * 100)) : 0;
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}