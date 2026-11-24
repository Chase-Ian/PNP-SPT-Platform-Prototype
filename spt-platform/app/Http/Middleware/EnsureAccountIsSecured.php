<?php

namespace App\Http\Middleware;

use App\Services\TwoFactorPolicy;
use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The one gate every signed-in request passes through, whatever path the
 * user took to get here (fresh registration, login, a reopened tab).
 *
 *   1. Email not verified          -> verify-email page
 *   2. 2FA required, not set up    -> "Secure your account" page
 *
 * Email comes first: the verified address is the account's recovery route,
 * so 2FA is only bound to a confirmed identity. While a step is unfinished
 * the only other thing the user can do is log out.
 */
class EnsureAccountIsSecured
{
    /** Routes an unfinished user may still reach, per step. */
    private const EMAIL_STEP_ROUTES = [
        'logout',
        'verification.notice',
        'verification.verify',
        'verification.send',
    ];

    private const TWO_FACTOR_STEP_ROUTES = [
        'logout',
        'security.setup',
        'security.setup.start',
        'security.setup.confirm',
        'security.setup.acknowledge',
    ];

    public function __construct(private TwoFactorPolicy $policy) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $route = $request->route()?->getName();

        // Step 1: email verification.
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            if (in_array($route, self::EMAIL_STEP_ROUTES, true)) {
                return $next($request);
            }

            return $this->deny($request, route('verification.notice'));
        }

        // Step 2: required two-factor setup.
        if ($this->policy->mustSetUpNow($user)) {
            if (in_array($route, self::TWO_FACTOR_STEP_ROUTES, true)) {
                return $next($request);
            }

            return $this->deny($request, route('security.setup'));
        }

        // Once 2FA is required, it can't be switched off or silently
        // re-keyed from the profile page. Recovery codes can still be
        // regenerated; a lost device goes through an admin reset or the
        // emailed recovery link.
        if ($this->policy->isRequiredFor($user)
            && in_array($route, ['two-factor.disable', 'two-factor.enable'], true)
            && $user->two_factor_confirmed_at) {
            abort(403, 'Two-factor authentication is required for your account and cannot be turned off.');
        }

        return $next($request);
    }

    private function deny(Request $request, string $to): Response
    {
        if ($request->expectsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['message' => 'Account setup is not finished.', 'redirect' => $to], 403);
        }

        return redirect()->guest($to);
    }
}
