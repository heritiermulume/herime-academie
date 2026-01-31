<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safety: if any guest reviews exist, remove them (we no longer allow guest reviews)
        DB::table('reviews')->whereNull('user_id')->delete();

        Schema::table('reviews', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
                // ignore if missing
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE reviews MODIFY user_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reviews ALTER COLUMN user_id SET NOT NULL');
        } elseif ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::rename('reviews', 'reviews__old');

            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('content_id');
                $table->integer('rating');
                $table->text('comment')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'content_id']);
            });

            DB::statement('INSERT INTO reviews (id, user_id, content_id, rating, comment, is_approved, created_at, updated_at) SELECT id, user_id, content_id, rating, comment, is_approved, created_at, updated_at FROM reviews__old WHERE user_id IS NOT NULL');
            Schema::drop('reviews__old');

            Schema::enableForeignKeyConstraints();
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Re-allow nullable user_id (rollback path)
        Schema::table('reviews', function (Blueprint $table) {
            try {
                $table->dropForeign(['user_id']);
            } catch (\Throwable $e) {
                // ignore if missing
            }
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE reviews MODIFY user_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE reviews ALTER COLUMN user_id DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();

            Schema::rename('reviews', 'reviews__old');

            Schema::create('reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('content_id');
                $table->integer('rating');
                $table->text('comment')->nullable();
                $table->boolean('is_approved')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'content_id']);
            });

            DB::statement('INSERT INTO reviews (id, user_id, content_id, rating, comment, is_approved, created_at, updated_at) SELECT id, user_id, content_id, rating, comment, is_approved, created_at, updated_at FROM reviews__old');
            Schema::drop('reviews__old');

            Schema::enableForeignKeyConstraints();
        }

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};

