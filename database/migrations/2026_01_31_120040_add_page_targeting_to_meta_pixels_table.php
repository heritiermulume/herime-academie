<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_pixels', function (Blueprint $table) {
            $table->string('match_route_name')->nullable()->index();
            $table->string('match_path_pattern')->nullable()->index();

            $table->json('excluded_route_names')->nullable();
            $table->json('excluded_path_patterns')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('meta_pixels', function (Blueprint $table) {
            $table->dropColumn([
                'match_route_name',
                'match_path_pattern',
                'excluded_route_names',
                'excluded_path_patterns',
            ]);
        });
    }
};

