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
        Schema::table('ambassador_applications', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('marketing_ideas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ambassador_applications', function (Blueprint $table) {
            $table->dropColumn('document_path');
        });
    }
};
