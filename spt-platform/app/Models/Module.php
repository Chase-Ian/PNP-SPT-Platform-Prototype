<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'order', 'title', 'description',
        'file_path', 'file_type', 'duration_minutes',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function completions()
    {
        return $this->hasMany(ModuleCompletion::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}