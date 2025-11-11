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
            $table->enum('claim_verification_status', ['pending', 'verified', 'rejected'])->nullable()->after('claimed_at');
            $table->timestamp('claim_verified_at')->nullable()->after('claim_verification_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_metadata', function (Blueprint $table) {
            $table->dropColumn(['claim_verification_status', 'claim_verified_at']);
        });
    }
};
