<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\LessonProgress;
use App\Models\ModuleCompletion;
use App\Models\User;
use App\Notifications\CourseResultsArchived;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Trainee course statuses for supervisors. They come only from saved records
 * (final exam submissions and archives), never from live lesson-by-lesson
 * progress, so monitoring doesn't turn into a real-time report:
 *
 *   Not enrolled  - the trainee hasn't enrolled in any course yet
 *   Not submitted - enrolled; no final exam submitted for the current course version
 *   Submitted     - final exam submitted (passed or failed)
 *   Archived      - a supervisor archived the results after a major course
 *                   change; the trainee has to redo the course and take the
 *                   new final exam
 *
 * Building the rows takes the same small number of queries however many
 * trainees there are.
 */
class TrainingStatus
{
    public const NOT_ENROLLED = 'Not enrolled';
    public const NOT_SUBMITTED = 'Not submitted';
    public const SUBMITTED = 'Submitted';
    public const ARCHIVED = 'Archived';

    /** One row per trainee per course, plus one row for each trainee with no course. */
    public function rows(?User $only = null): Collection
    {
        $trainees = User::where('role', 'trainee')
            ->when($only, fn ($q) => $q->whereKey($only->getKey()))
            ->with(['enrollments' => fn ($q) => $q->where('status', '!=', 'dropped')->with('course:id,title')])
            ->orderBy('last_name')->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'rank', 'unit_office', 'region']);

        $attempts = ExamAttempt::query()
            ->when($only, fn ($q) => $q->where('user_id', $only->getKey()))
            ->orderBy('created_at')->orderBy('id')
            ->get(['id', 'user_id', 'course_id', 'score', 'total_questions', 'passed', 'archived_at', 'created_at'])
            ->groupBy(fn ($a) => $a->user_id.'-'.$a->course_id);

        $certified = Certificate::query()
            ->when($only, fn ($q) => $q->where('user_id', $only->getKey()))
            ->whereNull('archived_at')
            ->get(['user_id', 'course_id'])
            ->mapWithKeys(fn ($c) => [$c->user_id.'-'.$c->course_id => true]);

        return $trainees->flatMap(function (User $trainee) use ($attempts, $certified) {
            $base = [
                'trainee_id' => $trainee->id,
                'name' => $trainee->name,
                'rank' => $trainee->rank,
                'unit_office' => $trainee->unit_office,
                'region' => $trainee->region,
            ];

            if ($trainee->enrollments->isEmpty()) {
                return [[
                    ...$base,
                    'course_id' => null, 'course_title' => null, 'status' => self::NOT_ENROLLED,
                    'result' => null, 'score' => null, 'score_total' => null, 'submitted_at' => null, 'certificate' => false,
                ]];
            }

            return $trainee->enrollments->map(function ($enrollment) use ($base, $trainee, $attempts, $certified) {
                $key = $trainee->id.'-'.$enrollment->course_id;
                $forCourse = $attempts->get($key, collect());
                $current = $forCourse->whereNull('archived_at')->last();

                return [
                    ...$base,
                    'course_id' => $enrollment->course_id,
                    'course_title' => $enrollment->course?->title,
                    'status' => match (true) {
                        $current !== null => self::SUBMITTED,
                        $forCourse->isNotEmpty() => self::ARCHIVED,
                        default => self::NOT_SUBMITTED,
                    },
                    'result' => $current ? ($current->passed ? 'Passed' : 'Failed') : null,
                    'score' => $current?->score,
                    // Questions in the exam when it was taken (null for attempts saved before this was recorded).
                    'score_total' => $current?->total_questions,
                    'submitted_at' => $current?->created_at?->format('Y-m-d'),
                    'certificate' => $certified->has($key),
                ];
            })->all();
        })->values();
    }

    public function summary(Collection $rows): array
    {
        return [
            'totalTrainees' => $rows->pluck('trainee_id')->unique()->count(),
            'submitted' => $rows->where('status', self::SUBMITTED)->count(),
            'notSubmitted' => $rows->where('status', self::NOT_SUBMITTED)->count(),
            'archived' => $rows->where('status', self::ARCHIVED)->count(),
            'notEnrolled' => $rows->where('status', self::NOT_ENROLLED)->count(),
        ];
    }

    /**
     * After a major course change: archive everyone's current results so they
     * redo the course and take the new final exam. Exam attempts and
     * certificates are kept as history; lesson results are cleared, because
     * the final exam only opens once every lesson quiz is passed again.
     * Returns how many submissions were archived.
     */
    public function archiveCourse(Course $course, User $by): int
    {
        [$archived, $trainees] = DB::transaction(function () use ($course) {
            $now = now();

            // Count what supervisors can see, but archive every record (also
            // deleted accounts'), so a reinstated account has no stale results.
            $archived = ExamAttempt::where('course_id', $course->id)->whereNull('archived_at')->count();
            ExamAttempt::withoutGlobalScope('activeTrainee')->where('course_id', $course->id)->whereNull('archived_at')
                ->update(['archived_at' => $now]);
            Certificate::withoutGlobalScope('activeTrainee')->where('course_id', $course->id)->whereNull('archived_at')
                ->update(['archived_at' => $now]);
            Enrollment::withoutGlobalScope('activeTrainee')->where('course_id', $course->id)->where('status', 'completed')
                ->update(['status' => 'enrolled']);

            LessonProgress::whereIn('lesson_id', $course->lessons()->pluck('id'))->delete();
            ModuleCompletion::whereIn('module_id', $course->modules()->pluck('id'))->delete();

            $trainees = User::whereIn('id', Enrollment::where('course_id', $course->id)
                ->where('status', '!=', 'dropped')->pluck('user_id'))->get();

            return [$archived, $trainees];
        });

        Notification::send($trainees, new CourseResultsArchived($course));

        Log::info('Course results archived', [
            'course_id' => $course->id,
            'supervisor_id' => $by->id,
            'submissions_archived' => $archived,
            'trainees_notified' => $trainees->count(),
        ]);

        return $archived;
    }
}
