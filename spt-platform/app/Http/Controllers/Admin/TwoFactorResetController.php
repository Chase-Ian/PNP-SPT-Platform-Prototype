<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TwoFactorWasReset;
use App\Services\TwoFactorPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TwoFactorResetController extends Controller
{
    public function store(Request $request, User $user, TwoFactorPolicy $policy): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, 'You cannot reset your own MFA. Ask another administrator.');

        $policy->reset($user);

        Log::warning('MFA reset by admin', ['user_id' => $user->id, 'admin_id' => $request->user()->id]);
        $user->notify(new TwoFactorWasReset($request->user()->name));

        return back()->with('status', 'two-factor-reset');
    }
}
