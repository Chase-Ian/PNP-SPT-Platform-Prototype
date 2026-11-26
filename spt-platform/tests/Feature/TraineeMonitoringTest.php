<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ExamAttempt;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Module;
use App\Models\ModuleCompletion;
use App\Models\User;
use App\Notifications\CourseResultsArchived;
use App\Services\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class TraineeMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'trainee'): User
    {
        return User::factory()->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'role' => $role,
            'unit_office' => 'Station 1',
            'region' => 'NCRPO - National Capital Region Police Office',
        ]);
    }

    private function course(string $code): Course
    {
        return Course::create(['activity_code' => $code, 'title' => "Course {$code}", 'is_published' => true]);
    }

    private function enroll(User $user, Course $course, string $status = 'enrolled'): void
    {
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'status' => $status]);
    }

    private function attempt(User $user, Course $course, bool $passed = true, bool $archived = false): ExamAttempt
    {
        return ExamAttempt::create([
            'user_id' => $user->id, 'course_id' => $course->id, 'score' => $passed ? 9 : 3,
            'passed' => $passed, 'answers' => [], 'archived_at' => $archived ? now() : null,
        ]);
    }

    private function rowFor(array|\Illuminate\Support\Collection $rows, User $user): array
    {
        return collect($rows)->firstWhere('trainee_id', $user->id);
    }

    public function test_statuses_come_from_saved_records(): void
    {
        $course = $this->course('A');
        $notSubmitted = $this->makeUser();
        $submitted = $this->makeUser();
        $archived = $this->makeUser();
        $notEnrolled = $this->makeUser();

        $this->enroll($notSubmitted, $course);
        $this->enroll($submitted, $course, 'completed');
        $this->attempt($submitted, $course);
        $this->enroll($archived, $course);
        $this->attempt($archived, $course, archived: true);

        $this->actingAs($this->makeUser('supervisor'))->get('/supervisor/trainees')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Supervisor/Trainees/Index')
                ->where('stats.totalTrainees', 4)
                ->where('stats.submitted', 1)
                ->where('stats.notSubmitted', 1)
                ->where('stats.archived', 1)
                ->where('rows', function ($rows) use ($notSubmitted, $submitted, $archived, $notEnrolled) {
                    $this->assertSame(TrainingStatus::NOT_SUBMITTED, $this->rowFor($rows, $notSubmitted)['status']);
                    $this->assertSame(TrainingStatus::SUBMITTED, $this->rowFor($rows, $submitted)['status']);
                    $this->assertSame('Passed', $this->rowFor($rows, $submitted)['result']);
                    $this->assertSame(TrainingStatus::ARCHIVED, $this->rowFor($rows, $archived)['status']);
                    $this->assertSame(TrainingStatus::NOT_ENROLLED, $this->rowFor($rows, $notEnrolled)['status']);

                    return true;
                }));
    }

    public function test_only_supervisors_monitor_trainees_and_see_analytics(): void
    {
        $course = $this->course('A');
        $trainee = $this->makeUser();
        $admin = $this->makeUser('admin');
        $supervisor = $this->makeUser('supervisor');

        foreach ([$admin, $trainee] as $user) {
            $this->actingAs($user)->get('/supervisor/trainees')->assertForbidden();
            $this->actingAs($user)->get("/supervisor/trainees/{$trainee->id}")->assertForbidden();
            $this->actingAs($user)->post("/supervisor/courses/{$course->id}/archive-results")->assertForbidden();
            $this->actingAs($user)->get('/supervisor/analytics')->assertForbidden();
        }

        $this->actingAs($supervisor)->get("/supervisor/trainees/{$trainee->id}")->assertOk()
            ->assertInertia(fn ($page) => $page->component('Supervisor/Trainees/Show'));
        $this->actingAs($supervisor)->get('/supervisor/analytics')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Supervisor/Analytics'));

        // Moved off the admin side.
        $this->actingAs($admin)->get('/admin/analytics')->assertNotFound();
        $this->actingAs($admin)->get("/admin/students/{$trainee->id}")->assertStatus(405);
    }

    public function test_admins_see_accounts_not_training_results(): void
    {
        $admin = $this->makeUser('admin');
        $trainee = $this->makeUser();

        $this->actingAs($admin)->get('/admin/students')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Students/Index')
                ->where('accounts.0.id', $trainee->id)
                ->has('accounts.0.two_factor_enabled')
                ->has('accounts.0.is_locked')
                ->missing('accounts.0.progress_percent')
                ->missing('accounts.0.courses'));

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Dashboard')->missing('officers'));

        $this->actingAs($this->makeUser('supervisor'))->get('/admin/students')->assertForbidden();
    }

    public function test_archiving_a_course_makes_trainees_redo_it(): void
    {
        Notification::fake();
        $course = $this->course('A');
        $other = $this->course('B');
        $module = Module::create(['course_id' => $course->id, 'title' => 'Module 1']);
        $lesson = Lesson::create(['course_id' => $course->id, 'module_id' => $module->id, 'title' => 'Lesson 1', 'content' => []]);
        $otherLesson = Lesson::create(['course_id' => $other->id, 'title' => 'Other lesson', 'content' => []]);

        $passed = $this->makeUser();
        $this->enroll($passed, $course, 'completed');
        $attempt = $this->attempt($passed, $course);
        $certificate = Certificate::create([
            'user_id' => $passed->id, 'course_id' => $course->id, 'serial_id' => 'PNP-SPT-CP-2026-000001',
            'verification_hash' => hash('sha256', Str::random()), 'issued_at' => now(),
        ]);
        LessonProgress::create(['user_id' => $passed->id, 'lesson_id' => $lesson->id, 'quiz_passed' => true, 'completed_at' => now()]);
        LessonProgress::create(['user_id' => $passed->id, 'lesson_id' => $otherLesson->id, 'quiz_passed' => true, 'completed_at' => now()]);
        ModuleCompletion::create(['user_id' => $passed->id, 'module_id' => $module->id, 'completed_at' => now()]);

        $midway = $this->makeUser();
        $this->enroll($midway, $course);
        LessonProgress::create(['user_id' => $midway->id, 'lesson_id' => $lesson->id, 'quiz_passed' => true, 'completed_at' => now()]);

        $this->actingAs($this->makeUser('supervisor'))
            ->post("/supervisor/courses/{$course->id}/archive-results")
            ->assertRedirect()
            ->assertSessionHas('status', 'course-results-archived:1');

        // History kept, but no longer current.
        $this->assertNotNull($attempt->fresh()->archived_at);
        $this->assertNotNull($certificate->fresh()->archived_at);
        $this->assertSame('enrolled', Enrollment::where('user_id', $passed->id)->value('status'));

        // Lesson results for this course are cleared; other courses are untouched.
        $this->assertSame(0, LessonProgress::where('lesson_id', $lesson->id)->count());
        $this->assertSame(1, LessonProgress::where('lesson_id', $otherLesson->id)->count());
        $this->assertSame(0, ModuleCompletion::where('module_id', $module->id)->count());

        Notification::assertSentTo([$passed, $midway], CourseResultsArchived::class);

        $rows = app(TrainingStatus::class)->rows();
        $this->assertSame(TrainingStatus::ARCHIVED, $this->rowFor($rows, $passed)['status']);
        $this->assertSame(TrainingStatus::NOT_SUBMITTED, $this->rowFor($rows, $midway)['status']);

        // The new final exam stays locked until the lessons are redone.
        $this->actingAs($passed)->get("/exams/{$course->id}")->assertForbidden();
    }

    public function test_supervisor_dashboard_shows_recent_submissions(): void
    {
        $course = $this->course('A');
        $trainee = $this->makeUser();
        $this->enroll($trainee, $course, 'completed');
        $this->attempt($trainee, $course);
        $this->attempt($this->makeUser(), $course, archived: true);

        $this->actingAs($this->makeUser('supervisor'))->get('/supervisor/dashboard')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Supervisor/Dashboard')
                ->has('recent', 1)
                ->where('recent.0.trainee_id', $trainee->id)
                ->where('stats.submitted', 1)
                ->missing('stats.inProgress'));
    }
}
