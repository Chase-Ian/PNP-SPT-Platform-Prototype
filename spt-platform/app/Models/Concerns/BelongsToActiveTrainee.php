<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Training records only count for active trainee accounts. Records of a
 * deleted (not yet erased) account are hidden everywhere: lists, totals,
 * supervisor monitoring and the public certificate check, and come back when
 * an administrator reinstates it. Staff accounts can't train (each account has
 * exactly one role), so records left from the old trainee view are ignored.
 * Use withoutGlobalScope('activeTrainee') where every record must be changed.
 */
trait BelongsToActiveTrainee
{
    public static function bootBelongsToActiveTrainee(): void
    {
        static::addGlobalScope('activeTrainee', fn (Builder $query) => $query->whereHas('user', fn (Builder $user) => $user->where('role', 'trainee')));
    }
}
