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

        DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN plan_type ENUM('recurring', 'one_time', 'freemium', 'premium') NOT NULL DEFAULT 'recurring'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE subscription_plans MODIFY COLUMN plan_type ENUM('recurring', 'one_time', 'freemium') NOT NULL DEFAULT 'recurring'");
    }
};
