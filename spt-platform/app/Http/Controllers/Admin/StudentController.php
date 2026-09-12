<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use App\Services\AccountLockService;

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

    public function show(User $student)
    {
        abort_unless($student->role === 'trainee', 403);

        $student->load(['enrollments.course', 'certificates.course', 'examAttempts.course', 'moduleCompletions']);

        return Inertia::render('Admin/Students/Show', [
            'student' => $student->only('id', 'first_name', 'last_name', 'rank', 'email', 'unit_office', 'region', 'is_locked'),
            'enrollments' => $student->enrollments->map(fn ($e) => [
                'course_title' => $e->course->title,
                'status' => $e->status,
            ]),
            'certificates' => $student->certificates->map(fn ($c) => [
                'course_title' => $c->course->title,
                'serial_id' => $c->serial_id,
                'issued_at' => $c->issued_at->format('Y-m-d'),
            ]),
            'examAttempts' => $student->examAttempts->map(fn ($a) => [
                'course_title' => $a->course->title,
                'score' => $a->score,
                'passed' => $a->passed,
                'date' => $a->created_at->format('Y-m-d'),
            ]),
        ]);
    }

    public function toggleLock(User $student, AccountLockService $lockService)
    {
        abort_unless($student->role === 'trainee', 403);

        $lockService->toggle($student);

        return back();
    }
}