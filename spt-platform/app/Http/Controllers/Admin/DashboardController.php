<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\EmailChangeRequest;
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
                'activeOfficers' => User::where('role', 'trainee')->count(),
                'coursesPublished' => Course::where('is_published', true)->count(),
                'examCompletionRate' => $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100) : 0,
                'certificatesIssued' => Certificate::count(),
                'lockedAccounts' => User::where('is_locked', true)->count(),
                'pendingEmailRequests' => EmailChangeRequest::pending()->count(),
            ],
        ]);
    }
}
