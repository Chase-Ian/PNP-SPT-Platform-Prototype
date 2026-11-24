<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Services\CertificateIssuer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateIssuerTest extends TestCase
{
    use RefreshDatabase;

    private function trainee(): User
    {
        return User::factory()->create([
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'role' => 'trainee',
            'unit_office' => 'Station 1',
            'region' => 'PRO NCR - National Capital Region (NCR)',
        ]);
    }

    public function test_certificate_numbers_stay_unique_after_an_account_is_deleted(): void
    {
        Storage::fake();
        $course = Course::create(['activity_code' => 'SPT-101', 'title' => 'Responsible AI']);
        $issuer = app(CertificateIssuer::class);

        $first = $issuer->issue($this->trainee(), $course);
        $deleted = $this->trainee();
        $issuer->issue($deleted, $course);
        $third = $issuer->issue($this->trainee(), $course);

        // Deleting an account deletes its certificate. Numbering by "count + 1"
        // then handed the next certificate the third one's number and crashed.
        $deleted->delete();
        $next = $issuer->issue($this->trainee(), $course);

        $serials = Certificate::pluck('serial_id');
        $this->assertCount(3, $serials);
        $this->assertCount(3, $serials->unique());
        $this->assertNotSame($third->serial_id, $next->serial_id);
        $this->assertNotSame($first->serial_id, $next->serial_id);
        $this->assertMatchesRegularExpression('/^PNP-SPT-CP-\d{4}-\d{6}$/', $next->serial_id);
        $this->assertMatchesRegularExpression('/^SPT-PRON-\d{4}-\d{4}$/', $next->training_ctrl_no);
        Storage::assertExists($next->pdf_path);
    }

    public function test_issuing_twice_returns_the_existing_certificate(): void
    {
        Storage::fake();
        $course = Course::create(['activity_code' => 'SPT-102', 'title' => 'Ethics']);
        $user = $this->trainee();

        $once = app(CertificateIssuer::class)->issue($user, $course);
        $twice = app(CertificateIssuer::class)->issue($user, $course);

        $this->assertTrue($once->is($twice));
        $this->assertSame(1, Certificate::count());
    }

    public function test_the_qr_code_is_drawn_locally(): void
    {
        $uri = app(CertificateIssuer::class)->qrDataUri('https://example.test/verify/PNP-SPT-CP-2026-000001');

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $uri);
        $this->assertStringContainsString('<svg', base64_decode(substr($uri, strlen('data:image/svg+xml;base64,'))));
    }
}
