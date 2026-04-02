<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE subscription_plans MODIFY billing_period ENUM('monthly', 'yearly', 'semiannual', 'quarterly') NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE subscription_plans MODIFY billing_period ENUM('monthly', 'yearly', 'semiannual') NULL");
    }
};
