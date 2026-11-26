<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonQuizQuestion;
use App\Models\Module;
use App\Models\User;
use App\Notifications\CourseResultsArchived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TraineeScreensTest extends TestCase
{
    use RefreshDatabase;

    private function trainee(): User
    {
        return User::factory()->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)', 'role' => 'trainee',
            'unit_office' => 'Station 1', 'region' => 'NCRPO - National Capital Region Police Office',
        ]);
    }

    private function lesson(): Lesson
    {
        $course = Course::create(['activity_code' => 'C1', 'title' => 'Course One', 'is_published' => true]);
        $module = Module::create(['course_id' => $course->id, 'title' => 'Module 1', 'order' => 1]);

        return Lesson::create(['course_id' => $course->id, 'module_id' => $module->id, 'title' => 'Part 1', 'content' => [], 'order' => 1]);
    }

    // ---------- lesson quiz ----------

    public function test_a_lesson_without_quiz_questions_can_be_marked_as_reviewed(): void
    {
        $user = $this->trainee();
        $lesson = $this->lesson();

        $this->actingAs($user)->post("/lessons/{$lesson->id}/quiz", ['answers' => []])
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Lessons/QuizResult')->where('passed', true)->where('total', 0));

        $this->assertTrue((bool) LessonProgress::where('user_id', $user->id)->where('lesson_id', $lesson->id)->value('quiz_passed'));
    }

    public function test_unanswered_questions_are_reported_instead_of_failing_silently(): void
    {
        $user = $this->trainee();
        $lesson = $this->lesson();
        $q1 = LessonQuizQuestion::create(['lesson_id' => $lesson->id, 'question' => 'One?', 'choices' => ['A', 'B'], 'correct_choice' => 'A']);
        LessonQuizQuestion::create(['lesson_id' => $lesson->id, 'question' => 'Two?', 'choices' => ['A', 'B'], 'correct_choice' => 'B']);

        $this->actingAs($user)->post("/lessons/{$lesson->id}/quiz", ['answers' => []])
            ->assertSessionHasErrors(['answers' => 'Answer all questions before submitting (2 left).']);
        $this->actingAs($user)->post("/lessons/{$lesson->id}/quiz", ['answers' => [$q1->id => 'A']])
            ->assertSessionHasErrors(['answers' => 'Answer all questions before submitting (1 left).']);

        $this->assertSame(0, LessonProgress::count());
    }

    public function test_a_fully_answered_quiz_is_graded(): void
    {
        $user = $this->trainee();
        $lesson = $this->lesson();
        $q1 = LessonQuizQuestion::create(['lesson_id' => $lesson->id, 'question' => 'One?', 'choices' => ['A', 'B'], 'correct_choice' => 'A']);
        $q2 = LessonQuizQuestion::create(['lesson_id' => $lesson->id, 'question' => 'Two?', 'choices' => ['A', 'B'], 'correct_choice' => 'B']);

        $this->actingAs($user)->post("/lessons/{$lesson->id}/quiz", ['answers' => [$q1->id => 'A', $q2->id => 'B']])
            ->assertInertia(fn ($page) => $page->where('passed', true)->where('score', 2)->where('total', 2));
    }

    // ---------- final exam grades ----------

    public function test_exam_grades_use_the_question_count_at_the_time_of_the_attempt(): void
    {
        \Illuminate\Support\Facades\Storage::fake();
        $user = $this->trainee();
        $course = Course::create(['activity_code' => 'E1', 'title' => 'Exam course', 'is_published' => true]);
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'status' => 'enrolled']);
        $questions = collect(['A', 'B', 'C', 'D'])->map(fn ($correct, $i) => \App\Models\ExamQuestion::create([
            'course_id' => $course->id, 'type' => 'multiple_choice', 'question' => "Question {$i}",
            'answer_data' => ['choices' => ['A', 'B', 'C', 'D'], 'correct_choice' => $correct],
        ]));

        // 3 of 4 correct.
        $this->actingAs($user)->post("/exams/{$course->id}", ['answers' => [
            $questions[0]->id => 'A', $questions[1]->id => 'B', $questions[2]->id => 'C', $questions[3]->id => 'A',
        ]])->assertOk();

        $attempt = \App\Models\ExamAttempt::firstOrFail();
        $this->assertSame(4, $attempt->total_questions);
        $this->assertSame(3, $attempt->score);

        // A supervisor later removes two questions: the old grade must not change.
        $questions[2]->delete();
        $questions[3]->delete();

        $this->actingAs($user)->get("/exams/result/{$attempt->id}")
            ->assertInertia(fn ($page) => $page->where('attempt.total', 4)->where('attempt.percent', 75));
        $this->actingAs($user)->get('/dashboard')
            ->assertInertia(fn ($page) => $page->where('recentExams.0.score_percent', 75));
    }

    // ---------- notifications ----------

    public function test_notifications_link_to_the_page_they_are_about(): void
    {
        $user = $this->trainee();
        $course = Course::create(['activity_code' => 'C9', 'title' => 'Updated course']);
        $user->notify(new CourseResultsArchived($course));

        // An older notification with only a message.
        DB::table('notifications')->insert([
            'id' => Str::uuid(), 'type' => 'App\\Notifications\\Generic', 'notifiable_type' => User::class,
            'notifiable_id' => $user->id, 'data' => json_encode(['message' => 'You have completed Police Ethics and Conduct']),
            'created_at' => now()->subHour(), 'updated_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($user)->getJson('/notifications')->assertOk();
        // Results archived: "My learning", where the course to redo is listed.
        $this->assertSame('/courses?tab=mine', $response->json('0.url'));
        $this->assertSame('/certificates', $response->json('1.url'));

        $id = $response->json('0.id');
        $this->actingAs($user)->postJson("/notifications/{$id}/read")->assertNoContent();
        $this->assertNotNull($user->notifications()->find($id)->read_at);
    }
}
