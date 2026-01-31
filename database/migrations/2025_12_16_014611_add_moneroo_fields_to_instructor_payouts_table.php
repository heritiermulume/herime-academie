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
        $tableName = Schema::hasTable('provider_payouts') ? 'provider_payouts' : (Schema::hasTable('instructor_payouts') ? 'instructor_payouts' : null);
        if (!$tableName) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            // Ajouter les champs Moneroo si ils n'existent pas déjà
            if (!Schema::hasColumn($tableName, 'moneroo_status')) {
                $table->string('moneroo_status')->nullable()->after('pawapay_status');
            }
            if (!Schema::hasColumn($tableName, 'moneroo_response')) {
                $table->json('moneroo_response')->nullable()->after('pawapay_response');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Schema::hasTable('provider_payouts') ? 'provider_payouts' : (Schema::hasTable('instructor_payouts') ? 'instructor_payouts' : null);
        if (!$tableName) {
            return;
        }
        if (!Schema::hasColumn($tableName, 'moneroo_status') && !Schema::hasColumn($tableName, 'moneroo_response')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $cols = [];
            if (Schema::hasColumn($tableName, 'moneroo_status')) $cols[] = 'moneroo_status';
            if (Schema::hasColumn($tableName, 'moneroo_response')) $cols[] = 'moneroo_response';
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
