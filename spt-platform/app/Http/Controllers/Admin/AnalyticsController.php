<?php

namespace App\Http\Controllers\Admin;

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
        $totalEnrolled = User::where('role', 'trainee')->count();

        $totalAttempts = ExamAttempt::count();
        $passedAttempts = ExamAttempt::where('passed', true)->count();
        $avgPassRate = $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100) : 0;

        $totalEnrollments = Enrollment::count();
        $completedEnrollments = Enrollment::where('status', 'completed')->count();
        $completionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100) : 0;

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
                    'percent' => $total > 0 ? round(($completed / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('percent')
            ->values();

        // Course Enrollment & Exam Performance — per course, enrollment count + pass rate
        $coursePerformance = Course::withCount('enrollments')
            ->get()
            ->map(function ($course) {
                $attempts = ExamAttempt::where('course_id', $course->id)->get();
                $passRate = $attempts->count() > 0
                    ? round(($attempts->where('passed', true)->count() / $attempts->count()) * 100)
                    : 0;
                $avgScore = $attempts->count() > 0 ? round($attempts->avg('score')) : 0;

                return [
                    'title' => $course->title,
                    'enrollments_count' => $course->enrollments_count,
                    'pass_rate' => $passRate,
                    'avg_score' => $avgScore,
                ];
            });

        return Inertia::render('Admin/Analytics/Index', [
            'stats' => [
                'totalEnrolled' => $totalEnrolled,
                'avgPassRate' => $avgPassRate,
                'completionRate' => $completionRate,
                'certificatesIssued' => $certificatesIssued,
            ],
            'regions' => $regions,
            'coursePerformance' => $coursePerformance,
        ]);
    }
}