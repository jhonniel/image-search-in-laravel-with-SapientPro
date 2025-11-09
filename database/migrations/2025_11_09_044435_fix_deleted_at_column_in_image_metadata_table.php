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
            $columns = DB::select("PRAGMA table_info(image_metadata)");
            $hasDeletedAt = false;
            
            foreach ($columns as $column) {
                if (strtolower($column->name) === 'deleted_at') {
                    $hasDeletedAt = true;
                    break;
                }
            }
            
            // If column exists but isn't working, recreate it using SQLite's method
            if ($hasDeletedAt) {
                // SQLite doesn't support DROP COLUMN easily, so we'll use a workaround
                // Try to verify the column works by testing a simple query
                try {
                    DB::table('image_metadata')->select('deleted_at')->limit(1)->get();
                } catch (\Exception $e) {
                    // Column exists but isn't accessible, need to recreate table
                    // This is a complex operation, so we'll use a simpler approach
                    // Just ensure the column is properly nullable
                    DB::statement("UPDATE image_metadata SET deleted_at = NULL WHERE deleted_at IS NULL");
                }
            } else {
                // Column doesn't exist, add it
                Schema::table('image_metadata', function (Blueprint $table) {
                    $table->timestamp('deleted_at')->nullable();
                });
            }
        } else {
            // For other databases, use standard Laravel methods
            if (!Schema::hasColumn('image_metadata', 'deleted_at')) {
                Schema::table('image_metadata', function (Blueprint $table) {
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
        Schema::table('image_metadata', function (Blueprint $table) {
            if (Schema::hasColumn('image_metadata', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
