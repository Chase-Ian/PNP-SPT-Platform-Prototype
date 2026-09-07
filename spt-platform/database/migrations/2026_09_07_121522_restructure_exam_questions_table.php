<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->enum('type', ['multiple_choice', 'true_false', 'matching', 'identification'])
                ->default('multiple_choice')
                ->after('course_id');
            $table->json('answer_data')->nullable()->after('question');
        });

        // Migrate any existing rows (from the old choices/correct_choice columns) into the new shape
        \DB::table('exam_questions')->get()->each(function ($row) {
            if ($row->choices && $row->correct_choice) {
                \DB::table('exam_questions')->where('id', $row->id)->update([
                    'type' => 'multiple_choice',
                    'answer_data' => json_encode([
                        'choices' => json_decode($row->choices, true),
                        'correct_choice' => $row->correct_choice,
                    ]),
                ]);
            }
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn(['choices', 'correct_choice']);
        });
    }

    public function down(): void
    {
        Schema::table('exam_questions', function (Blueprint $table) {
            $table->json('choices')->nullable();
            $table->string('correct_choice')->nullable();
        });

        \DB::table('exam_questions')->get()->each(function ($row) {
            $data = json_decode($row->answer_data, true);
            if ($row->type === 'multiple_choice' && $data) {
                \DB::table('exam_questions')->where('id', $row->id)->update([
                    'choices' => json_encode($data['choices'] ?? []),
                    'correct_choice' => $data['correct_choice'] ?? null,
                ]);
            }
        });

        Schema::table('exam_questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'answer_data']);
        });
    }
};