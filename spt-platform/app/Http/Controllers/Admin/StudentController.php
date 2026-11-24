<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use App\Services\AccountLockService;

class StudentController extends Controller
{
    /**
     * Trainee Accounts: account details and security status, so admins can
     * lock, reset 2FA or delete accounts. Training results are monitored by
     * supervisors (Supervisor\MonitoringController), not shown here.
     */
    public function index()
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
        ]);
    }
    public function destroy(User $student)
    {
        abort_unless($student->role === 'trainee', 403);
        $student->delete();

        return back();
    }

    public function toggleLock(User $student, AccountLockService $lockService)
    {
        abort_unless($student->role === 'trainee', 403);

        $lockService->toggle($student);

        return back();
    }
}