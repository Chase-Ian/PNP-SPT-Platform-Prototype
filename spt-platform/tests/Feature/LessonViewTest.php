<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_lesson_page_shows_the_module_position_not_its_database_id(): void
    {
        // A module in another course first, so database IDs and positions differ.
        $other = Course::create(['activity_code' => 'C0', 'title' => 'Other course']);
        Module::create(['course_id' => $other->id, 'title' => 'Unrelated', 'order' => 1]);

        $course = Course::create(['activity_code' => 'C1', 'title' => 'Course One', 'is_published' => true]);
        Module::create(['course_id' => $course->id, 'title' => 'Intro', 'order' => 1]);
        $second = Module::create(['course_id' => $course->id, 'title' => 'Deep dive', 'order' => 2]);
        $lesson = Lesson::create(['course_id' => $course->id, 'module_id' => $second->id, 'title' => 'Lesson 1', 'content' => [], 'order' => 1]);

        $trainee = User::factory()->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)', 'role' => 'trainee',
            'unit_office' => 'Station 1', 'region' => 'NCRPO - National Capital Region Police Office',
        ]);
        Enrollment::create(['user_id' => $trainee->id, 'course_id' => $course->id, 'status' => 'enrolled']);

        $this->assertNotSame(2, $second->id);

        $this->actingAs($trainee)->get("/lessons/{$lesson->id}")->assertOk()
            ->assertInertia(fn ($page) => $page->component('Lessons/Show')
                ->where('module.number', 2)
                ->where('module.count', 2)
                ->where('course.title', 'Course One'));
    }
}
