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
        // Pour SQLite, nous devons recréer la table pour modifier la contrainte CHECK
        Schema::create('orders_temp', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency')->default('USD');
            $table->enum('status', ['pending', 'confirmed', 'paid', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_id')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->text('order_items')->nullable();
            $table->text('billing_info')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
        });

        // Copier les données de l'ancienne table vers la nouvelle
        // Aligner explicitement les colonnes (certaines n'existent pas encore dans l'ancienne table)
        DB::statement('
            INSERT INTO orders_temp (
                id, order_number, user_id, affiliate_id, coupon_id,
                subtotal, discount, tax, total, currency,
                status, payment_id, billing_address, notes,
                created_at, updated_at,
                order_items, billing_info, payment_reference,
                confirmed_at, paid_at, completed_at,
                total_amount, payment_method
            )
            SELECT 
                id, order_number, user_id, affiliate_id, coupon_id,
                subtotal, discount, tax, total, currency,
                status, payment_id, billing_address, notes,
                created_at, updated_at,
                NULL as order_items, NULL as billing_info, NULL as payment_reference,
                NULL as confirmed_at, NULL as paid_at, NULL as completed_at,
                NULL as total_amount, payment_method
            FROM orders
        ');

        // Supprimer l'ancienne table
        Schema::dropIfExists('orders');

        // Renommer la nouvelle table
        Schema::rename('orders_temp', 'orders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancienne contrainte
        Schema::create('orders_temp', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('affiliate_id')->nullable();
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('currency')->default('USD');
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_id')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->text('order_items')->nullable();
            $table->text('billing_info')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
        });

        // Copier les données en convertissant les statuts non supportés
        DB::statement('
            INSERT INTO orders_temp 
            SELECT 
                id, order_number, user_id, affiliate_id, coupon_id, 
                subtotal, discount, tax, total, currency,
                CASE 
                    WHEN status = "confirmed" OR status = "completed" THEN "paid"
                    ELSE status 
                END as status,
                payment_id, billing_address, notes, created_at, updated_at,
                order_items, billing_info, payment_reference, confirmed_at, paid_at, completed_at,
                total_amount, payment_method
            FROM orders
        ');

        Schema::dropIfExists('orders');
        Schema::rename('orders_temp', 'orders');
    }
};
