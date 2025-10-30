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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'payment_provider')) {
                $table->string('payment_provider')->nullable()->after('payment_method');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'provider')) {
                $table->string('provider')->nullable()->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_provider')) {
                $table->dropColumn('payment_provider');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'provider')) {
                $table->dropColumn('provider');
            }
            if (Schema::hasColumn('payments', 'failure_reason')) {
                $table->dropColumn('failure_reason');
            }
        });
    }
};
