<?php
// app/Http/Controllers/Supervisor/DashboardController.php
namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Services\TrainingStatus;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(TrainingStatus $status)
    {
        $summary = $status->summary($status->rows());

        // The latest final exam submissions that still count (not archived).
        $recent = ExamAttempt::whereNull('archived_at')
            ->whereHas('user', fn ($q) => $q->where('role', 'trainee'))
            ->with(['user:id,first_name,last_name,unit_office', 'course:id,title'])
            ->latest()->limit(8)->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'trainee_id' => $a->user_id,
                'name' => $a->user->name,
                'unit_office' => $a->user->unit_office,
                'course_title' => $a->course->title,
                'result' => $a->passed ? 'Passed' : 'Failed',
                'score' => $a->score,
                'submitted_at' => $a->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Supervisor/Dashboard', [
            'stats' => [
                ...$summary,
                'coursesPublished' => Course::where('is_published', true)->count(),
            ],
            'recent' => $recent,
        ]);
    }
}
