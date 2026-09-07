<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'module_id', 'order', 'title', 'content', 'duration_minutes'];
    protected $casts = ['content' => 'array'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function quizQuestions()
    {
        return $this->hasMany(LessonQuizQuestion::class);
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }
}