<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\TwoFactorPolicy;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $policy = app(TwoFactorPolicy::class);

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'twoFactor' => [
                'setUp' => $policy->isSetUp($user),
                'required' => $policy->isRequiredFor($user),
            ],
            'emailRequests' => $user->emailChangeRequests()->latest()->limit(5)->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'new_email' => $r->new_email,
                    'status' => $r->status,
                    'admin_note' => $r->admin_note,
                    'created_at' => $r->created_at->format('Y-m-d'),
                ]),
        ]);
    }

    /**
     * Update the user's profile information. Email is not part of this:
     * see EmailChangeRequestController.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());
        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
