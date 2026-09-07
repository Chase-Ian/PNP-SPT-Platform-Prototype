<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'type', 'question', 'answer_data'];

    protected $casts = ['answer_data' => 'array'];

}