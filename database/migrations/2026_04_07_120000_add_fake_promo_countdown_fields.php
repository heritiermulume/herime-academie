<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('use_fake_promo_countdown')
                ->default(false)
                ->after('sale_end_at');
            $table->unsignedInteger('fake_promo_duration_days')
                ->nullable()
                ->after('use_fake_promo_countdown');
        });

        Schema::table('content_packages', function (Blueprint $table) {
            $table->boolean('use_fake_promo_countdown')
                ->default(false)
                ->after('sale_end_at');
            $table->unsignedInteger('fake_promo_duration_days')
                ->nullable()
                ->after('use_fake_promo_countdown');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn([
                'use_fake_promo_countdown',
                'fake_promo_duration_days',
            ]);
        });

        Schema::table('content_packages', function (Blueprint $table) {
            $table->dropColumn([
                'use_fake_promo_countdown',
                'fake_promo_duration_days',
            ]);
        });
    }
};
