<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('requires_subscription')->default(false)->after('is_free');
            $table->string('required_subscription_tier', 30)->nullable()->after('requires_subscription');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['requires_subscription', 'required_subscription_tier']);
        });
    }
};

