<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TwoFactorRecoveryLink;
use App\Notifications\TwoFactorWasReset;
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
            'region' => 'NCRPO - National Capital Region Police Office',
            ...$attrs,
        ]);
    }

    private function withTwoFactor(User $user): User
    {
        $user->forceFill([
            'two_factor_secret' => Fortify::currentEncrypter()->encrypt(app(Google2FA::class)->generateSecretKey()),
            'two_factor_confirmed_at' => now(),
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
        // A secret was made but the code was never confirmed.
        $user = $this->makeUser();
        $user->forceFill(['two_factor_secret' => Fortify::currentEncrypter()->encrypt(app(Google2FA::class)->generateSecretKey())])->save();

        $this->actingAs($user)->get('/profile')->assertRedirect(route('security.setup'));
        $this->actingAs($user)->get('/security/two-factor')
            ->assertInertia(fn ($page) => $page->component('Security/SetupTwoFactor')->where('step', 'scan'));
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

        // Confirming the code finishes setup: there are no recovery codes to save.
        $this->actingAs($user)->post('/security/two-factor/confirm', ['code' => $this->currentCode($user)])
            ->assertRedirect(route('dashboard'));
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->assertNull($user->fresh()->two_factor_recovery_codes);
        $this->actingAs($user)->get('/profile')->assertOk();
        $this->actingAs($user)->get('/security/two-factor')->assertRedirect(route('dashboard'));
    }

    public function test_required_mfa_cannot_be_disabled_and_has_no_recovery_codes(): void
    {
        config(['security.require_2fa' => 'all']);
        $user = $this->withTwoFactor($this->makeUser());

        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->deleteJson('/user/two-factor-authentication')->assertForbidden();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-authentication')->assertForbidden();
        // Fortify's recovery code endpoints are switched off.
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->getJson('/user/two-factor-recovery-codes')->assertNotFound();
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => time()])
            ->postJson('/user/two-factor-recovery-codes')->assertNotFound();
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
    }

    public function test_secrets_are_not_shared_with_the_browser(): void
    {
        $user = $this->withTwoFactor($this->makeUser());

        $this->actingAs($user)->get('/profile')->assertInertia(fn ($page) => $page
            ->missing('auth.user.two_factor_secret')
            ->missing('auth.user.two_factor_recovery_codes'));
    }

    // ---------- locked accounts ----------

    public function test_a_locked_account_is_signed_out_on_its_next_request(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/profile')->assertOk();

        // Locked by an Admin while this session is still open.
        $user->forceFill(['is_locked' => true, 'locked_at' => now()])->save();

        $this->actingAs($user->fresh())->get('/profile')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'This account has been locked. Please contact your administrator.']);
        $this->assertGuest();
    }

    public function test_remember_me_cannot_sign_a_locked_account_back_in(): void
    {
        $user = $this->makeUser(attrs: ['remember_token' => 'remember-token-123', 'is_locked' => true]);
        $recaller = auth()->guard('web')->getRecallerName();

        $this->withCookie($recaller, "{$user->id}|remember-token-123|{$user->password}")
            ->get('/profile')
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertNotSame('remember-token-123', $user->fresh()->remember_token);
    }

    // ---------- login code ----------

    public function test_wrong_login_codes_are_limited_to_five_a_minute(): void
    {
        $user = $this->withTwoFactor($this->makeUser());

        for ($i = 0; $i < 5; $i++) {
            $this->withSession(['login.id' => $user->id])
                ->post('/two-factor-challenge', ['code' => '000000'])
                ->assertSessionHasErrors(['code' => 'The provided MFA code was invalid.']);
        }

        // The sixth try is refused even with the right code, and the code isn't used up.
        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['code' => $this->currentCode($user)])
            ->assertSessionHasErrors('code');
        $this->assertStringStartsWith('Too many incorrect codes', session('errors')->first('code'));
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
            $this->withSession(['login.id' => $user->id])->post('/two-factor-challenge', ['code' => '000000']);
        }
        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['code' => $this->currentCode($user)])
            ->assertRedirect(route('dashboard'));

        $this->assertTrue(\Illuminate\Support\Facades\RateLimiter::attempts('two-factor-challenge|'.$user->id) === 0);
    }

    public function test_old_recovery_codes_no_longer_sign_in(): void
    {
        // Codes saved before recovery codes were removed don't work.
        $user = $this->withTwoFactor($this->makeUser());
        $user->forceFill(['two_factor_recovery_codes' => Fortify::currentEncrypter()->encrypt(json_encode(['code-one']))])->save();

        $this->withSession(['login.id' => $user->id])
            ->post('/two-factor-challenge', ['recovery_code' => 'code-one'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();
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

    // ---------- email address ----------

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

    public function test_email_change_requests_are_gone(): void
    {
        $admin = $this->makeUser('admin');
        $user = $this->makeUser();

        $this->actingAs($user)->post('/profile/email-change-request', ['new_email' => 'new@pnp.gov.ph'])->assertNotFound();
        $this->actingAs($admin)->get('/admin/email-requests')->assertNotFound();
        $this->actingAs($user)->get('/profile')->assertOk()->assertInertia(fn ($page) => $page->missing('emailRequests'));
    }
}
