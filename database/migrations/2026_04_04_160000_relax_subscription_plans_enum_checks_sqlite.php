<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sur SQLite, les colonnes enum() héritent d’un CHECK strict ; les migrations MySQL
     * qui étendent plan_type / billing_period ne s’y appliquent pas — les plans Membre
     * (quarterly, membre, etc.) échouaient alors à l’insertion.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            return;
        }

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('plan_type', 32)->default('recurring')->change();
            $table->string('billing_period', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Rétablir les CHECK SQLite d’origine impliquerait une recréation de table ;
        // on ne rollback pas ce relâchement sur SQLite.
    }
};
