<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'course_id', 'serial_id',
        'verification_hash', 'pdf_path', 'issued_at',
    ];

    protected $casts = ['issued_at' => 'datetime'];
}