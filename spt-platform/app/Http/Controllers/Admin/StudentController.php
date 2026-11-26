<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountLockService;
use App\Services\DeletedAccounts;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Trainee Accounts: account details and security status, so admins can
     * lock, unlock, reset MFA, delete or reinstate accounts. Training results
     * are monitored by supervisors (Supervisor\MonitoringController), not here.
     */
    public function index(DeletedAccounts $deleted)
    {
        $trainees = User::where("role", "trainee")
            ->orderBy("last_name")->orderBy("first_name")
            ->get(["id", "first_name", "last_name", "rank", "email", "unit_office", "region",
                "is_locked", "email_verified_at", "two_factor_confirmed_at", "created_at"]);

        return Inertia::render("Admin/Students/Index", [
            "stats" => [
                "total" => $trainees->count(),
                "locked" => $trainees->where("is_locked", true)->count(),
                "withTwoFactor" => $trainees->whereNotNull("two_factor_confirmed_at")->count(),
            ],
            "accounts" => $trainees->map(fn ($t) => [
                "id" => $t->id,
                "name" => $t->name,
                "rank" => $t->rank,
                "email" => $t->email,
                "unit_office" => $t->unit_office,
                "region" => $t->region,
                "is_locked" => (bool) $t->is_locked,
                "email_verified" => $t->email_verified_at !== null,
                "two_factor_enabled" => $t->two_factor_confirmed_at !== null,
                "joined" => $t->created_at?->format("Y-m-d"),
            ])->values(),
            "deleted" => $deleted->rows(User::onlyTrashed()->where("role", "trainee")),
            "retentionDays" => $deleted->retentionDays(),
        ]);
    }

    /** Hides the account; it can be reinstated until it is erased. */
    public function destroy(Request $request, User $student, DeletedAccounts $deleted)
    {
        abort_unless($student->role === 'trainee', 403);
        $deleted->delete($student, $request->user());

        return back();
    }

    public function reinstate(Request $request, User $student, DeletedAccounts $deleted)
    {
        abort_unless($student->role === 'trainee' && $student->trashed(), 404);
        $deleted->reinstate($student, $request->user());

        return back();
    }

    /** Erases a deleted account now instead of waiting for the retention period. */
    public function erase(Request $request, User $student, DeletedAccounts $deleted)
    {
        abort_unless($student->role === 'trainee' && $student->trashed(), 404);
        $deleted->erase($student, $request->user());

        return back();
    }

    public function toggleLock(User $student, AccountLockService $lockService)
    {
        abort_unless($student->role === 'trainee', 403);

        $lockService->toggle($student);

        return back();
    }
}
