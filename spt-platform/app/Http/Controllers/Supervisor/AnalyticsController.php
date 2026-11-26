<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\User;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Percentages are null when there is nothing to divide by, so the page
        // can say "No data yet" instead of showing a measured-looking 0%.
        $traineeAccounts = User::where('role', 'trainee')->count();
        $enrolledTrainees = Enrollment::whereHas('user', fn ($q) => $q->where('role', 'trainee'))
            ->distinct()->count('user_id');

        $totalAttempts = ExamAttempt::count();
        $passedAttempts = ExamAttempt::where('passed', true)->count();
        $avgPassRate = $totalAttempts > 0 ? (int) round(($passedAttempts / $totalAttempts) * 100) : null;

        $totalEnrollments = Enrollment::count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? (int) round(($completedEnrollments / $totalEnrollments) * 100) : null;

        $certificatesIssued = Certificate::count();

        // Regional Command Compliance — group trainees by region, show completed vs total enrollments
        $regions = User::where('role', 'trainee')
            ->whereNotNull('region')
            ->with('enrollments')
            ->get()
            ->groupBy('region')
            ->map(function ($users, $region) {
                $total = $users->sum(fn ($u) => $u->enrollments->count());
                $completed = $users->sum(fn ($u) => $u->enrollments->where('status', 'completed')->count());

                return [
                    'region' => $region,
                    'completed' => $completed,
                    'total' => $total,
                    'percent' => $total > 0 ? (int) round(($completed / $total) * 100) : null,
                    'trainees' => $users->count(),
                ];
            })
            ->sortByDesc(fn ($r) => [$r['percent'] ?? -1, $r['total']])
            ->values();

        // Course Enrollment & Exam Performance — per course, enrollment count + pass rate
        $coursePerformance = Course::withCount('enrollments')
            ->get()
            ->map(function ($course) {
                $attempts = ExamAttempt::where('course_id', $course->id)->get();
                $passed = $attempts->where('passed', true)->count();
                $passRate = $attempts->count() > 0 ? (int) round(($passed / $attempts->count()) * 100) : null;
                // Average grade in percent (raw scores differ by question count).
                $avgPercent = $attempts->count() > 0 ? (int) round($attempts->avg(fn ($a) => $a->percent())) : null;

                return [
                    'title' => $course->title,
                    'enrollments_count' => $course->enrollments_count,
                    'attempts' => $attempts->count(),
                    'passed' => $passed,
                    'pass_rate' => $passRate,
                    'avg_percent' => $avgPercent,
                ];
            });

        return Inertia::render('Supervisor/Analytics', [
            'stats' => [
                'traineeAccounts' => $traineeAccounts,
                'enrolledTrainees' => $enrolledTrainees,
                'avgPassRate' => $avgPassRate,
                'examAttempts' => $totalAttempts,
                'examAttemptsPassed' => $passedAttempts,
                'completionRate' => $completionRate,
                'enrollments' => $totalEnrollments,
                'completedEnrollments' => $completedEnrollments,
                'certificatesIssued' => $certificatesIssued,
            ],
            'regions' => $regions,
            'coursePerformance' => $coursePerformance,
        ]);
    }
}