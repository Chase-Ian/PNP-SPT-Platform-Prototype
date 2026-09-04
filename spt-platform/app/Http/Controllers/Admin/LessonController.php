<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index(Course $course)
    {
        return Inertia::render('Admin/Lessons/Index', [
            'course' => $course->only('id', 'title'),
            'lessons' => $course->lessons()->withCount('quizQuestions')->get(),
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'duration_minutes' => 'required|integer|min:1',
        ]);

        $course->lessons()->create([
            'order' => $course->lessons()->max('order') + 1,
            'title' => $request->title,
            'content' => $request->content,
            'duration_minutes' => $request->duration_minutes,
        ]);

        return back();
    }

    public function destroy(Lesson $lesson)
    {
        $lesson->delete();
        return back();
    }

    public function storeQuestion(Request $request, Lesson $lesson)
    {
        $request->validate([
            'question' => 'required|string',
            'choices' => 'required|array|min:2',
            'correct_choice' => 'required|string',
        ]);

        $lesson->quizQuestions()->create($request->only('question', 'choices', 'correct_choice'));

        return back();
    }

    public function destroyQuestion(\App\Models\LessonQuizQuestion $question)
    {
        $question->delete();
        return back();
    }
}