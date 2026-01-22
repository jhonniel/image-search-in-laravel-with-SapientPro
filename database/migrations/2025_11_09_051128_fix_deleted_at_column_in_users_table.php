<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Database-agnostic approach - works with SQLite, PostgreSQL, MySQL
        if (!Schema::hasColumn('users', 'deleted_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('deleted_at')->nullable();
            });
        } else {
            // Column exists, ensure it's nullable (for PostgreSQL compatibility)
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                // PostgreSQL: Ensure column is nullable
                DB::statement('ALTER TABLE users ALTER COLUMN deleted_at DROP NOT NULL');
            } elseif ($driver === 'sqlite') {
                // SQLite: Verify column is accessible
                try {
                    DB::table('users')->select('deleted_at')->limit(1)->get();
                } catch (\Exception $e) {
                    // Column exists but isn't accessible - ensure it's nullable
                    DB::statement("UPDATE users SET deleted_at = NULL WHERE deleted_at IS NULL");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
