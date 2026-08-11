<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('image_metadata', function (Blueprint $table) {
            $table->timestamp('images_purged_at')->nullable()->after('claim_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('image_metadata', function (Blueprint $table) {
            $table->dropColumn('images_purged_at');
        });
    }
};
