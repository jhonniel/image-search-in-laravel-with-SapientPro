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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'code_name')) {
                // SQLite cannot add a NOT NULL column without default; make it nullable first
                $table->string('code_name')->nullable()->after('username');
            }
        });
        try {
            // Add a unique index if supported
            \Illuminate\Support\Facades\DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_code_name_unique ON users(code_name)');
        } catch (\Throwable $e) {
            // ignore if not supported
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Always drop the index first before dropping the column
        // This is required for SQLite and good practice for other databases
        try {
            $driver = DB::getDriverName();
            
            if ($driver === 'sqlite') {
                // Drop the unique index first (SQLite requires this)
                DB::statement('DROP INDEX IF EXISTS users_code_name_unique');
            } elseif ($driver === 'pgsql') {
                // PostgreSQL: DROP INDEX with schema
                DB::statement('DROP INDEX IF EXISTS users_code_name_unique');
            } else {
                // MySQL/MariaDB: DROP INDEX ON table
                DB::statement('DROP INDEX IF EXISTS users_code_name_unique ON users');
            }
        } catch (\Throwable $e) {
            // Index might not exist, ignore error
        }
        
        // Now drop the column
        // For SQLite, we need to use a workaround since DROP COLUMN has limitations
        if (Schema::hasColumn('users', 'code_name')) {
            $driver = DB::getDriverName();
            
            if ($driver === 'sqlite') {
                // SQLite doesn't support DROP COLUMN in all versions reliably
                // Use raw SQL ALTER TABLE DROP COLUMN (works in SQLite 3.35.0+)
                // If it fails, the column might already be dropped or version doesn't support it
                try {
                    DB::statement('ALTER TABLE users DROP COLUMN code_name');
                } catch (\Throwable $e) {
                    // If DROP COLUMN fails, the column might not exist or SQLite version is too old
                    // In that case, we'll skip dropping the column
                    // The index is already dropped, so it won't cause issues
                }
            } else {
                // For other databases, use Laravel's schema builder
                Schema::table('users', function (Blueprint $table) {
                    $table->dropColumn('code_name');
                });
            }
        }
    }
};
