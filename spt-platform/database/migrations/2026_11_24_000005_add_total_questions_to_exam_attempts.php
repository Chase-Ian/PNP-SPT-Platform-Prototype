<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * How many questions the exam had when it was taken. Grades used to be
     * worked out against the exam's current question count, so editing the
     * exam later changed old grades (and could push them past 100%).
     * Existing attempts stay null and fall back to the current count.
     */
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->unsignedSmallInteger('total_questions')->nullable()->after('score');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn('total_questions');
        });
    }
};
