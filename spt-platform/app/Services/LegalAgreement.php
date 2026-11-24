<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Whether a user has accepted the current Terms of Service and Privacy
 * Notice, and recording it when they do.
 */
class LegalAgreement
{
    public function currentVersion(): string
    {
        return (string) config('legal.version');
    }

    public function hasAccepted(User $user): bool
    {
        return $user->terms_accepted_version === $this->currentVersion();
    }

    public function record(User $user, Request $request): void
    {
        $now = now();

        $user->forceFill([
            'terms_accepted_version' => $this->currentVersion(),
            'terms_accepted_at' => $now,
        ])->save();

        $user->legalAcceptances()->create([
            'version' => $this->currentVersion(),
            'ip_address' => $request->ip(),
            'accepted_at' => $now,
        ]);
    }
}
