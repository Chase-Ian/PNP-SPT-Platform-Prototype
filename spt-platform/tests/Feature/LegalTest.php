<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $attrs = [], bool $accepted = true): User
    {
        $factory = User::factory();
        if (! $accepted) {
            $factory = $factory->withoutAcceptedTerms();
        }

        return $factory->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'role' => 'trainee',
            'unit_office' => 'Test Unit',
            'region' => 'PRO NCR - National Capital Region (NCR)',
            ...$attrs,
        ]);
    }

    private function registrationData(array $overrides = []): array
    {
        return [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'email' => 'juan@example.com',
            'unit_office' => 'Station 1',
            'region' => 'PRO NCR - National Capital Region (NCR)',
            'password' => 'password',
            'password_confirmation' => 'password',
            ...$overrides,
        ];
    }

    public function test_terms_and_privacy_notice_are_public(): void
    {
        $this->get('/terms')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Legal/Terms')->where('version', config('legal.version')));

        $this->get('/privacy')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Legal/Privacy')
                ->where('processors.storage', config('legal.processors.storage'))
                ->where('twoFactorScope', config('security.require_2fa')));
    }

    public function test_registration_requires_agreeing_to_the_terms(): void
    {
        $this->post('/register', $this->registrationData())->assertSessionHasErrors('terms');

        $this->assertDatabaseMissing('users', ['email' => 'juan@example.com']);
        $this->assertGuest();
    }

    public function test_registration_records_the_agreement(): void
    {
        $this->post('/register', $this->registrationData(['terms' => '1']))
            ->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'juan@example.com')->firstOrFail();
        $this->assertSame(config('legal.version'), $user->terms_accepted_version);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertDatabaseHas('legal_acceptances', [
            'user_id' => $user->id,
            'version' => config('legal.version'),
            'ip_address' => '127.0.0.1',
        ]);
    }

    public function test_users_who_have_not_accepted_are_sent_to_the_accept_page(): void
    {
        $user = $this->makeUser(accepted: false);

        $this->actingAs($user)->get('/profile')->assertRedirect(route('legal.accept'));
        $this->actingAs($user)->get('/certificates')->assertRedirect(route('legal.accept'));

        // They can still read both documents, and log out.
        $this->actingAs($user)->get('/terms')->assertOk();
        $this->actingAs($user)->get('/privacy')->assertOk();
        $this->actingAs($user)->get('/legal/accept')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Legal/Accept')->where('isUpdate', false));
        $this->actingAs($user)->post('/logout')->assertRedirect('/');
    }

    public function test_accepting_records_it_and_returns_to_the_page_they_wanted(): void
    {
        $user = $this->makeUser(accepted: false);

        $this->actingAs($user)->get('/courses')->assertRedirect(route('legal.accept'));
        $this->actingAs($user)->post('/legal/accept', [])->assertSessionHasErrors('accepted');

        $this->actingAs($user)->post('/legal/accept', ['accepted' => '1'])->assertRedirect('/courses');

        $this->assertSame(config('legal.version'), $user->fresh()->terms_accepted_version);
        $this->assertSame(1, $user->legalAcceptances()->count());
        $this->actingAs($user->fresh())->get('/profile')->assertOk();
    }

    public function test_a_new_version_asks_everyone_to_accept_again(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user)->get('/profile')->assertOk();

        config(['legal.version' => '2099-01-01']);

        $this->actingAs($user)->get('/profile')->assertRedirect(route('legal.accept'));
        $this->actingAs($user)->get('/legal/accept')
            ->assertInertia(fn ($page) => $page->where('isUpdate', true)->where('version', '2099-01-01'));

        $this->actingAs($user)->post('/legal/accept', ['accepted' => '1']);
        $this->assertSame('2099-01-01', $user->fresh()->terms_accepted_version);
        $this->actingAs($user->fresh())->get('/profile')->assertOk();
    }

    public function test_gate_order_is_email_then_terms_then_two_factor(): void
    {
        config(['security.require_2fa' => 'all']);
        $user = $this->makeUser(['email_verified_at' => null], accepted: false);

        $this->actingAs($user)->get('/profile')->assertRedirect(route('verification.notice'));

        $user->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($user->fresh())->get('/profile')->assertRedirect(route('legal.accept'));

        $this->actingAs($user->fresh())->post('/legal/accept', ['accepted' => '1']);
        $this->actingAs($user->fresh())->get('/profile')->assertRedirect(route('security.setup'));
    }
}
