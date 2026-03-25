<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL TIMESTAMP is limited to 2038-01-19.
        // Lifetime licenses expire in 100 years (year 2126+), so we must use DATETIME.
        // Using Schema Builder (not raw SQL) so it is compatible with SQLite in tests.
        Schema::table('licenses', function (Blueprint $table) {
            $table->dateTime('activated_at')->nullable()->change();
            $table->dateTime('last_seen_at')->nullable()->change();
            $table->dateTime('expires_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('licenses', function (Blueprint $table) {
            $table->timestamp('activated_at')->nullable()->change();
            $table->timestamp('last_seen_at')->nullable()->change();
            $table->timestamp('expires_at')->nullable()->change();
        });
    }
};
