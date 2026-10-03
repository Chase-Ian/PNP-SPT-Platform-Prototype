<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (! $request->hasValidCode() && ! $request->validRecoveryCode()) {
            throw ValidationException::withMessages([
                'code' => 'The provided two-factor authentication code was invalid.',
            ]);
        }

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