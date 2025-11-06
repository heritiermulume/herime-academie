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
        // Modifier l'enum pour ajouter 'super_user'
        // MySQL ne permet pas de modifier directement un ENUM, donc on doit utiliser ALTER TABLE
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin', 'affiliate', 'super_user') DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre l'enum sans super_user
        // Note: Si des utilisateurs ont le rôle super_user, il faudra les convertir en admin d'abord
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin', 'affiliate') DEFAULT 'student'");
    }
};

