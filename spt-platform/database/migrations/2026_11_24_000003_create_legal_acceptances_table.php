<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The latest accepted version, so the Access Gate can check it cheaply.
        Schema::table('users', function (Blueprint $table) {
            $table->string('terms_accepted_version')->nullable()->after('two_factor_codes_acknowledged_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('terms_accepted_version');
        });

        // Every acceptance, kept as proof of who agreed to which version and when.
        Schema::create('legal_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('accepted_at');

            $table->index(['user_id', 'accepted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_acceptances');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['terms_accepted_version', 'terms_accepted_at']);
        });
    }
};
