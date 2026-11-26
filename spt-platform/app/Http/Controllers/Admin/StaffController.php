<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Support\PnpRegions;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use App\Services\AccountLockService;
use App\Services\DeletedAccounts;

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


    public function index(DeletedAccounts $deleted)
    {
        $staff = User::whereIn('role', ['supervisor', 'admin'])
            ->select('id', 'first_name', 'last_name', 'rank', 'email', 'role', 'unit_office', 'region', 'is_locked', 'two_factor_confirmed_at', 'created_at')
            ->latest()
            ->get();

        return Inertia::render('Admin/Staff/Index', [
            'staff' => $staff,
            'deleted' => $deleted->rows(User::onlyTrashed()->whereIn('role', ['supervisor', 'admin'])),
            'retentionDays' => $deleted->retentionDays(),
            'rankOptions' => self::RANKS,
            'regionOptions' => PnpRegions::all(),
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
            'region' => ['required', 'string', Rule::in(PnpRegions::all())],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], User::onlyTrashed()->where('email', strtolower((string) $request->input('email')))->exists()
            ? ['email.unique' => 'This email belongs to a deleted account. Reinstate it under "Deleted accounts" instead.']
            : []);

        $staff = User::create([
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

        // Staff confirm their own address on first sign-in, like trainees.
        $staff->sendEmailVerificationNotification();

        return back();
    }

    /** Hides the account; it can be reinstated until it is erased. */
    public function destroy(Request $request, User $staff, DeletedAccounts $deleted)
    {
        abort_unless(in_array($staff->role, ['supervisor', 'admin']), 403);
        abort_if($staff->id === request()->user()->id, 403, 'You cannot remove your own account.');

        $deleted->delete($staff, $request->user());

        return back();
    }

    public function reinstate(Request $request, User $staff, DeletedAccounts $deleted)
    {
        abort_unless(in_array($staff->role, ['supervisor', 'admin'], true) && $staff->trashed(), 404);
        $deleted->reinstate($staff, $request->user());

        return back();
    }

    /** Erases a deleted account now instead of waiting for the retention period. */
    public function erase(Request $request, User $staff, DeletedAccounts $deleted)
    {
        abort_unless(in_array($staff->role, ['supervisor', 'admin'], true) && $staff->trashed(), 404);
        $deleted->erase($staff, $request->user());

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