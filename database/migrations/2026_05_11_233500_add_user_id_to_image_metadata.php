<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('image_metadata', 'user_id')) {
            Schema::table('image_metadata', function (Blueprint $table): void {
                // Nullable so guest uploads stay valid; foreign-keyless to keep
                // SQLite/MySQL portability simple. We add an index for lookup.
                $table->unsignedBigInteger('user_id')->nullable()->after('uploader_email');
                $table->index('user_id');
            });
        }

        // Backfill user_id from existing uploader_email -> users.email mapping.
        // We use a correlated subquery so the same SQL works on SQLite,
        // PostgreSQL, and MySQL 8+ (MySQL's `UPDATE … JOIN` and PostgreSQL's
        // `UPDATE … FROM` are mutually incompatible, so we avoid both).
        DB::statement(
            'UPDATE image_metadata
             SET user_id = (
                SELECT users.id
                FROM users
                WHERE users.email = image_metadata.uploader_email
                LIMIT 1
             )
             WHERE user_id IS NULL AND uploader_email IS NOT NULL'
        );
    }

    public function down(): void
    {
        if (Schema::hasColumn('image_metadata', 'user_id')) {
            Schema::table('image_metadata', function (Blueprint $table): void {
                $table->dropIndex(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
