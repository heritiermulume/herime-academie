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
        Schema::table('instructor_payouts', function (Blueprint $table) {
            // Ajouter les champs Moneroo si ils n'existent pas déjà
            if (!Schema::hasColumn('instructor_payouts', 'moneroo_status')) {
                $table->string('moneroo_status')->nullable()->after('pawapay_status');
            }
            if (!Schema::hasColumn('instructor_payouts', 'moneroo_response')) {
                $table->json('moneroo_response')->nullable()->after('pawapay_response');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instructor_payouts', function (Blueprint $table) {
            $table->dropColumn(['moneroo_status', 'moneroo_response']);
        });
    }
};
