<?php

namespace App\Http\Middleware;

use App\Services\LegalAgreement;
use App\Services\TwoFactorPolicy;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * The one gate every signed-in request passes through, whatever path the
 * user took to get here (fresh registration, login, a reopened tab).
 *
 *   0. Account locked                        -> signed out, back to login
 *   1. Email not verified                    -> verify-email page
 *   2. Current Terms/Privacy Notice not accepted -> accept page
 *   3. 2FA required, not set up              -> "Secure your account" page
 *
 * Email comes first: the verified address is the account's recovery route,
 * so 2FA is only bound to a confirmed identity. While a step is unfinished
 * the user can only finish it, read the Terms and Privacy Notice, or log out.
 */
class EnsureAccountIsSecured
{
    /** Reachable at every step. */
    private const ALWAYS_ALLOWED = [
        'logout',
        'legal.terms',
        'legal.privacy',
    ];

    private const EMAIL_STEP_ROUTES = [
        'verification.notice',
        'verification.verify',
        'verification.send',
    ];

    private const TERMS_STEP_ROUTES = [
        'legal.accept',
        'legal.accept.store',
    ];

    private const TWO_FACTOR_STEP_ROUTES = [
        'security.setup',
        'security.setup.start',
        'security.setup.confirm',
    ];

    public function __construct(
        private TwoFactorPolicy $policy,
        private LegalAgreement $legal,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        // Step 0: a locked account is signed out on its next request, however
        // it is still signed in: a session that was open when an Admin locked
        // it (file sessions can't be ended from the lock action), or
        // "Remember me" signing it back in without the password check.
        if ($user->is_locked) {
            return $this->signOutLocked($request);
        }

        $route = $request->route()?->getName();

        // MFA has no recovery codes: Fortify's endpoints that show or
        // regenerate them are switched off.
        if (in_array($route, ['two-factor.recovery-codes', 'two-factor.regenerate-recovery-codes'], true)) {
            abort(404);
        }
        $allowedOnThisStep = fn (array $stepRoutes) => in_array($route, [...self::ALWAYS_ALLOWED, ...$stepRoutes], true);

        // Step 1: email verification.
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return $allowedOnThisStep(self::EMAIL_STEP_ROUTES)
                ? $next($request)
                : $this->deny($request, route('verification.notice'));
        }

        // Step 2: the current Terms of Service and Privacy Notice.
        if (! $this->legal->hasAccepted($user)) {
            return $allowedOnThisStep(self::TERMS_STEP_ROUTES)
                ? $next($request)
                : $this->deny($request, route('legal.accept'));
        }

        // Step 3: required MFA setup.
        if ($this->policy->mustSetUpNow($user)) {
            return $allowedOnThisStep(self::TWO_FACTOR_STEP_ROUTES)
                ? $next($request)
                : $this->deny($request, route('security.setup'));
        }

        // Once MFA is required, it can't be switched off or silently
        // re-keyed from the profile page. A lost device goes through an
        // admin reset or the emailed reset link.
        if ($this->policy->isRequiredFor($user)
            && in_array($route, ['two-factor.disable', 'two-factor.enable'], true)
            && $user->two_factor_confirmed_at) {
            abort(403, 'MFA is required for your account and cannot be turned off.');
        }

        return $next($request);
    }

    private function signOutLocked(Request $request): Response
    {
        // Logging out also clears the "Remember me" cookie and its token.
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'This account has been locked. Please contact your administrator.';

        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }

    private function deny(Request $request, string $to): Response
    {
        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => 'Account setup is not finished.', 'redirect' => $to], 403);
        }

        return redirect()->guest($to);
    }
}
