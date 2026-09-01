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

    public function examSettings()
    {
        return $this->hasOne(ExamSetting::class);
    }

    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    
}
