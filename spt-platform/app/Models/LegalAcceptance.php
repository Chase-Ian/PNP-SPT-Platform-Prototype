<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One row per time a user accepted the Terms of Service and Privacy Notice. */
class LegalAcceptance extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'version', 'ip_address', 'accepted_at'];

    protected function casts(): array
    {
        return ['accepted_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
