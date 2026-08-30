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
        $table->string('badge_number')->nullable();
        $table->string('unit_office')->nullable();
        $table->string('region')->nullable();
        $table->boolean('two_factor_verified')->default(false);
        $table->enum('role', ['officer', 'admin'])->default('officer');
        $table->string('poa_user_id')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['badge_number', 'unit_office', 'region', 'two_factor_verified', 'role', 'poa_user_id']);
        });
    }
};
