<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('cannot_post')->default(false)->after('role');
            $table->boolean('cannot_claim')->default(false)->after('cannot_post');
            $table->boolean('is_banned')->default(false)->after('cannot_claim');
            $table->timestamp('login_blocked_until')->nullable()->after('is_banned');
            $table->string('restriction_note')->nullable()->after('login_blocked_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'cannot_post',
                'cannot_claim',
                'is_banned',
                'login_blocked_until',
                'restriction_note',
            ]);
        });
    }
};
