<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleCompletion extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'module_id', 'completed_at', 'minutes_spent'];

    protected $casts = ['completed_at' => 'datetime'];
}