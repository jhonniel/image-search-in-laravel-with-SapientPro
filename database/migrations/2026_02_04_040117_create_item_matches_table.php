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
        Schema::create('item_matches', function (Blueprint $table) {
            $table->id();
            $table->string('user_item_upload_id'); // The user's item upload_id
            $table->string('matched_item_upload_id'); // The matched item's upload_id
            $table->string('user_email'); // User who owns the first item
            $table->string('matched_item_owner_email'); // Owner of the matched item
            $table->string('user_item_status'); // 'lost' or 'found'
            $table->string('matched_item_status'); // 'lost' or 'found'
            $table->decimal('similarity_score', 5, 4)->default(0); // Overall similarity (0.0000 to 1.0000)
            $table->decimal('visual_similarity', 5, 4)->default(0); // Visual similarity
            $table->decimal('text_similarity', 5, 4)->default(0); // Text similarity
            $table->boolean('is_notified')->default(false); // Whether notification was sent
            $table->timestamps();
            
            // Indexes for fast lookups
            $table->index('user_item_upload_id');
            $table->index('matched_item_upload_id');
            $table->index('user_email');
            $table->index('matched_item_owner_email');
            $table->index(['user_email', 'user_item_upload_id']);
            
            // Unique constraint to prevent duplicate matches
            $table->unique(['user_item_upload_id', 'matched_item_upload_id'], 'unique_item_match');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_matches');
    }
};
