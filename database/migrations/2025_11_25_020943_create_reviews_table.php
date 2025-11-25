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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('review_question_id')->constrained('review_questions')->onDelete('cascade');
            $table->integer('rating')->nullable(); // For rating questions (1-5)
            $table->text('answer')->nullable(); // For text questions
            $table->timestamps();
            
            // Ensure a user can only submit one answer per question
            $table->unique(['user_id', 'review_question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
