<?php
// app/Models/LessonQuizQuestion.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonQuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_id', 'question', 'choices', 'correct_choice'];
    protected $casts = ['choices' => 'array'];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}