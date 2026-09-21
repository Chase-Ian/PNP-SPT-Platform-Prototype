<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::withCount('modules')->latest()->get();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'activity_code' => 'required|string|max:50|unique:courses,activity_code',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'duration_hours' => 'required|integer|min:1',
            'lesson_count' => 'required|integer|min:1',
        ]);

        Course::create([
            'activity_code' => $request->activity_code,
            'title' => $request->title,
            'description' => $request->description,
            'instructor_name' => $request->instructor_name,
            'duration_hours' => $request->duration_hours,
            'lesson_count' => $request->lesson_count,
            'is_published' => true,
        ]);

        return back();
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'activity_code' => 'required|string|max:50|unique:courses,activity_code,' . $course->id,
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'duration_hours' => 'required|integer|min:1',
            'lesson_count' => 'required|integer|min:1',
        ]);

        $course->update($request->only(
            'activity_code', 'title', 'description', 'instructor_name', 'duration_hours', 'lesson_count'
        ));

        return back();
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return back();
    }
}