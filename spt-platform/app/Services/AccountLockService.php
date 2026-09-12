<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountLockService
{
    public function toggle(User $user): User
    {
        \Log::info('Toggling lock', ['user_id' => $user->id, 'before' => $user->is_locked]);

        $user->update([
            'is_locked' => ! $user->is_locked,
            'locked_at' => ! $user->is_locked ? now() : null,
        ]);

        \Log::info('After toggle', ['after' => $user->fresh()->is_locked]);

        if ($user->is_locked) {
            $this->killSessions($user);
        }

        return $user;
    }

    public function lock(User $user): User
    {
        $user->update(['is_locked' => true, 'locked_at' => now()]);
        $this->killSessions($user);

        return $user;
    }

    public function unlock(User $user): User
    {
        $user->update(['is_locked' => false, 'locked_at' => null]);

        return $user;
    }

    private function killSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}