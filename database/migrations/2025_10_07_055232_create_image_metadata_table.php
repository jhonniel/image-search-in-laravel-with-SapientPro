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
        Schema::create('image_metadata', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique(); // The stored filename in reference-images directory
            $table->string('original_name'); // Original filename when uploaded
            $table->text('description')->nullable(); // User-provided description
            $table->json('tags')->nullable(); // JSON array of tags
            $table->string('upload_id')->nullable(); // Upload session identifier
            $table->integer('file_size')->nullable(); // File size in bytes
            $table->string('mime_type')->nullable(); // MIME type of the image
            $table->timestamps();

            // Indexes for better query performance
            $table->index('filename');
            $table->index('original_name');
            $table->index('upload_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_metadata');
    }
};
