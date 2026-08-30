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
        Schema::table('users', function (Blueprint $table) {
             Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('officer', 'supervisor', 'admin') NOT NULL DEFAULT 'officer'");
    });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('users', function (Blueprint $table) {
        DB::statement("ALTER TABLE users MODIFY role ENUM('officer', 'admin') NOT NULL DEFAULT 'officer'");
        });
    }
};
