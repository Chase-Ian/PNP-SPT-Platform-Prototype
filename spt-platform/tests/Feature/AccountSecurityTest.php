<?php

namespace Tests\Feature;

use App\Models\EmailChangeRequest;
use App\Models\User;
use App\Notifications\EmailAddressChanged;
use App\Notifications\TwoFactorRecoveryLink;
use App\Notifications\TwoFactorWasReset;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Fortify;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AccountSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'trainee', array $attrs = []): User
    {
        return User::factory()->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'role' => $role,
            'unit_office' => 'Test Unit',
            'region' => 'PRO NCR - National Capital Region (NCR)',
            ...$attrs,
        ]);
    }

    private function withTwoFactor(User $user, bool $acknowledged = true): User
    {
        $user->forceFill([
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['code-one', 'code-two'])),
            'two_factor_confirmed_at' => now(),
            'two_factor_codes_acknowledged_at' => $acknowledged ? now() : null,
        ])->save();

        return $user;
    }

    private function currentCode(User $user): string
    {
        return app(Google2FA::class)->getCurrentOtp(
            Fortify::currentEncrypter()->decrypt($user->fresh()->two_factor_secret)
        );
    }

    // ---------- the gate ----------

    public function test_unverified_email_is_sent_to_verify_page_on_any_page(): void
    {
        $user = $this->makeUser('trainee', ['email_verified_at' => null]);

        $this->actingAs($user)->get('/profile')->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get('/courses')->assertRedirect(route('verification.notice'));
        $this->actingAs($user)->get('/verify-email')->assertOk();
    }

    public function test_email_check_comes_before_two_factor_check(): void
    {
        config(['security.require_2fa' => 'all']);
        $user = $this->makeUser('trainee', ['email_verified_at' => null]);

        $this->actingAs($user)->get('/security/two-factor')->assertRedirect(route('verification.notice'));
    }

    public function test_nobody_is_forced_when_setting_is_none(): void
    {
        config(['security.require_2fa' => 'none']);
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/profile')->assertOk();
    }

    public function test_staff_setting_forces_admins_and_supervisors_but_not_trainees(): void
    {
        config(['security.require_2fa' => 'staff']);

        $this->actingAs($this->makeUser('admin'))->get('/admin/dashboard')->assertRedirect(route('security.setup'));
        $this->actingAs($this->makeUser('supervisor'))->get('/profile')->assertRedirect(route('security.setup'));
        $this->actingAs($this->makeUser('trainee'))->get('/profile')->assertOk();
    }

    public function test_unfinished_user_can_only_reach_setup_and_logout(): void
    {
        config(['security.require_2fa' => 'all']);
        $user = $this->makeUser();

        $this->actingAs($user)->get('/security/two-factor')->assertOk();
        $this->actingAs($user)->get('/certificates')->assertRedirect(route('security.setup'));
        $this->actingAs($user)->post('/logout')->assertRedirect('/');
    }

    public function test_half_finished_setup_counts_as_not_set_up(): void
    {
        config(['security.require_2fa' => 'all']);
        $user = $this->withTwoFactor($this->makeUser(), acknowledged: false);

        $this->actingAs($user)->get('/profile')->assertRedirect(route('security.setup'));
        $this->actingAs($user)->get('/security/two-factor')
            ->assertInertia(fn ($page) => $page->component('Security/SetupTwoFactor')->where('step', 'codes'));
    }

    public function test_exempt_demo_accounts_are_not_forced(): void
    {
        config(['security.require_2fa' => 'all', 'security.exempt_emails' => ['demo@pnp.gov.ph']]);
        $user = $this->makeUser('admin', ['email' => 'demo@pnp.gov.ph']);

        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_existing_accounts_get_a_grace_period_then_are_enforced(): void
    {
        config([
            'security.require_2fa' => 'all',
            'security.enforced_since' => now()->subDays(3)->toDateString(),
            'security.grace_days' => 14,
        ]);

        $old = $this->makeUser('trainee', ['created_at' => now()->subMonth()]);
        $new = $this->makeUser('trainee');

        $this->actingAs($old)->get('/profile')->assertOk()
            ->assertInertia(fn ($page) => $page->where('security.twoFactorReminderUntil', now()->subDays(3)->addDays(14)->toDateString()));
        $this->actingAs($new)->get('/profile')->assertRedirect(route('security.setup'));

        $this->travel(15)->days();
        $this->actingAs($old->fresh())->get('/profile')->assertRedirect(route('security.setup'));
    }

    // ---------- setup page ----------

    public function test_full_setup_flow_lands_on_dashboard_and_is_not_asked_again(): void
    {
        config(['security.require_2fa' => 'all']);
        $user = $this->makeUser();

        $this->actingAs($user)->post('/security/two-factor/start')->assertRedirect(route('security.setup'));
        $this->actingAs($user)->get('/security/two-factor')
            ->assertInertia(fn ($page) => $page->where('step', 'scan')->whereNot('qrCodeSvg', null)->whereNot('secretKey', null));

        $this->actingAs($user)->post('/security/two-factor/confirm', ['code' => '000000'])->assertSessionHasErrors('code');

        $this->actingAs($user)->post('/security/two-factor/confirm', ['code' => $this->currentCode($user)])
            ->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);

        // Still gated until the recovery codes are acknowledged.
        $this->actingAs($user)->get('/profile')->assertRedirect(route('security.setup'));
        $this->actingAs($user)->post('/security/two-factor/acknowledge', [])->assertSessionHasErrors('acknowledged');

        $this->actingAs($user)->post('/security/two-factor/acknowledge', ['acknowledged' => true])
            ->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get('/profile')->assertOk();
    }

    public function test_required_two_factor_cannot_be_disabled_but_codes_can_be_regenerated(): void
    {
        config(['security.require_2fa' => 'all']);
        $user = $this->withTwoFactor($this->makeUser());

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->deleteJson('/user/two-factor-authentication')->assertForbidden();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-authentication')->assertForbidden();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-recovery-codes')->assertOk();
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_secrets_are_not_shared_with_the_browser(): void
    {
        $user = $this->withTwoFactor($this->makeUser());

        $this->actingAs($user)->get('/profile')->assertInertia(fn ($page) => $page
            ->missing('auth.user.two_factor_secret')
            ->missing('auth.user.two_factor_recovery_codes'));
    }

    // ---------- login code ----------

    public function test_wrong_login_codes_are_limited_to_five_a_minute(): void
    {
        $user = $this->withTwoFactor($this->makeUser());

        for ($i = 0; $i < 5; $i++) {
            $this->withSession(['login.id' => $user->id])
                ->post('/two-factor-challenge', ['code' => '000000'])
                ->assertSessionHasErrors(['code' => 'The provided two-factor authentication code was invalid.']);
        }

        // The sixth try is refused even with the right code, and the code isn't used up.
        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['code' => $this->currentCode($user)])
            ->assertSessionHasErrors('code');
        $this->assertStringStartsWith('Too many incorrect codes', session('errors')->first('code'));
        $this->assertGuest();

        // Recovery codes count toward the same limit.
        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['recovery_code' => 'code-one'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->travel(61)->seconds();

        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['code' => $this->currentCode($user)])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_a_correct_login_code_resets_the_count(): void
    {
        $user = $this->withTwoFactor($this->makeUser());

        for ($i = 0; $i < 4; $i++) {
            $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['recovery_code' => 'wrong-code']);
        }
        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['recovery_code' => 'code-one'])
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(\Illuminate\Support\Facades\RateLimiter::attempts('two-factor-challenge|'.$user->id) === 0);
    }

    // ---------- recovery ----------

    public function test_admin_can_reset_another_users_two_factor(): void
    {
        Notification::fake();
        config(['security.require_2fa' => 'all', 'security.exempt_emails' => ['boss@pnp.gov.ph']]);
        $admin = $this->makeUser('admin', ['email' => 'boss@pnp.gov.ph']);
        $user = $this->withTwoFactor($this->makeUser());

        $this->actingAs($admin)->post(route('admin.users.reset-two-factor', $user))->assertRedirect();

        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);
        Notification::assertSentTo($user, TwoFactorWasReset::class);
        $this->actingAs($user->fresh())->get('/profile')->assertRedirect(route('security.setup'));
    }

    public function test_non_admins_and_self_cannot_reset_two_factor(): void
    {
        $admin = $this->withTwoFactor($this->makeUser('admin'));
        $supervisor = $this->makeUser('supervisor');
        $user = $this->withTwoFactor($this->makeUser());

        $this->actingAs($supervisor)->post(route('admin.users.reset-two-factor', $user))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.users.reset-two-factor', $admin))->assertForbidden();
        $this->assertNotNull($user->fresh()->two_factor_secret);
    }

    public function test_lost_device_email_link_resets_two_factor_in_the_same_session(): void
    {
        Notification::fake();
        $user = $this->withTwoFactor($this->makeUser());

        $this->withSession(['login.id' => $user->id])
            ->post(route('two-factor.recovery.send'))->assertSessionHas('status', 'recovery-link-sent');
        Notification::assertSentTo($user, TwoFactorRecoveryLink::class);

        $url = URL::temporarySignedRoute('two-factor.recovery.verify', now()->addMinutes(30), ['user' => $user->id]);

        // Different browser (no pending login) -> refused.
        $this->flushSession();
        $this->get($url)->assertRedirect(route('login'));
        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertGuest();

        $this->withSession(['login.id' => $user->id])->get($url)->assertRedirect(route('security.setup'));
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_recovery_link_needs_a_pending_login(): void
    {
        $this->post(route('two-factor.recovery.send'))->assertForbidden();
    }

    // ---------- email changes ----------

    public function test_profile_update_cannot_change_email(): void
    {
        $user = $this->makeUser('trainee', ['email' => 'original@pnp.gov.ph']);

        $this->actingAs($user)->patch('/profile', [
            'first_name' => 'New', 'last_name' => 'Name', 'rank' => $user->rank,
            'unit_office' => 'Unit', 'region' => $user->region, 'email' => 'hacked@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertSame('original@pnp.gov.ph', $user->fresh()->email);
        $this->assertSame('New', $user->fresh()->first_name);
    }

    public function test_trainee_requests_email_change_and_admin_approves(): void
    {
        Notification::fake();
        $admin = $this->makeUser('admin');
        $user = $this->makeUser('trainee', ['email' => 'old@pnp.gov.ph']);

        $this->actingAs($user)->post(route('profile.email-request.store'), [
            'new_email' => 'NEW@pnp.gov.ph', 'reason' => 'Transferred to a new unit',
        ])->assertSessionHasNoErrors();

        // Only one pending request at a time.
        $this->actingAs($user)->post(route('profile.email-request.store'), [
            'new_email' => 'other@pnp.gov.ph', 'reason' => 'Another reason here',
        ])->assertSessionHasErrors('new_email');

        $request = EmailChangeRequest::firstOrFail();
        $this->assertSame('new@pnp.gov.ph', $request->new_email);
        $this->assertSame('old@pnp.gov.ph', $user->fresh()->email);

        $this->actingAs($user)->post(route('admin.email-requests.approve', $request))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.email-requests.approve', $request))->assertRedirect();

        $user->refresh();
        $this->assertSame('new@pnp.gov.ph', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('approved', $request->fresh()->status);
        Notification::assertSentTo($user, VerifyEmail::class);
        Notification::assertSentOnDemand(EmailAddressChanged::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === 'old@pnp.gov.ph');

        // New address must be verified before using the platform again.
        $this->actingAs($user)->get('/courses')->assertRedirect(route('verification.notice'));
    }

    public function test_reject_needs_a_note_and_admins_cannot_review_their_own_request(): void
    {
        $admin = $this->makeUser('admin');
        $other = $this->makeUser('trainee');

        $own = $admin->emailChangeRequests()->create(['current_email' => $admin->email, 'new_email' => 'x@pnp.gov.ph', 'reason' => 'Some reason']);
        $theirs = $other->emailChangeRequests()->create(['current_email' => $other->email, 'new_email' => 'y@pnp.gov.ph', 'reason' => 'Some reason']);

        $this->actingAs($admin)->post(route('admin.email-requests.approve', $own))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.email-requests.reject', $theirs))->assertSessionHasErrors('admin_note');
        $this->actingAs($admin)->post(route('admin.email-requests.reject', $theirs), ['admin_note' => 'Use your official email'])
            ->assertRedirect();
        $this->assertSame('rejected', $theirs->fresh()->status);
    }
}
