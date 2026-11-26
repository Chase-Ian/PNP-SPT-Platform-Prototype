<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Each account has exactly one role: a trainee is only a trainee, a
 * supervisor only a supervisor, an admin only an admin.
 */
class RoleSeparationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['security.require_2fa' => 'none']);
    }

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'role' => $role,
            'unit_office' => 'Test Unit',
            'region' => 'NCRPO - National Capital Region Police Office',
        ]);
    }

    public function test_staff_cannot_use_the_trainee_portal(): void
    {
        $course = Course::create(['activity_code' => 'ROLE-1', 'title' => 'Course', 'is_published' => true]);

        foreach (['admin', 'supervisor'] as $role) {
            $user = $this->makeUser($role);

            // The old "trainee view" link now just goes to their own dashboard.
            $this->actingAs($user)->get('/dashboard?view=trainee')->assertRedirect(route("{$role}.dashboard"));
            $this->actingAs($user)->get('/courses')->assertForbidden();
            $this->actingAs($user)->post("/courses/{$course->id}/enroll")->assertForbidden();
            $this->actingAs($user)->get('/certificates')->assertForbidden();
            $this->actingAs($user)->get("/exams/{$course->id}")->assertForbidden();

            // Pages every account has.
            $this->actingAs($user)->get('/profile')->assertOk();
            $this->actingAs($user)->get('/verify')->assertOk();
        }

        $this->assertSame(0, \App\Models\Enrollment::withoutGlobalScopes()->count());
    }

    public function test_trainees_admins_and_supervisors_keep_to_their_own_portal(): void
    {
        $trainee = $this->makeUser('trainee');
        $supervisor = $this->makeUser('supervisor');
        $admin = $this->makeUser('admin');

        $this->actingAs($trainee)->get('/dashboard')->assertOk();
        $this->actingAs($trainee)->get('/courses')->assertOk();
        $this->actingAs($trainee)->get('/supervisor/dashboard')->assertForbidden();
        $this->actingAs($trainee)->get('/admin/dashboard')->assertForbidden();

        $this->actingAs($supervisor)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($supervisor)->get('/admin/students')->assertForbidden();

        $this->actingAs($admin)->get('/supervisor/dashboard')->assertForbidden();
        $this->actingAs($admin)->get('/supervisor/trainees')->assertForbidden();
    }

    public function test_training_records_of_staff_accounts_do_not_count(): void
    {
        $course = Course::create(['activity_code' => 'ROLE-2', 'title' => 'Course', 'is_published' => true]);
        $trainee = $this->makeUser('trainee');
        $admin = $this->makeUser('admin');

        foreach ([$trainee, $admin] as $i => $user) {
            Certificate::create([
                'user_id' => $user->id, 'course_id' => $course->id,
                'serial_id' => "PNP-SPT-CP-2026-90000{$i}", 'verification_hash' => hash('sha256', "role-{$i}"),
                'issued_at' => now(),
            ]);
        }

        // A certificate left over from the old trainee view is ignored.
        $this->assertSame(1, Certificate::count());
        $this->get('/verify/PNP-SPT-CP-2026-900001')->assertInertia(fn ($page) => $page->where('certificate', null));
        $this->get('/verify/PNP-SPT-CP-2026-900000')->assertInertia(fn ($page) => $page->whereNot('certificate', null));
    }
}
