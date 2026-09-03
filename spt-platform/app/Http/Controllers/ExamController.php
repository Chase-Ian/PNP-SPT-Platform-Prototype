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

        $questions = $course->examQuestions()->get()->map(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'choices' => $q->choices,
        ]);

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
        foreach ($questions as $question) {
            if (($request->answers[$question->id] ?? null) === $question->correct_choice) {
                $correctCount++;
            }
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

        return redirect()->route('exams.result', $attempt->id);
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