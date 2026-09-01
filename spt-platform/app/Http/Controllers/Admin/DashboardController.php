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
                $enrollment = $officer->enrollments->first();
                $latestExam = $officer->examAttempts->last();

                return [
                    'name' => $officer->name,
                    'unit_office' => $officer->unit_office,
                    'course' => $enrollment?->course?->title ?? '—',
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