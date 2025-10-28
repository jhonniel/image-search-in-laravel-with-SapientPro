<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                // SQLite cannot add a NOT NULL column without default; make it nullable first
                $table->string('username')->nullable()->after('name');
            }
        });
        try {
            // Add a unique index if supported
            \Illuminate\Support\Facades\DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS users_username_unique ON users(username)');
        } catch (\Throwable $e) {
            // ignore if not supported
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropColumn('username');
            }
        });
    }
};


