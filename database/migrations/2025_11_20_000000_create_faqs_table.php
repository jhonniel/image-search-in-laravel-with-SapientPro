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
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed a few default FAQs
        DB::table('faqs')->insert([
            [
                'question' => 'How does FindITFast work?',
                'answer' => 'Report lost or found items in minutes, match with potential owners through smart search, and connect securely to arrange returns.',
                'display_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Is FindITFast free to use?',
                'answer' => 'Yes! Anyone can post a lost or found item without fees. Optional sponsor support helps us keep the service running.',
                'display_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'How fast will I get notified?',
                'answer' => 'As soon as someone reports an item that matches your description, we send an email alert and app notification (if enabled).',
                'display_order' => 3,
                'is_active' => true,
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
        Schema::dropIfExists('faqs');
    }
};

