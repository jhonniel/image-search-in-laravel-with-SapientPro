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
            if (!Schema::hasColumn('image_metadata', 'province')) {
                // Add province after city if city exists, otherwise after location
                if (Schema::hasColumn('image_metadata', 'city')) {
                    $table->string('province')->nullable()->after('city');
                } else {
                    $table->string('province')->nullable()->after('location');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_metadata', function (Blueprint $table) {
            $table->dropColumn('province');
        });
    }
};
