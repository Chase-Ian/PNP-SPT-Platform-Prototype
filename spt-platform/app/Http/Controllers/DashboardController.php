<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $enrollments = $user->enrollments()->pluck('status', 'course_id');
        $certificatesEarned = $user->certificates()->count();

        // Lessons count as done when their knowledge-check quiz is passed
        // (the same rule the course pages and the final exam use).
        $passedLessonIds = LessonProgress::where('user_id', $user->id)
            ->where('quiz_passed', true)
            ->pluck('lesson_id')
            ->flip();

        $courses = Course::whereIn('id', $enrollments->keys())->with('lessons')->get();
        $lessons = $courses->flatMap->lessons;

        // A module is complete when it has lessons and every one is passed.
        // (Module completions were never recorded separately, so the old
        // figure was always 0.)
        $completedModules = $lessons->groupBy('module_id')
            ->filter(fn ($moduleLessons) => $moduleLessons->every(fn ($l) => $passedLessonIds->has($l->id)))
            ->count();

        // Learning hours completed = total length of the passed lessons.
        // Time on the page is not tracked, so this is not "time spent".
        $learningHours = round($lessons->filter(fn ($l) => $passedLessonIds->has($l->id))->sum('duration_minutes') / 60, 1);

        $availableCourses = Course::where('is_published', true)
            ->whereNotIn('id', $enrollments->keys())
            ->count();

        $passedExamCourseIds = $user->examAttempts()
            ->whereNull('archived_at')
            ->where('passed', true)
            ->pluck('course_id')
            ->flip();

        // "Continue learning": enrolled (not yet completed) courses.
        $activeCourses = $courses
            ->filter(fn ($course) => ($enrollments[$course->id] ?? null) === 'enrolled')
            ->map(function ($course) use ($passedLessonIds, $passedExamCourseIds) {
                $total = $course->lessons->count();
                $done = $course->lessons->filter(fn ($l) => $passedLessonIds->has($l->id))->count();
                $next = $course->lessons->first(fn ($l) => ! $passedLessonIds->has($l->id));

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'activity_code' => $course->activity_code,
                    'lessons_total' => $total,
                    'lessons_completed' => $done,
                    'progress_percent' => $total > 0 ? (int) round($done / $total * 100) : 0,
                    'next_lesson_id' => $next?->id ?? $course->lessons->first()?->id,
                    'ready_for_exam' => $total > 0 && $done === $total && ! $passedExamCourseIds->has($course->id),
                ];
            })
            ->values();

        $recentExams = $user->examAttempts()
            ->with('course.examQuestions')
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($attempt) => [
                'title' => $attempt->course->title . ' Final Exam',
                'completed_on' => $attempt->created_at->format('Y-m-d'),
                'score_percent' => $attempt->percent(),
                'passed' => $attempt->passed,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'coursesEnrolled' => $enrollments->count(),
                'learningHours' => $learningHours,
                'completedModules' => $completedModules,
                'certificatesEarned' => $certificatesEarned,
                'availableCourses' => $availableCourses,
            ],
            'activeCourses' => $activeCourses,
            'recentExams' => $recentExams,
        ]);
    }
}