<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamAttempt extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'score', 'passed', 'answers'];

    protected $casts = ['answers' => 'array', 'passed' => 'boolean'];
}