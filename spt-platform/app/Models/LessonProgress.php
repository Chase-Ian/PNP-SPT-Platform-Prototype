<?php
// app/Models/LessonProgress.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'lesson_id', 'quiz_score', 'quiz_passed', 'completed_at'];
    protected $casts = ['quiz_passed' => 'boolean', 'completed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}