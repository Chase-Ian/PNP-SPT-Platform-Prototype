<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\User;
use Inertia\Inertia;

/**
 * System overview only. Trainee monitoring (statuses, results, analytics)
 * belongs to supervisors: see Supervisor\MonitoringController.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $totalAttempts = ExamAttempt::count();
        $passedAttempts = ExamAttempt::where('passed', true)->count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                // All trainee accounts, locked ones included.
                'traineeAccounts' => User::where('role', 'trainee')->count(),
                'coursesPublished' => Course::where('is_published', true)->count(),
                // null = no final exam taken yet (shown as "No results yet", not 0%).
                'examPassRate' => $totalAttempts > 0 ? (int) round(($passedAttempts / $totalAttempts) * 100) : null,
                'examAttempts' => $totalAttempts,
                'examAttemptsPassed' => $passedAttempts,
                'certificatesIssued' => Certificate::count(),
                'lockedAccounts' => User::where('is_locked', true)->count(),
                'deletedAccounts' => User::onlyTrashed()->count(),
            ],
        ]);
    }
}
