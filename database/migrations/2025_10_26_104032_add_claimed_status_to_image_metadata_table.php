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
            $table->boolean('is_claimed')->default(false)->after('status');
            $table->string('claimed_by_email')->nullable()->after('is_claimed');
            $table->timestamp('claimed_at')->nullable()->after('claimed_by_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_metadata', function (Blueprint $table) {
            $table->dropColumn(['is_claimed', 'claimed_by_email', 'claimed_at']);
        });
    }
};
