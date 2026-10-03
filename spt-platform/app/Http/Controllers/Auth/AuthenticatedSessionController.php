<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return inertia('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        if (! Auth::validate($request->only('email', 'password'))) {
            RateLimiter::hit($request->throttleKey());

            return back()->withErrors(['email' => trans('auth.failed')]);
        }

        $user = Auth::getLastAttempted();

        // Lock check happens before any 2FA detour — a locked account
        // should never reach the 2FA challenge at all.
        if ($user->is_locked) {
            return redirect()->route('login')->withErrors([
                'email' => 'This account has been locked. Please contact your administrator.',
            ]);
        }

        RateLimiter::clear($request->throttleKey());

        // 2FA detour: don't log in yet. Stash identity in the session and
        // redirect to the challenge; Auth::login() only happens once that
        // challenge is passed, in TwoFactorChallengeController::store().
        if ($user->two_factor_confirmed_at) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $request->boolean('remember'),
            ]);

            return redirect()->route('two-factor.login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathForRole($user));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function redirectPathForRole(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'supervisor' => route('supervisor.dashboard'),
            default => route('dashboard'),
        };
    }
}