<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_code', 'title', 'description', 'instructor_name',
        'duration_hours', 'lesson_count', 'is_published',
    ];
}
