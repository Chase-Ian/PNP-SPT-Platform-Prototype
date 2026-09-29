<?php
// app/Http/Controllers/Supervisor/DashboardController.php
namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $trainees = User::where('role', 'trainee')
            ->with(['enrollments.course', 'certificates', 'examAttempts', 'moduleCompletions'])
            ->get();

        $officers = $trainees->map(function ($t) {
            $total = $t->enrollments->count();
            $completed = $t->enrollments->where('status', 'completed')->count();

            $status = match (true) {
                $total === 0 => 'Not Enrolled',
                $completed === $total => 'Completed',
                $completed > 0 => 'In Progress',
                default => 'Enrolled',
            };

            $latestExam = $t->examAttempts->last();

            return [
                'id' => $t->id,
                'name' => $t->name,
                'unit_office' => $t->unit_office,
                'course_progress' => $total > 0 ? "{$completed}/{$total} Courses" : '—',
                'course_status' => $status,
                'exam_result' => $latestExam ? ($latestExam->passed ? 'Passed (' . $latestExam->score . ')' : 'Failed') : 'Pending',
                'certificate_status' => $t->certificates->isNotEmpty() ? 'Issued' : 'Pending',
            ];
        });

        return Inertia::render('Supervisor/Dashboard', [
            'stats' => [
                'totalTrainees' => $trainees->count(),
                'coursesPublished' => Course::where('is_published', true)->count(),
                'inProgress' => $officers->where('course_status', 'In Progress')->count(),
                'completed' => $officers->where('course_status', 'Completed')->count(),
            ],
            'officers' => $officers->values(),
        ]);
    }
}