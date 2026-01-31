<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_pixels', function (Blueprint $table) {
            $table->integer('priority')->default(0)->index();
        });

        Schema::table('meta_event_triggers', function (Blueprint $table) {
            $table->integer('priority')->default(0)->index();
        });
    }

    public function down(): void
    {
        Schema::table('meta_pixels', function (Blueprint $table) {
            $table->dropColumn('priority');
        });

        Schema::table('meta_event_triggers', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};

