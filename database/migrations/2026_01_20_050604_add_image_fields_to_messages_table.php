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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('message');
            $table->enum('view_option', ['once', 'twice', 'keep'])->nullable()->after('image_path');
            $table->integer('view_count')->default(0)->after('view_option');
            $table->boolean('is_expired')->default(false)->after('view_count');
        });
        
        // Create message_image_views table to track who viewed the image
        Schema::create('message_image_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained('messages')->onDelete('cascade');
            $table->foreignId('viewer_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            $table->unique(['message_id', 'viewer_id']);
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_image_views');
        
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'view_option', 'view_count', 'is_expired']);
        });
    }
};
