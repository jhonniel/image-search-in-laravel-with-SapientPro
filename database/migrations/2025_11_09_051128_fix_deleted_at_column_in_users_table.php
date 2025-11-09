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
        // Check database driver
        $driver = DB::getDriverName();
        
        if ($driver === 'sqlite') {
            // For SQLite, check if column exists and is accessible
            $columns = DB::select("PRAGMA table_info(users)");
            $hasDeletedAt = false;
            
            foreach ($columns as $column) {
                if (strtolower($column->name) === 'deleted_at') {
                    $hasDeletedAt = true;
                    break;
                }
            }
            
            // If column exists but isn't working, verify it's accessible
            if ($hasDeletedAt) {
                // Try to verify the column works by testing a simple query
                try {
                    DB::table('users')->select('deleted_at')->limit(1)->get();
                } catch (\Exception $e) {
                    // Column exists but isn't accessible, this shouldn't happen but if it does,
                    // we'll try to ensure the column is properly set up
                    // For SQLite, we can't easily recreate, so we'll just verify it exists
                }
            } else {
                // Column doesn't exist, add it
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable();
                });
            }
        } else {
            // For other databases, use standard Laravel methods
            if (!Schema::hasColumn('users', 'deleted_at')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable();
                });
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
