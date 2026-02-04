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
        if (!Schema::hasTable('rewards')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so rebuild table
            $this->rebuildRewardsTableForSqlite(true);
            return;
        }

        // For PostgreSQL and MySQL, use standard ALTER COLUMN
        if ($driver === 'pgsql') {
            // PostgreSQL: Drop foreign key, alter column, recreate foreign key
            DB::statement('ALTER TABLE rewards DROP CONSTRAINT IF EXISTS rewards_user_id_foreign');
            DB::statement('ALTER TABLE rewards ALTER COLUMN user_id DROP NOT NULL');
            DB::statement('ALTER TABLE rewards ADD CONSTRAINT rewards_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        } else {
            // MySQL/MariaDB: Use Laravel's change() method
            Schema::table('rewards', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('rewards', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });

            Schema::table('rewards', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('rewards')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so rebuild table
            $this->rebuildRewardsTableForSqlite(false);
            return;
        }

        // For PostgreSQL and MySQL, use standard ALTER COLUMN
        if ($driver === 'pgsql') {
            // PostgreSQL: Drop foreign key, alter column, recreate foreign key
            DB::statement('ALTER TABLE rewards DROP CONSTRAINT IF EXISTS rewards_user_id_foreign');
            DB::statement('ALTER TABLE rewards ALTER COLUMN user_id SET NOT NULL');
            DB::statement('ALTER TABLE rewards ADD CONSTRAINT rewards_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE');
        } else {
            // MySQL/MariaDB: Use Laravel's change() method
            Schema::table('rewards', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });

            Schema::table('rewards', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });

            Schema::table('rewards', function (Blueprint $table) {
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Rebuild rewards table for SQLite to toggle nullable constraint.
     */
    protected function rebuildRewardsTableForSqlite(bool $makeNullable): void
    {
        Schema::create('rewards_temp', function (Blueprint $table) use ($makeNullable) {
            $table->id();
            $userColumn = $table->foreignId('user_id')
                ->nullable($makeNullable)
                ->constrained()
                ->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->string('type')->default('discount');
            $table->decimal('value', 10, 2)->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_used')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_auto_assign')->default(false);
            $table->integer('min_reports')->nullable();
            $table->integer('min_claims')->nullable();
            $table->integer('min_found_items')->nullable();
            $table->integer('min_lost_items')->nullable();
            $table->text('rule_description')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });

        $columns = 'id, user_id, title, description, code, type, value, expires_at, is_used, used_at, status, is_auto_assign, min_reports, min_claims, min_found_items, min_lost_items, rule_description, created_at, updated_at';

        $insertQuery = sprintf('INSERT INTO rewards_temp (%s) SELECT %s FROM rewards', $columns, $columns);

        if (!$makeNullable) {
            $insertQuery .= ' WHERE user_id IS NOT NULL';
        }

        DB::statement('PRAGMA foreign_keys=OFF;');
        DB::statement($insertQuery);
        Schema::drop('rewards');
        Schema::rename('rewards_temp', 'rewards');
        DB::statement('PRAGMA foreign_keys=ON;');
    }
};

