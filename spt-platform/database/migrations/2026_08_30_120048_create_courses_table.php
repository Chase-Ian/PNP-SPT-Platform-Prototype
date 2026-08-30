<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('activity_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('instructor_name')->nullable();
            $table->unsignedTinyInteger('duration_hours')->default(6);
            $table->unsignedTinyInteger('lesson_count')->default(5);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
