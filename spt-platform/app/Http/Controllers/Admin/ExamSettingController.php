<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamSettingController extends Controller
{
    public function index()
    {
        $courses = Course::with('examSettings')->get()->map(function ($course) {
            $settings = $course->examSettings;

            return [
                'course_id' => $course->id,
                'title' => $course->title,
                'question_count' => $settings->question_count ?? 50,
                'time_limit_minutes' => $settings->time_limit_minutes ?? 120,
                'pass_threshold_percent' => $settings->pass_threshold_percent ?? 80,
                'media_formats' => $course->modules->pluck('file_type')->filter()->unique()->values(),
            ];
        });

        return Inertia::render('Admin/ExamSettings/Index', [
            'courses' => $courses,
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'time_limit_minutes' => 'required|integer|min:15|max:480',
            'pass_threshold_percent' => 'required|integer|min:1|max:100',
        ]);

        ExamSetting::updateOrCreate(
            ['course_id' => $course->id],
            [
                'time_limit_minutes' => $request->time_limit_minutes,
                'pass_threshold_percent' => $request->pass_threshold_percent,
                'question_count' => $course->examSettings->question_count ?? 50,
            ]
        );

        return back();
    }
}