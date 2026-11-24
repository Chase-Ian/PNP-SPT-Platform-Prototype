<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\Http\Requests\TwoFactorLoginRequest;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request)
    {
        abort_unless($request->session()->has('login.id'), 403);

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(TwoFactorLoginRequest $request)
    {
        if (! $request->hasChallengedUser()) {
            abort(403);
        }

        // Same rule as the password step: after 5 wrong codes (authenticator
        // or recovery) the user has to wait. Counted per account rather than
        // per browser, so starting a new session doesn't reset the count.
        $throttleKey = 'two-factor-challenge|'.$request->session()->get('login.id');

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'code' => "Too many incorrect codes. Please try again in {$seconds} seconds.",
            ]);
        }

        if (! $request->hasValidCode() && ! $request->validRecoveryCode()) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user = $request->challengedUser();

        Auth::login($user, $request->session()->pull('login.remember', false));
        $request->session()->forget('login.id');
        $request->session()->regenerate();

        return redirect()->intended(match ($user->role) {
            'admin' => route('admin.dashboard'),
            'supervisor' => route('supervisor.dashboard'),
            default => route('dashboard'),
        });
    }
}