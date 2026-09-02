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
        // Security mitigation — see docs/SECURITY_ADVISORIES.md (GHSA-5vg9-5847-vvmq)
        $request->merge([
            'email' => preg_replace('/[\r\n]|%0[ad]/i', '', (string) $request->input('email')),
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email:filter|max:255|unique:'.User::class,
            'badge_number' => 'required|string|max:50',
            'unit_office' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'badge_number' => $request->badge_number,
            'unit_office' => $request->unit_office,
            'region' => $request->region,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
