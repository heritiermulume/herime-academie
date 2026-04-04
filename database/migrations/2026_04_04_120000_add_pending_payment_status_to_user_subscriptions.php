<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM(
                'trialing',
                'active',
                'past_due',
                'cancelled',
                'expired',
                'pending_payment'
            ) NOT NULL DEFAULT 'active'");
        }
        // SQLite (tests) : colonne string, aucune contrainte ENUM à modifier.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("UPDATE user_subscriptions SET status = 'expired' WHERE status = 'pending_payment'");

            DB::statement("ALTER TABLE user_subscriptions MODIFY COLUMN status ENUM(
                'trialing',
                'active',
                'past_due',
                'cancelled',
                'expired'
            ) NOT NULL DEFAULT 'active'");
        }
    }
};
