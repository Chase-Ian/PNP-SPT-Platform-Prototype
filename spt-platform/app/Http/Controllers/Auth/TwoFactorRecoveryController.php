<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TwoFactorRecoveryLink;
use App\Services\TwoFactorPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Lost authenticator: after the password step of login,
 * the user can have a reset link sent to their verified email. The link only
 * works in the same browser session that passed the password check, so the
 * reset still needs two factors (password + mailbox).
 */
class TwoFactorRecoveryController extends Controller
{
    public function __construct(private TwoFactorPolicy $policy) {}

    public function send(Request $request): RedirectResponse
    {
        $user = User::find($request->session()->get('login.id'));
        abort_unless($user, 403);

        if ($user->hasVerifiedEmail()) {
            $minutes = config('security.recovery_link_minutes', 30);
            $url = URL::temporarySignedRoute(
                'two-factor.recovery.verify',
                now()->addMinutes($minutes),
                ['user' => $user->getKey()],
            );

            $user->notify(new TwoFactorRecoveryLink($url, $minutes));
            Log::info('MFA email recovery link sent', ['user_id' => $user->id]);
        }

        return back()->with('status', 'recovery-link-sent');
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->session()->get('login.id') !== $user->getKey()) {
            return redirect()->route('login')->withErrors([
                'email' => 'This reset link must be opened in the same browser where you signed in. Sign in again, then request a new link.',
            ]);
        }

        if ($user->is_locked) {
            return redirect()->route('login')->withErrors([
                'email' => 'This account has been locked. Please contact your administrator.',
            ]);
        }

        $this->policy->reset($user);
        Log::warning('MFA reset via email recovery link', ['user_id' => $user->id]);

        Auth::login($user, $request->session()->pull('login.remember', false));
        $request->session()->forget('login.id');
        $request->session()->regenerate();

        // The gate sends them to setup if 2FA is required; otherwise go there
        // anyway so they can re-bind their new device right away.
        return redirect()->route('security.setup');
    }
}
