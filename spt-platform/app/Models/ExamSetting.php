<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamSetting extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'question_count', 'time_limit_minutes', 'pass_threshold_percent'];
}