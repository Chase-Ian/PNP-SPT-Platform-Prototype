<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Fortify;

/**
 * "Secure your account": scan QR (or type the key) -> confirm a 6-digit
 * code -> save recovery codes -> acknowledge. Used both when 2FA is forced
 * by the gate and when a user turns it on voluntarily from their profile.
 */
class TwoFactorSetupController extends Controller
{
    public function __construct(private TwoFactorPolicy $policy) {}

    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($this->policy->isSetUp($user)) {
            return redirect($this->home($user));
        }

        $step = match (true) {
            $user->two_factor_confirmed_at !== null => 'codes',
            ! empty($user->two_factor_secret) => 'scan',
            default => 'start',
        };

        return Inertia::render('Security/SetupTwoFactor', [
            'step' => $step,
            'required' => $this->policy->isRequiredFor($user),
            'graceEndsAt' => $this->policy->shouldRemind($user)
                ? $this->policy->graceEndsAt($user)?->toDateString()
                : null,
            'qrCodeSvg' => $step === 'scan' ? $user->twoFactorQrCodeSvg() : null,
            'secretKey' => $step === 'scan'
                ? Fortify::currentEncrypter()->decrypt($user->two_factor_secret)
                : null,
            'recoveryCodes' => $step === 'codes' ? $user->recoveryCodes() : [],
        ]);
    }

    /** Generate (or, with restart, regenerate) the secret. */
    public function start(Request $request, EnableTwoFactorAuthentication $enable): RedirectResponse
    {
        $user = $request->user();

        // Never re-key an already confirmed authenticator from here.
        abort_if($user->two_factor_confirmed_at !== null, 409);

        $enable($user, $request->boolean('restart'));

        return redirect()->route('security.setup');
    }

    public function confirm(Request $request, ConfirmTwoFactorAuthentication $confirm): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'digits:6']]);

        $user = $request->user();
        abort_if(empty($user->two_factor_secret), 409);

        try {
            $confirm($user, $request->input('code'));
        } catch (ValidationException) {
            throw ValidationException::withMessages([
                'code' => 'That code did not match. Check the time on your phone and try the newest code.',
            ]);
        }

        return redirect()->route('security.setup');
    }

    public function acknowledge(Request $request): RedirectResponse
    {
        $request->validate(['acknowledged' => ['accepted']], [
            'acknowledged.accepted' => 'Please confirm that you have saved your recovery codes.',
        ]);

        $user = $request->user();
        abort_if($user->two_factor_confirmed_at === null, 409);

        $user->forceFill(['two_factor_codes_acknowledged_at' => now()])->save();

        // The user is already signed in, so no further code is asked for in
        // this session.
        return redirect($this->home($user))->with('status', 'two-factor-enabled');
    }

    private function home(User $user): string
    {
        return match ($user->role) {
            'admin' => route('admin.dashboard'),
            'supervisor' => route('supervisor.dashboard'),
            default => route('dashboard'),
        };
    }
}
