<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LegalAgreement;
use App\Support\PnpRegions;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', ['regionOptions' => PnpRegions::all()]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, LegalAgreement $legal): RedirectResponse
    {
        $request->merge([
            'email' => preg_replace('/[\r\n]|%0[ad]/i', '', (string) $request->input('email')),
        ]);

$request->validate([
    'first_name' => 'required|string|max:100',
    'last_name' => 'required|string|max:100',
    'rank' => 'required|string|in:' . implode(',', [
        'Police General (PGen)',
        'Police Lieutenant General (PLtGen)',
        'Police Major General (PMGen)',
        'Police Brigadier General (PBGen)',
        'Police Colonel (PCol)',
        'Police Lieutenant Colonel (PLtCol)',
        'Police Major (PMaj)',
        'Police Captain (PCpt)',
        'Police Lieutenant (PLt)',
        'Police Executive Master Sergeant (PEMS)',
        'Police Chief Master Sergeant (PCMS)',
        'Police Senior Master Sergeant (PSMS)',
        'Police Master Sergeant (PMSg)',
        'Police Staff Sergeant (PSSg)',
        'Police Corporal (PCpl)',
        'Patrolman/Patrolwoman (Pat)',
    ]),
    'email' => 'required|string|lowercase|email:filter|max:255|unique:'.User::class,
    'unit_office' => 'required|string|max:255',
     'region' => ['required', 'string', Rule::in(PnpRegions::all())],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
    'terms' => ['accepted'],
], [
    'terms.accepted' => 'You must agree to the Terms of Service and acknowledge the Privacy Notice to register.',
    ...(User::onlyTrashed()->where('email', strtolower((string) $request->input('email')))->exists()
        ? ['email.unique' => 'This email belongs to a deleted account. Ask an administrator to reinstate it.']
        : []),
]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'rank' => $request->rank,
            'email' => $request->email,
            'unit_office' => $request->unit_office,
            'region' => $request->region,
            'password' => Hash::make($request->password),
            // Self-registration is for trainees only. Supervisor and Admin
            // accounts are created by an Admin in Manage Staff.
            'role' => 'trainee',
        ]);

        // Proof of agreement: version, time and IP address (see LegalAgreement).
        $legal->record($user, $request);

        event(new Registered($user));
        Auth::login($user);

        // Step 1 of the account gate: verify email (then 2FA setup if required).
        return redirect()->route('verification.notice');
    }
}
