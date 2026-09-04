<?php
// app/Models/Lesson.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'order', 'title', 'content', 'duration_minutes'];

    public function course()
    {
        return $this->belongsTo(Course::class);
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