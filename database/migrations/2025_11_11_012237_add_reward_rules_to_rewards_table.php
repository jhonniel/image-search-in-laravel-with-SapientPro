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
        Schema::table('rewards', function (Blueprint $table) {
            $table->boolean('is_auto_assign')->default(false)->after('status');
            $table->integer('min_reports')->nullable()->after('is_auto_assign'); // Minimum reports to earn
            $table->integer('min_claims')->nullable()->after('min_reports'); // Minimum claims to earn
            $table->integer('min_found_items')->nullable()->after('min_claims'); // Minimum found items to earn
            $table->integer('min_lost_items')->nullable()->after('min_found_items'); // Minimum lost items to earn
            $table->text('rule_description')->nullable()->after('min_lost_items'); // Description of the rule
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn([
                'is_auto_assign',
                'min_reports',
                'min_claims',
                'min_found_items',
                'min_lost_items',
                'rule_description'
            ]);
        });
    }
};
