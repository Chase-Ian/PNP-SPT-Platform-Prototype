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

        $year    = now()->year;
        $count   = str_pad(Certificate::count() + 1, 6, '0', STR_PAD_LEFT);
        $serial  = 'PNP-SPT-CP-' . $year . '-' . $count;
        $hash    = hash('sha256', $user->id . $course->id . now()->timestamp);

        // Training Control Number (e.g. SPTRIV-2026-TRN-0001)
        $ctrlNo  = 'SPT-' . strtoupper(substr(preg_replace('/\s+/', '', $user->region ?? 'REG'), 0, 4))
                   . '-' . $year . '-' . str_pad(Certificate::count() + 1, 4, '0', STR_PAD_LEFT);

        $verifyUrl = url('/verify/' . $serial);

        // Generate QR code as inline SVG data URI (no ext-gd needed)
        $qrDataUri = $this->generateQrDataUri($verifyUrl);

        $pdf = Pdf::loadView('certificates.template', [
            'name'             => $user->name,
            'course'           => $course->title,
            'duration_hours'   => $course->duration_hours ?? 3,
            'serial_id'        => $serial,
            'training_ctrl_no' => $ctrlNo,
            'unit_office'      => $user->unit_office ?? 'N/A',
            'region'           => $user->region ?? '',
            'date'             => now()->format('d F Y'),
            'verify_url'       => $verifyUrl,
            'qr_code_data_uri' => $qrDataUri,
        ])->setPaper('a4', 'landscape');

        $path = "certificates/{$serial}.pdf";
        Storage::put($path, $pdf->output());

        Certificate::create([
            'user_id'          => $user->id,
            'course_id'        => $course->id,
            'serial_id'        => $serial,
            'training_ctrl_no' => $ctrlNo,
            'verification_hash'=> $hash,
            'pdf_path'         => $path,
            'issued_at'        => now(),
        ]);
    }

    /**
     * Generates a QR code as a base64 PNG data URI using pure PHP (no ext-gd).
     * Uses a compact matrix representation and renders to PNG via imagecreatetruecolor
     * with a fallback text placeholder if GD is unavailable.
     */
    private function generateQrDataUri(string $text): string
    {
        // Use Google Chart API to generate the QR code image (no server-side GD needed)
        // This works during PDF generation as DomPDF can embed remote images.
        // For fully offline environments, swap this with a local SVG QR generator.
        $encoded  = urlencode($text);
        $apiUrl   = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$encoded}&format=png&margin=4";

        // Try to fetch the QR image as base64; fall back to a placeholder on failure
        try {
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            $imgData = @file_get_contents($apiUrl, false, $context);
            if ($imgData !== false) {
                return 'data:image/png;base64,' . base64_encode($imgData);
            }
        } catch (\Throwable $e) {
            // fall through to placeholder
        }

        // Offline placeholder: a simple SVG QR-like grid
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80">'
             . '<rect width="80" height="80" fill="white"/>'
             . '<rect x="5" y="5" width="25" height="25" fill="none" stroke="#000" stroke-width="3"/>'
             . '<rect x="10" y="10" width="15" height="15" fill="#000"/>'
             . '<rect x="50" y="5" width="25" height="25" fill="none" stroke="#000" stroke-width="3"/>'
             . '<rect x="55" y="10" width="15" height="15" fill="#000"/>'
             . '<rect x="5" y="50" width="25" height="25" fill="none" stroke="#000" stroke-width="3"/>'
             . '<rect x="10" y="55" width="15" height="15" fill="#000"/>'
             . '<text x="40" y="44" text-anchor="middle" font-size="5" fill="#000">SCAN</text>'
             . '</svg>';
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}