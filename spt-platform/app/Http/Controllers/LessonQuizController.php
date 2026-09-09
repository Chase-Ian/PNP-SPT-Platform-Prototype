<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonQuizController extends Controller
{
    public function show(Request $request, Lesson $lesson)
    {
        $questions = $lesson->quizQuestions()->get()->map(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'choices' => $q->choices,
        ]);

        return Inertia::render('Lessons/Quiz', [
            'lesson' => $lesson->only('id', 'title'),
            'questions' => $questions,
        ]);
    }

    public function submit(Request $request, Lesson $lesson)
    {
        $request->validate(['answers' => 'required|array']);

        $questions = $lesson->quizQuestions;
        $correctCount = 0;

        foreach ($questions as $q) {
            if (($request->answers[$q->id] ?? null) === $q->correct_choice) {
                $correctCount++;
            }
        }

        $total = max($questions->count(), 1);
        $percent = round(($correctCount / $total) * 100);
        $passed = $percent >= 70;

        LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['quiz_score' => $correctCount, 'quiz_passed' => $passed, 'completed_at' => $passed ? now() : null]
        );

        return Inertia::render('Lessons/QuizResult', [
            'lesson' => $lesson->only('id', 'title'),
            'passed' => $passed,
            'score' => $correctCount,
            'total' => $total,
        ]);
    }
}