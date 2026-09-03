<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
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
    'region' => 'required|string|in:' . implode(',', [/* PRO list, unchanged */]),
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'rank' => $request->rank,
            'email' => $request->email,
            'unit_office' => $request->unit_office,
            'region' => $request->region,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
