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
     'region' => 'required|string|in:' . implode(',', [
        'PRO NCR - National Capital Region (NCR)',
        'PRO 1 - Region 1 - Ilocos Region',
        'PRO 2 - Region 2 - Cagayan Valley',
        'PRO 3 - Region 3 - Central Luzon',
        'PRO 4A - Region 4A - CALABARZON',
        'PRO 4B - Region 4B - MIMAROPA',
        'PRO 5 - Region 5 - Bicol Region',
        'PRO 6 - Region 6 - Western Visayas',
        'PRO 7 - Region 7 - Central Visayas',
        'PRO 8 - Region 8 - Eastern Visayas',
        'PRO 9 - Region 9 - Zamboanga Peninsula',
        'PRO 10 - Region 10 - Northern Mindanao',
        'PRO 11 - Region 11 - Davao Region',
        'PRO 12 - Region 12 - SOCCSKSARGEN',
        'PRO 13 - Region 13 - Caraga Region',
        'PRO BARMM - Bangsamoro Autonomous Region (BARMM)',
        'PRO CAR - Cordillera Administrative Region (CAR)',
        'NHQ Camp Crame - PNP National Headquarters',
    ]),
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
