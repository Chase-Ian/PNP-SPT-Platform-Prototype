<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Laravel\Fortify\TwoFactorAuthenticationProvider;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request)
    {
        abort_unless($request->session()->has('login.id'), 403);

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        abort_unless($request->session()->has('login.id'), 403);

        $user = User::findOrFail($request->session()->get('login.id'));
        $valid = false;

        if ($request->filled('code')) {
            $valid = app(TwoFactorAuthenticationProvider::class)
                ->verify($user->twoFactorAuthenticationSecret(), $request->code);
        } elseif ($request->filled('recovery_code')) {
            $codes = $user->recoveryCodes();
            if (in_array($request->recovery_code, $codes, true)) {
                $user->replaceRecoveryCode($request->recovery_code);
                $valid = true;
            }
        }

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

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