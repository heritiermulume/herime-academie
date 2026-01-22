<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Renomme les colonnes pawapay_* en moneroo_* selon la documentation Moneroo
     */
    public function up(): void
    {
        // Utiliser DB::statement pour renommer les colonnes (compatible avec MySQL/PostgreSQL)
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL
            if (Schema::hasColumn('users', 'pawapay_phone')) {
                DB::statement('ALTER TABLE users CHANGE pawapay_phone moneroo_phone VARCHAR(255) NULL');
            }
            if (Schema::hasColumn('users', 'pawapay_provider')) {
                DB::statement('ALTER TABLE users CHANGE pawapay_provider moneroo_provider VARCHAR(255) NULL');
            }
            if (Schema::hasColumn('users', 'pawapay_country')) {
                DB::statement('ALTER TABLE users CHANGE pawapay_country moneroo_country VARCHAR(2) NULL');
            }
            if (Schema::hasColumn('users', 'pawapay_currency')) {
                DB::statement('ALTER TABLE users CHANGE pawapay_currency moneroo_currency VARCHAR(3) NULL');
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL
            if (Schema::hasColumn('users', 'pawapay_phone')) {
                DB::statement('ALTER TABLE users RENAME COLUMN pawapay_phone TO moneroo_phone');
            }
            if (Schema::hasColumn('users', 'pawapay_provider')) {
                DB::statement('ALTER TABLE users RENAME COLUMN pawapay_provider TO moneroo_provider');
            }
            if (Schema::hasColumn('users', 'pawapay_country')) {
                DB::statement('ALTER TABLE users RENAME COLUMN pawapay_country TO moneroo_country');
            }
            if (Schema::hasColumn('users', 'pawapay_currency')) {
                DB::statement('ALTER TABLE users RENAME COLUMN pawapay_currency TO moneroo_currency');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL
            if (Schema::hasColumn('users', 'moneroo_phone')) {
                DB::statement('ALTER TABLE users CHANGE moneroo_phone pawapay_phone VARCHAR(255) NULL');
            }
            if (Schema::hasColumn('users', 'moneroo_provider')) {
                DB::statement('ALTER TABLE users CHANGE moneroo_provider pawapay_provider VARCHAR(255) NULL');
            }
            if (Schema::hasColumn('users', 'moneroo_country')) {
                DB::statement('ALTER TABLE users CHANGE moneroo_country pawapay_country VARCHAR(2) NULL');
            }
            if (Schema::hasColumn('users', 'moneroo_currency')) {
                DB::statement('ALTER TABLE users CHANGE moneroo_currency pawapay_currency VARCHAR(3) NULL');
            }
        } elseif ($driver === 'pgsql') {
            // PostgreSQL
            if (Schema::hasColumn('users', 'moneroo_phone')) {
                DB::statement('ALTER TABLE users RENAME COLUMN moneroo_phone TO pawapay_phone');
            }
            if (Schema::hasColumn('users', 'moneroo_provider')) {
                DB::statement('ALTER TABLE users RENAME COLUMN moneroo_provider TO pawapay_provider');
            }
            if (Schema::hasColumn('users', 'moneroo_country')) {
                DB::statement('ALTER TABLE users RENAME COLUMN moneroo_country TO pawapay_country');
            }
            if (Schema::hasColumn('users', 'moneroo_currency')) {
                DB::statement('ALTER TABLE users RENAME COLUMN moneroo_currency TO pawapay_currency');
            }
        }
    }
};
