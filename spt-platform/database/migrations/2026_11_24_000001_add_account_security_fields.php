<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 2FA only counts as set up once the user has also acknowledged
            // saving their recovery codes.
            $table->timestamp('two_factor_codes_acknowledged_at')->nullable()->after('two_factor_confirmed_at');
        });

        // Accounts that already confirmed 2FA before this change keep it.
        DB::table('users')
            ->whereNotNull('two_factor_confirmed_at')
            ->update(['two_factor_codes_acknowledged_at' => DB::raw('two_factor_confirmed_at')]);

        // Email verification is enforced from now on. Accounts created before
        // it existed never received a verification email, so treat them as
        // verified instead of locking them out at their next request.
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => DB::raw('created_at')]);

        Schema::create('email_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_email');
            $table->string('new_email');
            $table->text('reason');
            $table->string('status')->default('pending'); // pending | approved | rejected | cancelled
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_change_requests');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('two_factor_codes_acknowledged_at');
        });
    }
};
