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
            // Champs supplémentaires utilisés par les commandes WhatsApp
            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable()->after('total');
            }
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_id');
            }
            if (!Schema::hasColumn('orders', 'billing_info')) {
                $table->json('billing_info')->nullable()->after('billing_address');
            }
            if (!Schema::hasColumn('orders', 'order_items')) {
                $table->json('order_items')->nullable()->after('billing_info');
            }
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('confirmed_at');
            }
            if (!Schema::hasColumn('orders', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('paid_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('orders', 'paid_at')) {
                $table->dropColumn('paid_at');
            }
            if (Schema::hasColumn('orders', 'confirmed_at')) {
                $table->dropColumn('confirmed_at');
            }
            if (Schema::hasColumn('orders', 'order_items')) {
                $table->dropColumn('order_items');
            }
            if (Schema::hasColumn('orders', 'billing_info')) {
                $table->dropColumn('billing_info');
            }
            if (Schema::hasColumn('orders', 'payment_reference')) {
                $table->dropColumn('payment_reference');
            }
            if (Schema::hasColumn('orders', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
        });
    }
};



