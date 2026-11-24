<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_register_as_trainees_and_must_verify_their_email(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'email' => 'test@example.com',
            'unit_office' => 'Station 1',
            'region' => 'PRO NCR - National Capital Region (NCR)',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('trainee', $user->role);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_accounts_left_on_the_old_officer_role_become_trainees(): void
    {
        $user = User::factory()->create(['rank' => 'Patrolman/Patrolwoman (Pat)', 'role' => 'officer']);

        (require database_path('migrations/2026_11_24_000002_move_officer_role_to_trainee.php'))->up();

        $this->assertSame('trainee', $user->fresh()->role);
    }
}
