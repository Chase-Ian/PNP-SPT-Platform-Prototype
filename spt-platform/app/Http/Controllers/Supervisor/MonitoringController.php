<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    public function index()
    {
        // No supervisor-to-trainee assignment concept exists yet, so this
        // shows all trainees for now. If assignments get added later,
        // scope this query to only trainees under the logged-in supervisor.
        $trainees = User::where('role', 'trainee')
            ->with(['enrollments.course', 'certificates', 'examAttempts', 'moduleCompletions'])
            ->get()
            ->map(function ($trainee) {
                $totalEnrollments = $trainee->enrollments->count();
                $completedCount = $trainee->enrollments->where('status', 'completed')->count();

                $courseStatus = match (true) {
                    $totalEnrollments === 0 => 'Not Enrolled',
                    $completedCount === $totalEnrollments => 'Completed',
                    $completedCount > 0 => 'In Progress',
                    default => 'Enrolled',
                };

                $latestExam = $trainee->examAttempts->last();

                return [
                    'name' => $trainee->name,
                    'unit_office' => $trainee->unit_office,
                    'course_progress' => $totalEnrollments > 0 ? "{$completedCount}/{$totalEnrollments} Courses" : '—',
                    'course_status' => $courseStatus,
                    'modules_completed' => $trainee->moduleCompletions->whereNotNull('completed_at')->count(),
                    'exam_result' => $latestExam ? ($latestExam->passed ? 'Passed (' . $latestExam->score . ')' : 'Failed') : 'Pending',
                    'certificate_status' => $trainee->certificates->isNotEmpty() ? 'Issued' : 'Pending',
                ];
            });

        return Inertia::render('Supervisor/Monitoring', [
            'stats' => [
                'totalTrainees' => $trainees->count(),
                'inProgress' => $trainees->where('course_status', 'In Progress')->count(),
                'completed' => $trainees->where('course_status', 'Completed')->count(),
            ],
            'trainees' => $trainees->values(),
        ]);
    }
}