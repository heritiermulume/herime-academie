<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * error_message était en VARCHAR(255), les erreurs SMTP complètes dépassent cette taille.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sent_emails MODIFY error_message TEXT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sent_emails ALTER COLUMN error_message TYPE TEXT');
        }
        // SQLite : TEXT par défaut, pas de modification nécessaire
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE sent_emails MODIFY error_message VARCHAR(255) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE sent_emails ALTER COLUMN error_message TYPE VARCHAR(255)');
        }
    }
};
