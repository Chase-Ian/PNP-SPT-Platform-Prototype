<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ModuleCompletion;
use App\Models\ExamAttempt;
use App\Models\Certificate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin.demo@pnp.gov.ph'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('demo1234'),
                'two_factor_verified' => true,
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'supervisor.demo@pnp.gov.ph'],
            [
                'name' => 'Supervisor Demo',
                'password' => Hash::make('demo1234'),
                'unit_office' => 'Regional Training Command',
                'two_factor_verified' => true,
                'role' => 'supervisor',
            ]
        );

        $courses = Course::all();

        if ($courses->isEmpty()) {
            // CourseSeeder must run before this seeder — see DatabaseSeeder.php order
            return;
        }

        // Every trainee (Maria + the 5 samples) defined together, with explicit
        // per-person enrollment counts and statuses — no more range-based guessing.
        $trainees = [
            [
                'name' => 'Maria Cruz', 'email' => 'maria.cruz@pnp.gov.ph',
                'unit_office' => 'Manila Police District - Station 1 (Ermita)',
                'region' => 'National Capital Region (NCR)',
                'enroll_count' => 2, 'in_progress_index' => 1, 'fails_exam' => false,
            ],
            [
                'name' => 'Juan Santos', 'email' => 'juan.santos@pnp.gov.ph',
                'unit_office' => 'Pampanga Police Provincial Office',
                'region' => 'Region 3 - Central Luzon',
                'enroll_count' => 2, 'in_progress_index' => null, 'fails_exam' => false,
            ],
            [
                'name' => 'Pedro Reyes', 'email' => 'pedro.reyes@pnp.gov.ph',
                'unit_office' => 'Manila Police District - Station 1 (Ermita)',
                'region' => 'National Capital Region (NCR)',
                'enroll_count' => 2, 'in_progress_index' => null, 'fails_exam' => false,
            ],
            [
                'name' => 'Ana Villanueva', 'email' => 'ana.villanueva@pnp.gov.ph',
                'unit_office' => 'Quezon City Police District',
                'region' => 'National Capital Region (NCR)',
                'enroll_count' => 2, 'in_progress_index' => 1, 'fails_exam' => false,
            ],
            [
                'name' => 'Ramon Bautista', 'email' => 'ramon.bautista@pnp.gov.ph',
                'unit_office' => 'Cebu City Police Office',
                'region' => 'Region 7 - Central Visayas',
                'enroll_count' => 1, 'in_progress_index' => null, 'fails_exam' => true,
            ],
            [
                'name' => 'Liza Fernandez', 'email' => 'liza.fernandez@pnp.gov.ph',
                'unit_office' => 'Davao City Police Office',
                'region' => 'Region 11 - Davao Region',
                'enroll_count' => 1, 'in_progress_index' => 0, 'fails_exam' => false,
            ],
        ];

        foreach ($trainees as $i => $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('demo1234'),
                    'unit_office' => $data['unit_office'],
                    'region' => $data['region'],
                    'two_factor_verified' => true,
                    'role' => 'trainee',
                ]
            );

            foreach ($courses->take($data['enroll_count']) as $j => $course) {
                $isInProgress = $data['in_progress_index'] === $j;
                $status = $isInProgress ? 'enrolled' : 'completed';

                Enrollment::updateOrCreate(
                    ['user_id' => $user->id, 'course_id' => $course->id],
                    ['status' => $status]
                );

                if ($status !== 'completed') {
                    continue;
                }

                $module = $course->modules()->first();
                if ($module) {
                    ModuleCompletion::updateOrCreate(
                        ['user_id' => $user->id, 'module_id' => $module->id],
                        ['completed_at' => now()->subDays(rand(1, 20)), 'minutes_spent' => rand(30, 90)]
                    );
                }

                $passed = ! $data['fails_exam'];
                $score = $passed ? rand(40, 50) : rand(20, 35);

                ExamAttempt::updateOrCreate(
                    ['user_id' => $user->id, 'course_id' => $course->id],
                    ['score' => $score, 'passed' => $passed, 'answers' => []]
                );

                if ($passed) {
                    Certificate::updateOrCreate(
                        ['user_id' => $user->id, 'course_id' => $course->id],
                        [
                            'serial_id' => 'PNP-2026-' . str_pad((100 + $i * 10 + $j), 6, '0', STR_PAD_LEFT),
                            'verification_hash' => hash('sha256', $user->id . $course->id . now() . $i . $j),
                            'issued_at' => now()->subDays(rand(1, 15)),
                        ]
                    );
                }
            }
        }

        // --- Sample notifications for Maria ---
        $maria = User::where('email', 'maria.cruz@pnp.gov.ph')->first();
        if ($maria) {
            DB::table('notifications')->insert([
                [
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\Generic',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $maria->id,
                    'data' => json_encode(['message' => 'New course available: Advanced Crisis Management']),
                    'read_at' => null,
                    'created_at' => now()->subHours(2),
                    'updated_at' => now()->subHours(2),
                ],
                [
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\Generic',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $maria->id,
                    'data' => json_encode(['message' => 'You have completed Police Ethics and Conduct']),
                    'read_at' => null,
                    'created_at' => now()->subHours(5),
                    'updated_at' => now()->subHours(5),
                ],
            ]);
        }
    }
}