<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuleSlide extends Model
{
    use HasFactory;

    protected $fillable = ['module_id', 'slide_number', 'image_path'];
}