<?php

// database/seeders/CourseSeeder.php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\LessonQuizQuestion;
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
                [
                    'title' => 'Module 1: Fundamentals of Artificial Intelligence',
                    'description' => 'An introductory, self-paced module on AI concepts, practical PNP applications, and responsible use.',
                    'duration_minutes' => 60,
                ]
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

        $this->seedFundamentalsModule($courses[0]['activity_code']);
    }

    private function seedFundamentalsModule(string $activityCode): void
    {
        $course = Course::where('activity_code', $activityCode)->firstOrFail();
        $module = $course->modules()->where('order', 1)->firstOrFail();

        $module->update([
            'title' => 'Module 1: Fundamentals of Artificial Intelligence',
            'description' => 'An introductory, self-paced module on AI concepts, practical PNP applications, and responsible use.',
            'duration_minutes' => 60,
        ]);

        $lessons = [
            [
                'order' => 1,
                'title' => 'What Is Artificial Intelligence?',
                'duration_minutes' => 15,
                'content' => [
                    ['type' => 'heading', 'text' => 'Learning objective'],
                    ['type' => 'paragraph', 'text' => 'By the end of this lesson, you should be able to explain artificial intelligence in simple terms and distinguish narrow AI from the idea of general AI.'],
                    ['type' => 'heading', 'text' => 'A practical definition'],
                    ['type' => 'paragraph', 'text' => 'Artificial intelligence is the field of building computer systems that perform tasks commonly associated with human intelligence, such as recognizing patterns, understanding language, making predictions, and supporting decisions. AI systems do not think like people. They process data using rules, statistical models, or learned patterns to produce an output.'],
                    ['type' => 'bullet_list', 'items' => [
                        ['bold' => 'Narrow AI:', 'text' => 'designed for a specific task or limited set of tasks.'],
                        ['bold' => 'Machine learning:', 'text' => 'a way for a system to learn patterns from examples rather than relying only on manually written rules.'],
                        ['bold' => 'Generative AI:', 'text' => 'a type of system that produces new text, images, audio, or other content from an instruction.'],
                    ]],
                    ['type' => 'callout', 'variant' => 'info', 'text' => 'Most AI used in real organizations today is narrow AI. Its usefulness depends on the quality of its data, its design, and the people who review its output.'],
                ],
                'questions' => [
                    ['question' => 'Which description best matches narrow AI?', 'choices' => ['A system designed for a specific task', 'A system that can do every human task', 'A person using a computer', 'A database with no rules'], 'correct_choice' => 'A system designed for a specific task'],
                    ['question' => 'What does machine learning primarily use to find patterns?', 'choices' => ['Examples and data', 'Random guesses only', 'Paper forms only', 'A power supply'], 'correct_choice' => 'Examples and data'],
                ],
            ],
            [
                'order' => 2,
                'title' => 'How AI Systems Work',
                'duration_minutes' => 15,
                'content' => [
                    ['type' => 'heading', 'text' => 'From data to output'],
                    ['type' => 'paragraph', 'text' => 'An AI system receives an input, applies a model that was developed or trained using data, and returns an output. The output may be a classification, prediction, recommendation, summary, or generated response. The result is not automatically a fact; it must be checked against reliable information and the situation at hand.'],
                    ['type' => 'bullet_list', 'items' => [
                        ['bold' => 'Input:', 'text' => 'the information given to the system, such as text, an image, or structured records.'],
                        ['bold' => 'Model:', 'text' => 'the learned or programmed method used to identify patterns and produce a result.'],
                        ['bold' => 'Output:', 'text' => 'the prediction, recommendation, or content returned to the user.'],
                        ['bold' => 'Human review:', 'text' => 'the step where an authorized person checks whether the result is accurate, relevant, and appropriate.'],
                    ]],
                    ['type' => 'heading', 'text' => 'Why results can be wrong'],
                    ['type' => 'paragraph', 'text' => 'AI can produce an incorrect or misleading result when its training data is incomplete, the input is unclear, the task is outside its design, or the system reflects bias in the data. A confident-sounding answer is not proof that the answer is correct.'],
                    ['type' => 'callout', 'variant' => 'warning', 'text' => 'Never treat an AI output as the sole basis for an operational, legal, disciplinary, or safety-critical decision.'],
                ],
                'questions' => [
                    ['question' => 'What should happen after an AI system produces an output?', 'choices' => ['An authorized person should review it', 'It should always be accepted immediately', 'It should be deleted without review', 'It should be shared publicly'], 'correct_choice' => 'An authorized person should review it'],
                    ['question' => 'Which factor can cause an AI result to be misleading?', 'choices' => ['Incomplete or biased data', 'Human review', 'A clear task definition', 'Reliable reference material'], 'correct_choice' => 'Incomplete or biased data'],
                ],
            ],
            [
                'order' => 3,
                'title' => 'AI Opportunities for PNP Work',
                'duration_minutes' => 15,
                'content' => [
                    ['type' => 'heading', 'text' => 'Supporting, not replacing, personnel'],
                    ['type' => 'paragraph', 'text' => 'AI literacy helps PNP personnel recognize where technology may support routine learning and administrative work. Appropriate uses may include summarizing approved training material, organizing information, identifying patterns for further review, or helping prepare a draft that a qualified person will verify.'],
                    ['type' => 'bullet_list', 'items' => [
                        ['bold' => 'Efficiency:', 'text' => 'reduce time spent on repetitive information-processing tasks.'],
                        ['bold' => 'Consistency:', 'text' => 'apply the same documented process to similar inputs.'],
                        ['bold' => 'Accessibility:', 'text' => 'help users search, summarize, or understand approved learning material.'],
                        ['bold' => 'Decision support:', 'text' => 'surface information for human consideration, without transferring accountability to the system.'],
                    ]],
                    ['type' => 'heading', 'text' => 'Use boundaries'],
                    ['type' => 'paragraph', 'text' => 'A proposed use must have a clear purpose, an authorized user, an approved source of data, and a review process. Personnel remain responsible for following PNP policy, applicable law, and the chain of command.'],
                    ['type' => 'callout', 'variant' => 'important', 'text' => 'Use AI as a support tool. Authority, accountability, and final judgment remain with the authorized human decision-maker.'],
                ],
                'questions' => [
                    ['question' => 'What is the appropriate role of AI in operational work?', 'choices' => ['Decision support for authorized personnel', 'A replacement for accountability', 'An unrestricted source of orders', 'A way to bypass policy'], 'correct_choice' => 'Decision support for authorized personnel'],
                    ['question' => 'Before using AI for a task, personnel should identify its purpose, data source, and what else?', 'choices' => ['A review process', 'A public audience', 'A personal password', 'An unrelated application'], 'correct_choice' => 'A review process'],
                ],
            ],
            [
                'order' => 4,
                'title' => 'Responsible and Safe AI Use',
                'duration_minutes' => 15,
                'content' => [
                    ['type' => 'heading', 'text' => 'Core responsibilities'],
                    ['type' => 'paragraph', 'text' => 'Responsible AI use protects people, information, and institutional trust. Before entering information into an AI tool, consider whether the tool is approved and whether the information contains personal, confidential, sensitive, or operational details.'],
                    ['type' => 'bullet_list', 'items' => [
                        ['bold' => 'Privacy:', 'text' => 'do not disclose personal or sensitive information to an unapproved tool.'],
                        ['bold' => 'Security:', 'text' => 'use only authorized accounts, systems, and data sources.'],
                        ['bold' => 'Fairness:', 'text' => 'look for errors or patterns that could unfairly affect people or groups.'],
                        ['bold' => 'Transparency:', 'text' => 'record and disclose AI assistance when required by policy.'],
                        ['bold' => 'Human oversight:', 'text' => 'verify outputs and retain responsibility for the final action.'],
                    ]],
                    ['type' => 'heading', 'text' => 'Before you use an AI tool'],
                    ['type' => 'paragraph', 'text' => 'Pause and ask: Is this tool approved? Is the information necessary for the task? Could the output harm someone if it is wrong? Who will review the result? If any answer is unclear, stop and consult the proper supervisor or policy owner.'],
                    ['type' => 'callout', 'variant' => 'info', 'text' => 'The safest default is to use approved educational materials and remove unnecessary identifying details before requesting assistance.'],
                ],
                'questions' => [
                    ['question' => 'What is the safest action when it is unclear whether an AI tool is approved?', 'choices' => ['Stop and consult the proper authority', 'Upload the information anyway', 'Share the tool with others', 'Ignore the concern'], 'correct_choice' => 'Stop and consult the proper authority'],
                    ['question' => 'Who remains responsible for the final action supported by AI?', 'choices' => ['The authorized human decision-maker', 'The AI system', 'The internet provider', 'No one'], 'correct_choice' => 'The authorized human decision-maker'],
                ],
            ],
        ];

        foreach ($lessons as $lessonData) {
            $questions = $lessonData['questions'];
            unset($lessonData['questions']);

            $lesson = Lesson::firstOrCreate(
                ['module_id' => $module->id, 'order' => $lessonData['order']],
                [...$lessonData, 'course_id' => $course->id]
            );

            foreach ($questions as $question) {
                LessonQuizQuestion::firstOrCreate(
                    ['lesson_id' => $lesson->id, 'question' => $question['question']],
                    $question
                );
            }
        }
    }
}