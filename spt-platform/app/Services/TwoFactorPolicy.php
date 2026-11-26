<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Single source of truth for "does this user have to have MFA, and are they
 * done?". Used by the gate middleware, the setup page, the profile page and
 * the Inertia shared props, so every path answers the same way.
 */
class TwoFactorPolicy
{
    public function isRequiredFor(User $user): bool
    {
        if (in_array(strtolower($user->email), config('security.exempt_emails', []), true)) {
            return false;
        }

        return match (config('security.require_2fa')) {
            'all' => true,
            'staff' => in_array($user->role, ['admin', 'supervisor'], true),
            default => false,
        };
    }

    /**
     * Set up = the authenticator code was confirmed. A secret that was never
     * confirmed (setup left half-way) counts as not set up.
     */
    public function isSetUp(User $user): bool
    {
        return $user->two_factor_confirmed_at !== null;
    }

    /**
     * End of the grace period for accounts that existed before enforcement
     * started, or null if this user has no grace period.
     */
    public function graceEndsAt(User $user): ?Carbon
    {
        $since = config('security.enforced_since');
        if (! $since) {
            return null;
        }

        $since = Carbon::parse($since)->startOfDay();
        if (! $user->created_at || $user->created_at->gte($since)) {
            return null;
        }

        return $since->copy()->addDays(config('security.grace_days', 14));
    }

    public function inGracePeriod(User $user): bool
    {
        $ends = $this->graceEndsAt($user);

        return $ends !== null && now()->lt($ends);
    }

    /** Required, not set up, and no grace left: send them to setup. */
    public function mustSetUpNow(User $user): bool
    {
        return $this->isRequiredFor($user)
            && ! $this->isSetUp($user)
            && ! $this->inGracePeriod($user);
    }

    /** Required, not set up, still in grace: show a reminder. */
    public function shouldRemind(User $user): bool
    {
        return $this->isRequiredFor($user)
            && ! $this->isSetUp($user)
            && $this->inGracePeriod($user);
    }

    public function reset(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }
}
