<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Notifications\AccountReinstated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Deleting an account hides it; an admin can reinstate it until it is erased
 * (by an admin, or automatically after security.deleted_account_days).
 */
class AccountDeletionTest extends TestCase
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

    private function certificateFor(User $user): Certificate
    {
        $course = Course::create(['activity_code' => "TEST-{$user->id}", 'title' => 'Test course', 'is_published' => true]);

        return Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'serial_id' => 'PNP-SPT-CP-2026-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'verification_hash' => hash('sha256', "test-{$user->id}"),
            'issued_at' => now(),
        ]);
    }

    public function test_admin_deletes_and_reinstates_a_trainee(): void
    {
        Notification::fake();
        $admin = $this->makeUser('admin');
        $trainee = $this->makeUser('trainee', ['email' => 'trainee@pnp.gov.ph']);

        $this->actingAs($admin)->delete(route('admin.students.destroy', $trainee))->assertRedirect();
        $this->assertSoftDeleted($trainee);

        $this->actingAs($admin)->get(route('admin.students.index'))->assertInertia(fn ($page) => $page
            ->has('accounts', 0)
            ->has('deleted', 1)
            ->where('deleted.0.email', 'trainee@pnp.gov.ph')
            ->where('retentionDays', 30));

        $this->actingAs($admin)->post(route('admin.students.reinstate', $trainee))->assertRedirect();
        $this->assertNotSoftDeleted($trainee);
        Notification::assertSentTo($trainee, AccountReinstated::class);

        // Only deleted accounts can be reinstated.
        $this->actingAs($admin)->post(route('admin.students.reinstate', $trainee))->assertNotFound();
    }

    public function test_only_admins_can_reinstate_or_erase(): void
    {
        $supervisor = $this->makeUser('supervisor');
        $trainee = $this->makeUser();
        $trainee->delete();

        $this->actingAs($supervisor)->post(route('admin.students.reinstate', $trainee))->assertForbidden();
        $this->actingAs($supervisor)->delete(route('admin.students.erase', $trainee))->assertForbidden();
        $this->assertSoftDeleted($trainee);
    }

    public function test_a_deleted_account_cannot_sign_in(): void
    {
        $this->makeUser('trainee', ['email' => 'gone@pnp.gov.ph'])->delete();

        $this->post('/login', ['email' => 'gone@pnp.gov.ph', 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);
        $this->post('/login', ['email' => 'gone@pnp.gov.ph', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'This account was deleted. Ask an administrator to reinstate it.']);
        $this->assertGuest();
    }

    public function test_a_deleted_accounts_certificates_stop_verifying_until_reinstated(): void
    {
        $trainee = $this->makeUser();
        $serial = $this->certificateFor($trainee)->serial_id;

        $this->get("/verify/{$serial}")->assertInertia(fn ($page) => $page->whereNot('certificate', null));

        $trainee->delete();
        $this->get("/verify/{$serial}")->assertInertia(fn ($page) => $page->where('certificate', null));

        $trainee->restore();
        $this->get("/verify/{$serial}")->assertInertia(fn ($page) => $page->whereNot('certificate', null));
    }

    public function test_the_email_of_a_deleted_account_cannot_be_reused(): void
    {
        $this->makeUser('trainee', ['email' => 'taken@pnp.gov.ph'])->delete();

        $this->post('/register', [
            'first_name' => 'New', 'last_name' => 'Person', 'rank' => 'Patrolman/Patrolwoman (Pat)',
            'email' => 'taken@pnp.gov.ph', 'unit_office' => 'Unit', 'region' => 'NCRPO - National Capital Region Police Office',
            'password' => 'Strong-Pass-2026', 'password_confirmation' => 'Strong-Pass-2026', 'terms' => true,
        ])->assertSessionHasErrors(['email' => 'This email belongs to a deleted account. Ask an administrator to reinstate it.']);
    }

    public function test_admin_can_erase_a_deleted_account_now(): void
    {
        $admin = $this->makeUser('admin');
        $active = $this->makeUser();
        $deleted = $this->makeUser();
        $this->certificateFor($deleted);
        $deleted->delete();

        // An account must be deleted before it can be erased.
        $this->actingAs($admin)->delete(route('admin.students.erase', $active))->assertNotFound();

        $this->actingAs($admin)->delete(route('admin.students.erase', $deleted))->assertRedirect();
        $this->assertNull(User::withTrashed()->find($deleted->id));
        $this->assertSame(0, Certificate::withoutGlobalScopes()->where('user_id', $deleted->id)->count());
    }

    public function test_deleted_accounts_are_erased_after_the_retention_period(): void
    {
        $old = $this->makeUser();
        $old->delete();

        $this->travel(31)->days();
        $recent = $this->makeUser();
        $recent->delete();

        $this->artisan('model:prune', ['--model' => [User::class]])->assertSuccessful();

        $this->assertNull(User::withTrashed()->find($old->id));
        $this->assertSoftDeleted($recent);
    }

    public function test_staff_accounts_can_be_reinstated_but_not_your_own_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $supervisor = $this->makeUser('supervisor');

        $this->actingAs($admin)->delete(route('admin.staff.destroy', $admin))->assertForbidden();

        $this->actingAs($admin)->delete(route('admin.staff.destroy', $supervisor))->assertRedirect();
        $this->assertSoftDeleted($supervisor);
        $this->actingAs($admin)->get(route('admin.staff.index'))
            ->assertInertia(fn ($page) => $page->has('deleted', 1)->where('deleted.0.role', 'Supervisor'));

        $this->actingAs($admin)->post(route('admin.staff.reinstate', $supervisor))->assertRedirect();
        $this->assertNotSoftDeleted($supervisor);
    }
}
