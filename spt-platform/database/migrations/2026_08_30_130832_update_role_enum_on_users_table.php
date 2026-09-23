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
        // SQLite does not support MODIFY ENUM — role column is already a plain string.
        // No action needed; the column accepts any string value.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op — see up() comment.
    }
};
