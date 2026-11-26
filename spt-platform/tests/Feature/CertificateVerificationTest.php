<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The public certificate check shows only what verifying needs: never the
 * holder's unit, office or region (not even in the page data).
 */
class CertificateVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_check_shows_only_what_verifying_needs(): void
    {
        $trainee = User::factory()->create([
            'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'role' => 'trainee',
            'rank' => 'Patrolman/Patrolwoman (Pat)', 'unit_office' => 'Station 9',
            'region' => 'NCRPO - National Capital Region Police Office',
        ]);
        $course = Course::create(['activity_code' => 'VER-1', 'title' => 'Ethics', 'is_published' => true]);
        Certificate::create([
            'user_id' => $trainee->id, 'course_id' => $course->id, 'serial_id' => 'PNP-SPT-CP-2026-000042',
            'verification_hash' => hash('sha256', 'verify-42'), 'issued_at' => now(),
        ]);

        $this->get('/verify/PNP-SPT-CP-2026-000042')->assertOk()->assertInertia(fn ($page) => $page
            ->component('Verify/Show')
            ->where('certificate.holder_name', $trainee->name)
            ->where('certificate.title', 'Ethics')
            ->missing('certificate.unit_office')
            ->missing('certificate.region'));

        $this->get('/verify/PNP-SPT-CP-2026-000042')->assertDontSee('Station 9')->assertDontSee('NCRPO');
    }
}
