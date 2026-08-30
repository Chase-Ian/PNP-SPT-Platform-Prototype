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
    }
}