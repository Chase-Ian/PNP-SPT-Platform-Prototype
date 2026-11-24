<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Accounts that self-registered after the officer -> trainee rename got
     * the column default 'officer', a role the app no longer uses, so admins
     * and supervisors couldn't see or manage them. Move them to 'trainee'.
     *
     * The column default itself is left alone: changing it rebuilds the users
     * table on SQLite. The User model sets 'trainee' as the default instead.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'officer')->update(['role' => 'trainee']);
    }

    /**
     * Not reversible: after up() these accounts can't be told apart from
     * trainees who registered with the right role.
     */
    public function down(): void
    {
        //
    }
};
