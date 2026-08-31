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
            DB::statement("ALTER TABLE users MODIFY role ENUM('officer','trainee','supervisor','admin') NOT NULL DEFAULT 'officer'");
            DB::statement("UPDATE users SET role = 'trainee' WHERE role = 'officer'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('trainee','supervisor','admin') NOT NULL DEFAULT 'trainee'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            DB::statement("ALTER TABLE users MODIFY role ENUM('officer','trainee','supervisor','admin') NOT NULL DEFAULT 'trainee'");
            DB::statement("UPDATE users SET role = 'officer' WHERE role = 'trainee'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('officer','supervisor','admin') NOT NULL DEFAULT 'officer'");
        });
    }
};
