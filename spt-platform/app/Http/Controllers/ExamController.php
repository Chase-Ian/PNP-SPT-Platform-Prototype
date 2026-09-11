<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ExamController extends Controller
{
    // app/Http/Controllers/ExamController.php

  public function show(Request $request, Course $course)
    {
        $user = $request->user();

        $alreadyEnrolled = $user->enrollments()->where('course_id', $course->id)->exists();
        abort_unless($alreadyEnrolled, 403, 'You must enroll in this course before taking the exam.');

        $totalLessons = $course->lessons()->count();
        if ($totalLessons > 0) {
            $passedLessons = $course->lessons()
                ->whereHas('progress', fn ($q) => $q->where('user_id', $user->id)->where('quiz_passed', true))
                ->count();

            abort_unless($passedLessons === $totalLessons, 403, 'Complete all lessons before taking the final exam.');
        }

        $questions = $course->examQuestions()->get()->map(fn ($q) => [
            'id' => $q->id,
            'type' => $q->type,
            'question' => $q->question,
            'choices' => $q->answer_data['choices'] ?? [],
            'pairs' => $q->answer_data['pairs'] ?? [], // only 'left' side shown to trainee; 'right' options extracted below
        ]);

        // For matching questions, trainees pick from a shuffled pool of right-side answers
        $questions = $questions->map(function ($q) {
            if ($q['type'] === 'matching') {
                $q['right_options'] = collect($q['pairs'])->pluck('right')->shuffle()->values();
            }
            return $q;
        });

        $settings = $course->examSettings;

        return Inertia::render('Exam/Show', [
            'course' => ['id' => $course->id, 'title' => $course->title],
            'questions' => $questions,
            'time_limit_minutes' => $settings?->time_limit_minutes ?? 120,
            'pass_threshold_percent' => $settings?->pass_threshold_percent ?? 80,
        ]);
    }

   public function submit(Request $request, Course $course)
    {
        $request->validate(['answers' => 'required|array']);

        $user = $request->user();
        $questions = $course->examQuestions;

        $correctCount = 0;
        $breakdown = [];

        foreach ($questions as $question) {
            $submitted = $request->answers[$question->id] ?? null;

            $isCorrect = match ($question->type) {
                'multiple_choice', 'true_false' => $submitted === $question->answer_data['correct_choice'],
                'identification' => is_string($submitted) && strtolower(trim($submitted)) === strtolower(trim($question->answer_data['correct_answer'])),
                'matching' => $this->gradeMatching($submitted, $question->answer_data['pairs']),
                default => false,
            };

            if ($isCorrect) {
                $correctCount++;
            }

            $breakdown[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'type' => $question->type,
                'correct' => $isCorrect,
                // Deliberately NOT including the correct answer here
            ];
        }

        $totalQuestions = max($questions->count(), 1);
        $percent = round(($correctCount / $totalQuestions) * 100);
        $passThreshold = $course->examSettings?->pass_threshold_percent ?? 80;
        $passed = $percent >= $passThreshold;

        $attempt = ExamAttempt::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'score' => $correctCount,
            'passed' => $passed,
            'answers' => $request->answers,
        ]);

        if ($passed) {
            $user->enrollments()->where('course_id', $course->id)->update(['status' => 'completed']);
            $this->issueCertificate($user, $course);
        }

        return Inertia::render('Exam/Result', [
            'attempt' => [
                'score' => $attempt->score,
                'total' => $totalQuestions,
                'percent' => $percent,
                'passed' => $attempt->passed,
                'course_title' => $course->title,
            ],
            'breakdown' => $breakdown,
        ]);
    }

    private function gradeMatching($submitted, array $correctPairs): bool
    {
        if (! is_array($submitted)) {
            return false;
        }

        foreach ($correctPairs as $pair) {
            if (($submitted[$pair['left']] ?? null) !== $pair['right']) {
                return false; // every pair must be correct — no partial credit
            }
        }

        return true;
    }

    public function result(Request $request, ExamAttempt $attempt)
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);

        return Inertia::render('Exam/Result', [
            'attempt' => [
                'score' => $attempt->score,
                'passed' => $attempt->passed,
                'course_title' => $attempt->course->title,
            ],
        ]);
    }

    private function issueCertificate($user, $course)
    {
        $existing = Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($existing) {
            return;
        }

        $serial = 'PNP-' . now()->year . '-' . str_pad(Certificate::count() + 1, 6, '0', STR_PAD_LEFT);
        $hash = hash('sha256', $user->id . $course->id . now());

        $pdf = Pdf::loadView('certificates.template', [
            'name' => $user->name,
            'course' => $course->title,
            'serial' => $serial,
            'date' => now()->format('F j, Y'),
        ]);

        $path = "certificates/{$serial}.pdf";
        Storage::put($path, $pdf->output());

        Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'serial_id' => $serial,
            'verification_hash' => $hash,
            'pdf_path' => $path,
            'issued_at' => now(),
        ]);
    }
}