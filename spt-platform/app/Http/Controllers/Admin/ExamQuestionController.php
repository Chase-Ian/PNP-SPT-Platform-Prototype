<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamQuestionController extends Controller
{
    public function index(Course $course)
    {
        return Inertia::render('Admin/ExamQuestions/Index', [
            'course' => $course->only('id', 'title'),
            'questions' => $course->examQuestions,
        ]);
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

        return back();
    }

    public function destroy(ExamQuestion $question)
    {
        $question->delete();
        return back();
    }
}