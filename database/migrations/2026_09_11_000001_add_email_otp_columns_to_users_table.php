<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'email_otp_hash')) {
                $table->string('email_otp_hash', 255)->nullable()->after('email_verified_at');
            }
            if (! Schema::hasColumn('users', 'email_otp_expires_at')) {
                $table->timestamp('email_otp_expires_at')->nullable()->after('email_otp_hash');
            }
            if (! Schema::hasColumn('users', 'email_otp_attempts')) {
                $table->unsignedTinyInteger('email_otp_attempts')->default(0)->after('email_otp_expires_at');
            }
            if (! Schema::hasColumn('users', 'email_otp_sent_at')) {
                $table->timestamp('email_otp_sent_at')->nullable()->after('email_otp_attempts');
            }
        });

        // Existing accounts were created before OTP signup — treat them as already verified.
        \Illuminate\Support\Facades\DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = ['email_otp_hash', 'email_otp_expires_at', 'email_otp_attempts', 'email_otp_sent_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
