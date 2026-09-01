<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        $trainees = User::where('role', 'trainee')
            ->with(['enrollments.course', 'certificates', 'moduleCompletions'])
            ->get();

        $students = $trainees->map(function ($trainee) {
            $totalEnrollments = $trainee->enrollments->count();
            $completedCourses = $trainee->enrollments->where('status', 'completed')->count();
            $progressPercent = $totalEnrollments > 0
                ? round(($completedCourses / $totalEnrollments) * 100)
                : 0;

            return [
                'id' => $trainee->id,
                'name' => $trainee->name,
                'email' => $trainee->email,
                'organization' => $trainee->unit_office ?? '—',
                'courses' => "{$completedCourses}/{$totalEnrollments}",
                'progress_percent' => $progressPercent,
                'certificates' => $trainee->certificates->count(),
                'status' => 'active', // no deactivation concept yet — hardcoded for now
            ];
        });

        return Inertia::render('Admin/Students/Index', [
            'stats' => [
                'totalStudents' => $trainees->count(),
                'totalEnrollments' => $trainees->sum(fn ($t) => $t->enrollments->count()),
                'coursesCompleted' => $trainees->sum(fn ($t) => $t->enrollments->where('status', 'completed')->count()),
                'avgProgress' => $students->count() > 0 ? round($students->avg('progress_percent')) : 0,
            ],
            'students' => $students->values(),
        ]);
    }

    public function destroy(User $student)
    {
        abort_unless($student->role === 'trainee', 403);
        $student->delete();

        return back();
    }
}