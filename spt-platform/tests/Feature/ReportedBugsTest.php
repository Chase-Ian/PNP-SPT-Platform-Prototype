<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Notifications\CustomResetPassword;
use App\Notifications\TwoFactorWasReset;
use App\Support\UploadLimits;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Reported bugs: upload errors, password rules, the reset email, and where
 * notifications go.
 */
class ReportedBugsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['security.require_2fa' => 'none']);
    }

    private function makeUser(string $role = 'trainee', array $attrs = []): User
    {
        return User::factory()->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'role' => $role,
            'unit_office' => 'Test Unit',
            'region' => 'NCRPO - National Capital Region Police Office',
            ...$attrs,
        ]);
    }

    // ---------- 1. uploads ----------

    public function test_a_module_file_of_the_wrong_type_gets_a_clear_message(): void
    {
        Storage::fake();
        $course = Course::create(['activity_code' => 'UP-1', 'title' => 'Course']);

        $this->actingAs($this->makeUser('admin'))->from("/admin/courses/{$course->id}/modules")
            ->post("/admin/courses/{$course->id}/modules", [
                'title' => 'Module', 'duration_minutes' => 20,
                'file' => UploadedFile::fake()->create('notes.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ])
            ->assertSessionHasErrors(['file' => 'Only PDF or MP4 files can be uploaded.']);
    }

    public function test_a_file_over_the_server_limit_goes_back_to_the_form_with_a_message(): void
    {
        $course = Course::create(['activity_code' => 'UP-2', 'title' => 'Course']);
        $page = "/admin/courses/{$course->id}/modules";

        // PHP drops a body this large, so it never reaches the app's own checks.
        $this->actingAs($this->makeUser('admin'))->from($page)
            ->call('POST', $page, [], [], [], ['CONTENT_LENGTH' => 1024 ** 3, 'HTTP_X_INERTIA' => 'true', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertRedirect($page)
            ->assertSessionHasErrors(['file' => UploadLimits::tooLargeMessage()]);
    }

    public function test_the_lesson_editor_gets_a_message_for_a_file_over_the_server_limit(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->call('POST', '/admin/lessons/upload-image', [], [], [], ['CONTENT_LENGTH' => 1024 ** 3, 'HTTP_ACCEPT' => 'application/json', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'])
            ->assertStatus(413)
            ->assertJsonPath('message', UploadLimits::tooLargeMessage());
    }

    public function test_upload_pages_get_the_real_limits(): void
    {
        $course = Course::create(['activity_code' => 'UP-3', 'title' => 'Course']);

        $this->actingAs($this->makeUser('admin'))->get("/admin/courses/{$course->id}/modules")
            ->assertInertia(fn ($page) => $page->where('maxFileMb', UploadLimits::mb(51200)));
    }

    // ---------- 2. passwords ----------

    public function test_new_passwords_need_mixed_case_a_number_and_a_symbol(): void
    {
        $register = fn (string $password) => $this->post('/register', [
            'first_name' => 'Rosa', 'last_name' => 'Lim', 'rank' => 'Patrolman/Patrolwoman (Pat)',
            'email' => 'rosa.lim@pnp.gov.ph', 'unit_office' => 'Unit', 'region' => 'NCRPO - National Capital Region Police Office',
            'password' => $password, 'password_confirmation' => $password, 'terms' => true,
        ]);

        foreach (['alllowercase1!', 'ALLUPPERCASE1!', 'No-Numbers-Here', 'NoSymbols123', 'Sh0rt!'] as $weak) {
            $register($weak)->assertSessionHasErrors('password');
        }
        $this->assertDatabaseMissing('users', ['email' => 'rosa.lim@pnp.gov.ph']);

        $register('Strong-Pass-2026')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'rosa.lim@pnp.gov.ph']);
    }

    public function test_changing_a_password_follows_the_same_rules(): void
    {
        $this->actingAs($this->makeUser())->from('/profile')
            ->put('/password', ['current_password' => 'password', 'password' => 'weakpassword', 'password_confirmation' => 'weakpassword'])
            ->assertSessionHasErrors('password');
    }

    // ---------- 3. password reset email ----------

    public function test_the_password_reset_email_is_sent_right_away(): void
    {
        // Queued, it only went out while a queue worker was running.
        $this->assertNotInstanceOf(ShouldQueue::class, new CustomResetPassword('token'));
    }

    // ---------- 4. notification links ----------

    public function test_notifications_link_to_the_exact_place_or_nowhere(): void
    {
        $supervisor = $this->makeUser('supervisor');
        $trainee = $this->makeUser();

        $supervisor->notify(new TwoFactorWasReset('Admin Demo'));
        $saved = fn (User $user, array $data) => $user->notifications()->create([
            'id' => (string) Str::uuid(), 'type' => 'App\\Notifications\\Generic', 'data' => $data,
        ]);
        $saved($supervisor, ['message' => 'A new course is available']);                    // trainee page
        $saved($supervisor, ['message' => 'Profile link', 'url' => 'https://other-copy.example/profile']);
        $saved($supervisor, ['message' => 'Removed page', 'url' => '/admin/email-requests']);
        $saved($trainee, ['message' => 'A new course is available']);

        $links = fn (User $user) => collect($this->actingAs($user)->getJson('/notifications')->json())->pluck('url', 'message');

        $staff = $links($supervisor);
        $this->assertSame('/profile#mfa', $staff['Your MFA was reset by Admin Demo.']);
        $this->assertNull($staff['A new course is available']);  // staff can't open trainee pages
        $this->assertSame('/profile', $staff['Profile link']);   // same page on this copy of the site
        $this->assertNull($staff['Removed page']);

        $this->assertSame('/courses?tab=mine', $links($trainee)['A new course is available']);
    }
}
