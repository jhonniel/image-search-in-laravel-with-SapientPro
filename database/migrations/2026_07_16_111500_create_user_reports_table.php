<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('upload_id')->nullable()->index();
            $table->string('label', 50);
            $table->text('explanation');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();

            $table->unique(['reporter_user_id', 'reported_user_id', 'upload_id'], 'user_reports_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
