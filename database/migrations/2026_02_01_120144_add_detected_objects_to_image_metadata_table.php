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
        Schema::table('image_metadata', function (Blueprint $table) {
            $table->json('detected_objects')->nullable()->after('tags');
            $table->index('detected_objects', 'idx_detected_objects');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_metadata', function (Blueprint $table) {
            $table->dropIndex('idx_detected_objects');
            $table->dropColumn('detected_objects');
        });
    }
};
