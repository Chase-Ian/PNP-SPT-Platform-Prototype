<?php

// database/seeders/CourseSeeder.php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['activity_code' => 'SRAIU-2026-M1', 'title' => 'Fundamentals of Artificial Intelligence', 'duration_hours' => 6, 'lesson_count' => 6],
            ['activity_code' => 'SRAIU-2026-M2', 'title' => 'Generative AI and AI Tools for Law Enforcement', 'duration_hours' => 6, 'lesson_count' => 6],
            ['activity_code' => 'SRAIU-2026-M3', 'title' => 'Responsible AI, Ethics, and Human Rights', 'duration_hours' => 6, 'lesson_count' => 5],
            ['activity_code' => 'SRAIU-2026-M4', 'title' => 'Data Privacy, Cybersecurity, and AI Risks', 'duration_hours' => 6, 'lesson_count' => 5],
        ];

        foreach ($courses as $data) {
            $course = Course::firstOrCreate(['activity_code' => $data['activity_code']], [
                ...$data,
                'is_published' => true,
                'instructor_name' => 'Insp. Maria Lopez',
            ]);

            Module::firstOrCreate(
                ['course_id' => $course->id, 'order' => 1],
                ['title' => 'Introduction to ' . $data['title'], 'duration_minutes' => 20]
            );
        }
    
        $maria = \App\Models\User::where('email', 'maria.cruz@pnp.gov.ph')->first();
        $courses = \App\Models\Course::all();

        if ($maria && $courses->isNotEmpty()) {
            // Enroll in first two courses
            \App\Models\Enrollment::firstOrCreate(['user_id' => $maria->id, 'course_id' => $courses[0]->id], ['status' => 'completed']);
            \App\Models\Enrollment::firstOrCreate(['user_id' => $maria->id, 'course_id' => $courses[1]->id], ['status' => 'enrolled']);

            // Mark first course's module complete
            $module = \App\Models\Module::where('course_id', $courses[0]->id)->first();
            if ($module) {
                \App\Models\ModuleCompletion::firstOrCreate(
                    ['user_id' => $maria->id, 'module_id' => $module->id],
                    ['completed_at' => now()->subDays(3), 'minutes_spent' => 45]
                );
            }

            // Sample exam attempt
            \App\Models\ExamAttempt::firstOrCreate(
                ['user_id' => $maria->id, 'course_id' => $courses[0]->id],
                ['score' => 42, 'passed' => true, 'answers' => []]
            );

            // Sample certificate
            \App\Models\Certificate::firstOrCreate(
                ['user_id' => $maria->id, 'course_id' => $courses[0]->id],
                [
                    'serial_id' => 'PNP-2026-000001',
                    'verification_hash' => hash('sha256', $maria->id . $courses[0]->id . now()),
                    'issued_at' => now()->subDays(2),
                ]
            );
        }
    }
}