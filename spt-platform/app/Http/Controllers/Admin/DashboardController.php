<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $activeOfficers = User::where('role', 'trainee')->count();
        $coursesPublished = Course::where('is_published', true)->count();
        $certificatesIssued = Certificate::count();

        $totalAttempts = ExamAttempt::count();
        $passedAttempts = ExamAttempt::where('passed', true)->count();
        $examCompletionRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100) : 0;

        $officers = User::where('role', 'trainee')
            ->with(['enrollments.course', 'certificates', 'examAttempts'])
            ->get()
            ->map(function ($officer) {
                $totalEnrollments = $officer->enrollments->count();
                $completedCount = $officer->enrollments->where('status', 'completed')->count();

                $courseStatus = match (true) {
                    $totalEnrollments === 0 => 'Not Enrolled',
                    $completedCount === $totalEnrollments => 'Completed',
                    $completedCount > 0 => 'In Progress',
                    default => 'Enrolled',
                };

                $latestExam = $officer->examAttempts->last();

                return [
                    'name' => $officer->name,
                    'unit_office' => $officer->unit_office,
                    'region' => $officer->region, // ← moved here, inside the closure
                    'course' => $totalEnrollments > 0 ? "{$completedCount}/{$totalEnrollments} Courses" : '—',
                    'course_status' => $courseStatus,
                    'modules_completed' => $officer->moduleCompletions()->whereNotNull('completed_at')->count(),
                    'exam_result' => $latestExam ? ($latestExam->passed ? 'Passed (' . $latestExam->score . ')' : 'Failed') : 'Pending',
                    'certificate_status' => $officer->certificates->isNotEmpty() ? 'Issued' : 'Pending',
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'activeOfficers' => $activeOfficers,
                'coursesPublished' => $coursesPublished,
                'examCompletionRate' => $examCompletionRate,
                'certificatesIssued' => $certificatesIssued,
            ],
            'officers' => $officers,
           
        ]);
    }
}