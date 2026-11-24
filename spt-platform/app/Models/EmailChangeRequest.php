<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailChangeRequest extends Model
{
    protected $fillable = [
        'user_id', 'current_email', 'new_email', 'reason',
        'status', 'reviewed_by', 'reviewed_at', 'admin_note',
    ];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
