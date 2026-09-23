<?php

// database/seeders/CourseSeeder.php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\ExamQuestion;
use App\Models\ExamSetting;
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
                'is_published'    => true,
                'instructor_name' => 'Insp. Maria Lopez',
            ]);

            Module::firstOrCreate(
                ['course_id' => $course->id, 'order' => 1],
                ['title' => 'Introduction to ' . $data['title'], 'duration_minutes' => 20]
            );

            // Uses the restructured exam_questions schema: type + answer_data (JSON)
            $questions = [
                [
                    'question'    => 'What is the primary goal of AI ethics?',
                    'type'        => 'multiple_choice',
                    'answer_data' => [
                        'choices'        => ['Speed', 'Fairness and safety', 'Cost reduction', 'Automation'],
                        'correct_choice' => 'Fairness and safety',
                    ],
                ],
                [
                    'question'    => 'Which law governs data privacy in the Philippines?',
                    'type'        => 'multiple_choice',
                    'answer_data' => [
                        'choices'        => ['RA 10173', 'RA 9262', 'RA 7610', 'RA 8792'],
                        'correct_choice' => 'RA 10173',
                    ],
                ],
            ];

            foreach ($questions as $q) {
                ExamQuestion::firstOrCreate(
                    ['course_id' => $course->id, 'question' => $q['question']],
                    ['type' => $q['type'], 'answer_data' => $q['answer_data']]
                );
            }

            ExamSetting::firstOrCreate(
                ['course_id' => $course->id],
                ['question_count' => 50, 'time_limit_minutes' => 120, 'pass_threshold_percent' => 80]
            );
        }
    }
}