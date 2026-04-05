<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        if ($driver === 'sqlite') {
            // Laravel `enum` sur SQLite ajoute un CHECK qui n’inclut pas `pending_payment`.
            // Renommer la table parente casse les FK de `subscription_invoices` (référence résiduelle vers *_old) :
            // on vide les factures, on supprime et recrée `user_subscriptions` avec `string` (usage SQLite = dev/tests).
            Schema::disableForeignKeyConstraints();
            DB::table('subscription_invoices')->delete();
            Schema::dropIfExists('user_subscriptions');
            Schema::create('user_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
                $table->string('status')->default('active');
                $table->dateTime('starts_at');
                $table->dateTime('trial_ends_at')->nullable();
                $table->dateTime('current_period_starts_at')->nullable();
                $table->dateTime('current_period_ends_at')->nullable();
                $table->dateTime('cancelled_at')->nullable();
                $table->dateTime('ended_at')->nullable();
                $table->boolean('auto_renew')->default(true);
                $table->string('payment_method')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
            Schema::enableForeignKeyConstraints();
        }
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

        if ($driver === 'sqlite') {
            Schema::disableForeignKeyConstraints();
            DB::table('subscription_invoices')->delete();
            Schema::dropIfExists('user_subscriptions');
            Schema::create('user_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
                $table->enum('status', ['trialing', 'active', 'past_due', 'cancelled', 'expired'])->default('active');
                $table->dateTime('starts_at');
                $table->dateTime('trial_ends_at')->nullable();
                $table->dateTime('current_period_starts_at')->nullable();
                $table->dateTime('current_period_ends_at')->nullable();
                $table->dateTime('cancelled_at')->nullable();
                $table->dateTime('ended_at')->nullable();
                $table->boolean('auto_renew')->default(true);
                $table->string('payment_method')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
            Schema::enableForeignKeyConstraints();
        }
    }
};
