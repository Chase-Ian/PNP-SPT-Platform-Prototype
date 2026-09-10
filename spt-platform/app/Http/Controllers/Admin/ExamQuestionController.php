<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamQuestion;
use App\Models\ExamSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamQuestionController extends Controller
{
    public function index(Course $course)
    {
        $questions = $course->examQuestions;

        return Inertia::render('Admin/ExamQuestions/Index', [
            'course' => $course->only('id', 'title'),
            'questions' => $questions,
            'settings' => [
                'time_limit_minutes' => $course->examSettings?->time_limit_minutes ?? 120,
                'pass_threshold_percent' => $course->examSettings?->pass_threshold_percent ?? 80,
            ],
            'liveQuestionCount' => $questions->count(),
        ]);
    }

    public function updateSettings(Request $request, Course $course)
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
                'question_count' => $course->examQuestions()->count(), // always kept in sync with the real bank
            ]
        );

        return back();
    }

    public function update(Request $request, ExamQuestion $question)
    {
        $request->validate([
            'type' => 'required|in:multiple_choice,true_false,matching,identification',
            'question' => 'required|string',
            'answer_data' => 'required|array',
        ]);

        $question->update([
            'type' => $request->type,
            'question' => $request->question,
            'answer_data' => $request->answer_data,
        ]);

        return back();
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'type' => 'required|in:multiple_choice,true_false,matching,identification',
            'question' => 'required|string',
            'answer_data' => 'required|array',
        ]);

        $course->examQuestions()->create([
            'type' => $request->type,
            'question' => $request->question,
            'answer_data' => $request->answer_data,
        ]);

        // Keep question_count in sync whenever the bank changes
        ExamSetting::where('course_id', $course->id)->update([
            'question_count' => $course->examQuestions()->count(),
        ]);

        return back();
    }

    public function destroy(ExamQuestion $question)
    {
        $courseId = $question->course_id;
        $question->delete();

        ExamSetting::where('course_id', $courseId)->update([
            'question_count' => ExamQuestion::where('course_id', $courseId)->count(),
        ]);

        return back();
    }

    public function preview(Course $course)
    {
        $questions = $course->examQuestions->map(function ($q) {
            $data = ['id' => $q->id, 'type' => $q->type, 'question' => $q->question];

            if ($q->type === 'matching') {
                $data['pairs'] = $q->answer_data['pairs'];
                $data['right_options'] = collect($q->answer_data['pairs'])->pluck('right')->shuffle()->values();
            } else {
                $data['choices'] = $q->answer_data['choices'] ?? [];
            }

            return $data;
        });

        return Inertia::render('Admin/ExamQuestions/Preview', [
            'course' => $course->only('id', 'title'),
            'questions' => $questions,
            'settings' => [
                'time_limit_minutes' => $course->examSettings?->time_limit_minutes ?? 120,
                'pass_threshold_percent' => $course->examSettings?->pass_threshold_percent ?? 80,
            ],
        ]);
    }
}