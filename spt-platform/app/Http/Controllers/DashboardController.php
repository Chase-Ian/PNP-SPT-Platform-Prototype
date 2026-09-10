<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $coursesEnrolled = $user->enrollments()->count();
        $completedModules = $user->moduleCompletions()->whereNotNull('completed_at')->count();
        $hoursSpent = round($user->moduleCompletions()->sum('minutes_spent') / 60, 1);
        $certificatesEarned = $user->certificates()->count();

        $enrolledCourseIds = $user->enrollments()->pluck('course_id');
        $newModulesCount = \App\Models\Course::where('is_published', true)
            ->whereNotIn('id', $enrolledCourseIds)
            ->count();

        $recentExams = $user->examAttempts()
            ->with('course')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($attempt) => [
                'title' => $attempt->course->title . ' Final Exam',
                'completed_on' => $attempt->created_at->format('Y-m-d'),
                'score_percent' => $attempt->course
                ? round(($attempt->score / max($this->totalExamPoints($attempt->course), 1)) * 100)
                : null,
                'passed' => $attempt->passed,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'coursesEnrolled' => $coursesEnrolled,
                'hoursSpent' => $hoursSpent,
                'completedModules' => $completedModules,
                'certificatesEarned' => $certificatesEarned,
                'newModulesCount' => $newModulesCount,
            ],
            'recentExams' => $recentExams,
        ]);
    }
}