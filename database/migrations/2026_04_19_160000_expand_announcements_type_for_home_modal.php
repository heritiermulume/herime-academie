<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE announcements MODIFY COLUMN type ENUM('info', 'success', 'warning', 'error', 'home_modal') NOT NULL DEFAULT 'info'");

            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteAnnouncementsWithoutTypeCheck();

            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('type', 32)->default('info')->change();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::table('announcements')->where('type', 'home_modal')->update(['type' => 'info']);
            DB::statement("ALTER TABLE announcements MODIFY COLUMN type ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info'");

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement('UPDATE announcements SET type = "info" WHERE type NOT IN ("info","success","warning","error")');
            $this->rebuildSqliteAnnouncementsWithLegacyTypeCheck();

            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('type', 32)->default('info')->change();
        });
    }

    /**
     * SQLite impose un CHECK sur les enums Laravel : on recrée la table sans CHECK pour autoriser home_modal.
     */
    private function rebuildSqliteAnnouncementsWithoutTypeCheck(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('CREATE TABLE announcements__new (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            title VARCHAR NOT NULL,
            content TEXT NOT NULL,
            image VARCHAR,
            button_text VARCHAR,
            button_url VARCHAR,
            type VARCHAR(32) NOT NULL DEFAULT \'info\',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            starts_at DATETIME,
            expires_at DATETIME,
            created_at DATETIME,
            updated_at DATETIME
        )');

        DB::statement('INSERT INTO announcements__new (id, title, content, image, button_text, button_url, type, is_active, starts_at, expires_at, created_at, updated_at)
            SELECT id, title, content, image, button_text, button_url, type, is_active, starts_at, expires_at, created_at, updated_at FROM announcements');

        DB::statement('DROP TABLE announcements');
        DB::statement('ALTER TABLE announcements__new RENAME TO announcements');

        Schema::enableForeignKeyConstraints();
    }

    private function rebuildSqliteAnnouncementsWithLegacyTypeCheck(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::statement('CREATE TABLE announcements__new (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            title VARCHAR NOT NULL,
            content TEXT NOT NULL,
            image VARCHAR,
            button_text VARCHAR,
            button_url VARCHAR,
            type VARCHAR CHECK ("type" IN (\'info\', \'success\', \'warning\', \'error\')) NOT NULL DEFAULT \'info\',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            starts_at DATETIME,
            expires_at DATETIME,
            created_at DATETIME,
            updated_at DATETIME
        )');

        DB::statement('INSERT INTO announcements__new (id, title, content, image, button_text, button_url, type, is_active, starts_at, expires_at, created_at, updated_at)
            SELECT id, title, content, image, button_text, button_url, type, is_active, starts_at, expires_at, created_at, updated_at FROM announcements');

        DB::statement('DROP TABLE announcements');
        DB::statement('ALTER TABLE announcements__new RENAME TO announcements');

        Schema::enableForeignKeyConstraints();
    }
};
