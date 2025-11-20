<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('pending'); // pending, in_progress, resolved
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('contact_help_sections', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->text('body');
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        DB::table('contact_help_sections')->insert([
            [
                'heading' => 'Need help or have feedback?',
                'body' => 'Share your questions, partnership ideas, or product feedback. Our team reviews every request within one business day.',
                'cta_label' => 'Documentation',
                'cta_url' => '#',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_help_sections');
        Schema::dropIfExists('contact_requests');
    }
};

