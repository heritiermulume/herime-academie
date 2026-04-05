<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('access_granted_by_subscription_id')
                ->nullable()
                ->after('order_id')
                ->constrained('user_subscriptions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('access_granted_by_subscription_id');
        });
    }
};
