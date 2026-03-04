<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajoute is_external_provider si absent (peut manquer si migrations appliquées dans un ordre différent).
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_external_provider')) {
            return;
        }

        // Si is_external_instructor existe, le renommer
        if (Schema::hasColumn('users', 'is_external_instructor')) {
            $driver = DB::getDriverName();
            if ($driver === 'mysql') {
                DB::statement('ALTER TABLE `users` CHANGE `is_external_instructor` `is_external_provider` TINYINT(1) NULL DEFAULT 0');
            } elseif ($driver === 'pgsql') {
                DB::statement('ALTER TABLE users RENAME COLUMN is_external_instructor TO is_external_provider');
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->renameColumn('is_external_instructor', 'is_external_provider');
                });
            }
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_external_provider')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_external_provider')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_external_provider');
        });
    }
};
