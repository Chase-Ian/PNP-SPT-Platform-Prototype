<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $enrollments = $user->enrollments()->pluck('status', 'course_id');

        $courses = Course::where('is_published', true)
            ->withCount('modules')
            ->get()
            ->map(fn ($course) => [
                'id' => $course->id,
                'activity_code' => $course->activity_code,
                'title' => $course->title,
                'duration_hours' => $course->duration_hours,
                'lesson_count' => $course->modules_count,
                'status' => $enrollments[$course->id] ?? null, // 'enrolled' | 'dropped' | 'completed' | null
            ]);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
        ]);
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
            ->update(['status' => 'dropped']);

        return back();
    }
}