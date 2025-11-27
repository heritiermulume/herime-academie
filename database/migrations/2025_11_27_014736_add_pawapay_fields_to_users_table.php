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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_external_instructor')->default(false)->after('role');
            $table->string('pawapay_phone')->nullable()->after('is_external_instructor');
            $table->string('pawapay_provider')->nullable()->after('pawapay_phone');
            $table->string('pawapay_country', 3)->nullable()->after('pawapay_provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_external_instructor',
                'pawapay_phone',
                'pawapay_provider',
                'pawapay_country',
            ]);
        });
    }
};
