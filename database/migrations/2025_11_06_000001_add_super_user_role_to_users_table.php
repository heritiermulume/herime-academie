<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Modifier l'enum pour ajouter 'super_user'
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin', 'affiliate', 'super_user') DEFAULT 'student'");
        } else {
            // Pour SQLite / autres pilotes utilisés lors des tests, aucune modification nécessaire :
            // la colonne est généralement stockée en VARCHAR. On s'assure néanmoins que la colonne existe.
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role')->default('student');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            // Remettre l'enum sans super_user
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin', 'affiliate') DEFAULT 'student'");
        } else {
            // Aucun retour spécifique requis pour SQLite / autres pilotes de tests
        }
    }
};

