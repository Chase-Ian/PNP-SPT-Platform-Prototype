<?php

namespace App\Models;

use App\Models\Concerns\BelongsToActiveTrainee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use BelongsToActiveTrainee, HasFactory;

    protected $fillable = ['user_id', 'course_id', 'status'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}