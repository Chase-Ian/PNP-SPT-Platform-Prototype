<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use App\Services\AccountLockService;

class StaffController extends Controller
{
    private const RANKS = [
        'Police General (PGen)', 'Police Lieutenant General (PLtGen)', 'Police Major General (PMGen)',
        'Police Brigadier General (PBGen)', 'Police Colonel (PCol)', 'Police Lieutenant Colonel (PLtCol)',
        'Police Major (PMaj)', 'Police Captain (PCpt)', 'Police Lieutenant (PLt)',
        'Police Executive Master Sergeant (PEMS)', 'Police Chief Master Sergeant (PCMS)',
        'Police Senior Master Sergeant (PSMS)', 'Police Master Sergeant (PMSg)',
        'Police Staff Sergeant (PSSg)', 'Police Corporal (PCpl)', 'Patrolman/Patrolwoman (Pat)',
    ];

    private const REGIONS = [
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
    ];

    public function index()
    {
        $staff = User::whereIn('role', ['supervisor', 'admin'])
            ->select('id', 'first_name', 'last_name', 'rank', 'email', 'role', 'unit_office', 'region', 'is_locked', 'created_at')
            ->latest()
            ->get();

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $staff,
            'rankOptions' => self::RANKS,
            'regionOptions' => self::REGIONS,
        ]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'email' => preg_replace('/[\r\n]|%0[ad]/i', '', (string) $request->input('email')),
        ]);

        $request->validate([
            'role' => 'required|in:supervisor,admin',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'rank' => 'required|string|in:' . implode(',', self::RANKS),
            'email' => 'required|string|lowercase|email:filter|max:255|unique:users,email',
            'unit_office' => 'required|string|max:255',
            'region' => 'required|string|in:' . implode(',', self::REGIONS),
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'role' => $request->role,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'rank' => $request->rank,
            'email' => $request->email,
            'unit_office' => $request->unit_office,
            'region' => $request->region,
            'password' => Hash::make($request->password),
            'two_factor_verified' => true,
        ]);

        return back();
    }

    public function destroy(User $staff)
    {
        abort_unless(in_array($staff->role, ['supervisor', 'admin']), 403);
        abort_if($staff->id === request()->user()->id, 403, 'You cannot remove your own account.');

        $staff->delete();

        return back();
    }

    public function toggleLock(User $staff, AccountLockService $lockService)
    {
        abort_unless(in_array($staff->role, ['supervisor', 'admin']), 403);
        abort_if($staff->id === request()->user()->id, 403, 'You cannot lock your own account.');

        $lockService->toggle($staff);

        return back();
    }

}