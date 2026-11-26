<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AccountReinstated;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

/**
 * Deleting an account hides it: it can't sign in and its records disappear
 * from lists and certificate checks. An administrator can reinstate it until
 * it is erased, either by an administrator or automatically after
 * config('security.deleted_account_days') days (User::prunable()).
 */
class DeletedAccounts
{
    private const ROLES = ['admin' => 'Administrator', 'supervisor' => 'Supervisor', 'trainee' => 'Trainee'];

    public function retentionDays(): int
    {
        return max(1, (int) config('security.deleted_account_days', 30));
    }

    /** Rows for the "Deleted accounts" list on the admin pages. */
    public function rows(Builder $query): array
    {
        return $query->orderByDesc('deleted_at')
            ->get(['id', 'first_name', 'last_name', 'email', 'role', 'unit_office', 'deleted_at'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => self::ROLES[$user->role] ?? $user->role,
                'unit_office' => $user->unit_office,
                'deleted_on' => $user->deleted_at->format('Y-m-d'),
                'erase_on' => $user->deleted_at->copy()->addDays($this->retentionDays())->format('Y-m-d'),
            ])
            ->all();
    }

    public function delete(User $user, User $by): void
    {
        $user->delete();
        Log::info('Account deleted', ['user_id' => $user->id, 'by' => $by->id]);
    }

    public function reinstate(User $user, User $by): void
    {
        $user->restore();
        Log::info('Account reinstated', ['user_id' => $user->id, 'by' => $by->id]);
        $user->notify(new AccountReinstated($by->name));
    }

    public function erase(User $user, User $by): void
    {
        $user->forceDelete();
        Log::warning('Deleted account erased', ['user_id' => $user->id, 'by' => $by->id]);
    }
}
