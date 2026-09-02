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
    Public function store(Request $request): RedirectResponse
{
    // Security mitigation: strip CRLF sequences from user-supplied email
    // before validation. Laravel 11.x has an unpatched CRLF injection
    // advisory in the default `email` rule (GHSA-5vg9-5847-vvmq).
    // See docs/SECURITY_ADVISORIES.md.
    $request->merge([
        'email' => preg_replace('/[\r\n]|%0[ad]/i', '', (string) $request->input('email')),
    ]);

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email:filter|max:255|unique:'.User::class,
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    event(new Registered($user));
    Auth::login($user);

    return redirect(route('dashboard', absolute: false));
}
}
