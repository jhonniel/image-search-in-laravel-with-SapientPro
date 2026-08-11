<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_reports', function (Blueprint $table) {
            $table->text('appeal_message')->nullable()->after('status');
            $table->timestamp('appealed_at')->nullable()->after('appeal_message');
        });

        Schema::table('user_reports', function (Blueprint $table) {
            $table->text('appeal_message')->nullable()->after('status');
            $table->timestamp('appealed_at')->nullable()->after('appeal_message');
        });
    }

    public function down(): void
    {
        Schema::table('item_reports', function (Blueprint $table) {
            $table->dropColumn(['appeal_message', 'appealed_at']);
        });

        Schema::table('user_reports', function (Blueprint $table) {
            $table->dropColumn(['appeal_message', 'appealed_at']);
        });
    }
};
