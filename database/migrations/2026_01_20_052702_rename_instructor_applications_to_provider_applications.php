<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Renomme la table instructor_applications en provider_applications
     */
    public function up(): void
    {
        if (Schema::hasTable('instructor_applications') && !Schema::hasTable('provider_applications')) {
            Schema::rename('instructor_applications', 'provider_applications');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('provider_applications') && !Schema::hasTable('instructor_applications')) {
            Schema::rename('provider_applications', 'instructor_applications');
        }
    }
};
