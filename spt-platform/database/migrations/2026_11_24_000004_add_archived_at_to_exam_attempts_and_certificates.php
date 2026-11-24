<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A supervisor can archive a course's results when the course changes
     * enough that trainees must redo it and take the new final exam. Archived
     * attempts and certificates are kept as history; they just stop counting
     * as the trainee's current result.
     */
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('answers');
            $table->index(['course_id', 'archived_at']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('issued_at');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'archived_at']);
            $table->dropColumn('archived_at');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
