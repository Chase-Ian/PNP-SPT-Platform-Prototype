<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        $request->validate(['answers' => ['nullable', 'array']]);

        $questions = $lesson->quizQuestions;
        $answers = $request->input('answers') ?? [];

        // Say what's missing instead of failing silently (the page used to do
        // nothing when an answer was missing).
        $unanswered = $questions->filter(fn ($q) => blank($answers[$q->id] ?? null))->count();
        if ($unanswered > 0) {
            throw ValidationException::withMessages([
                'answers' => "Answer all questions before submitting ({$unanswered} left).",
            ]);
        }

        $correctCount = 0;
        $breakdown = [];

        foreach ($questions as $q) {
            $submitted = $answers[$q->id] ?? null;
            $isCorrect = $submitted === $q->correct_choice;

            if ($isCorrect) {
                $correctCount++;
            }

            $breakdown[] = [
                'question' => $q->question,
                'correct' => $isCorrect,
            ];
        }

        // A lesson without quiz questions is passed by confirming the material
        // was reviewed. Otherwise the next lesson and the final exam (which
        // need every lesson quiz passed) could never unlock.
        $total = $questions->count();
        $passed = $total === 0 || round(($correctCount / $total) * 100) >= 70;

        LessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['quiz_score' => $correctCount, 'quiz_passed' => $passed, 'completed_at' => $passed ? now() : null]
        );

        return Inertia::render('Lessons/QuizResult', [
            'lesson' => $lesson->only('id', 'title'),
            'passed' => $passed,
            'score' => $correctCount,
            'total' => $total,
            'breakdown' => $breakdown,
        ]);
}
}