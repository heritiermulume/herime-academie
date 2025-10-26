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
        // Sous SQLite, DROP COLUMN n'est pas supporté simplement.
        // On ignore cette suppression pendant les tests SQLite.
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        if (Schema::hasColumn('cart_items', 'quantity')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->integer('quantity')->default(1);
        });
    }
};