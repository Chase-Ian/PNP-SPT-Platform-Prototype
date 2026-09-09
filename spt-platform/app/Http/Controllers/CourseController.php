<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
// app/Http/Controllers/CourseController.php
  public function index(Request $request)
    {
        $user = $request->user();
        $enrollments = $user->enrollments()->pluck('status', 'course_id');

        $courses = Course::where('is_published', true)
            ->withCount('modules')
            ->with('lessons.progress')
            ->get()
            ->map(function ($course) use ($user, $enrollments) {
                $totalLessons = $course->lessons->count();

                $completedLessons = $course->lessons->filter(function ($lesson) use ($user) {
                    return $lesson->progress->where('user_id', $user->id)->where('quiz_passed', true)->isNotEmpty();
                })->count();

                $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                $allLessonsPassed = $totalLessons > 0 && $completedLessons === $totalLessons;

                $nextLesson = $course->lessons->first(function ($lesson) use ($user) {
                    return $lesson->progress->where('user_id', $user->id)->where('quiz_passed', true)->isEmpty();
                });

                // Has the trainee already passed the final exam for this course?
                $examPassed = $user->examAttempts()->where('course_id', $course->id)->where('passed', true)->exists();

                return [
                    'id' => $course->id,
                    'activity_code' => $course->activity_code,
                    'title' => $course->title,
                    'duration_hours' => $course->duration_hours,
                    'lesson_count' => $totalLessons > 0 ? $totalLessons : $course->modules_count,
                    'status' => $enrollments[$course->id] ?? null,
                    'lessons_total' => $totalLessons,
                    'lessons_completed' => $completedLessons,
                    'progress_percent' => $progressPercent,
                    'launch_lesson_id' => $nextLesson?->id ?? $course->lessons->first()?->id,
                    'all_lessons_passed' => $allLessonsPassed,
                    'exam_passed' => $examPassed,
                ];
            });

        return Inertia::render('Courses/Index', ['courses' => $courses]);
    }

    public function enroll(Request $request, Course $course)
    {
        Enrollment::updateOrCreate(
            ['user_id' => $request->user()->id, 'course_id' => $course->id],
            ['status' => 'enrolled']
        );

        return back();
    }

    public function drop(Request $request, Course $course)
    {
        Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->delete();

        return back();
    }
}