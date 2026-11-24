<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Services\TrainingStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Monitor Trainees: the supervisor's view of every trainee's course status
 * (Not submitted / Submitted / Archived), built from saved records only.
 */
class MonitoringController extends Controller
{
    public function __construct(private TrainingStatus $status) {}

    public function index(): Response
    {
        // No supervisor-to-trainee assignment exists yet, so this shows all
        // trainees. If assignments are added, scope TrainingStatus::rows().
        $rows = $this->status->rows();

        return Inertia::render('Supervisor/Trainees/Index', [
            'rows' => $rows,
            'stats' => $this->status->summary($rows),
            'courses' => Course::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function show(User $student): Response
    {
        abort_unless($student->role === 'trainee', 404);

        $student->load(['certificates.course', 'examAttempts.course']);

        return Inertia::render('Supervisor/Trainees/Show', [
            'student' => $student->only('id', 'first_name', 'last_name', 'rank', 'email', 'unit_office', 'region', 'is_locked'),
            'courses' => $this->status->rows($student)
                ->where('status', '!=', TrainingStatus::NOT_ENROLLED)->values(),
            'certificates' => $student->certificates->sortByDesc('issued_at')->values()->map(fn ($c) => [
                'course_title' => $c->course->title,
                'serial_id' => $c->serial_id,
                'issued_at' => $c->issued_at->format('Y-m-d'),
                'archived' => $c->archived_at !== null,
            ]),
            'examAttempts' => $student->examAttempts->sortByDesc('created_at')->values()->map(fn ($a) => [
                'id' => $a->id,
                'course_title' => $a->course->title,
                'score' => $a->score,
                'passed' => $a->passed,
                'date' => $a->created_at->format('Y-m-d'),
                'archived' => $a->archived_at !== null,
            ]),
        ]);
    }

    /** After a major course change: everyone redoes the course and takes the new final exam. */
    public function archive(Request $request, Course $course): RedirectResponse
    {
        $archived = $this->status->archiveCourse($course, $request->user());

        return back()->with('status', "course-results-archived:{$archived}");
    }
}
