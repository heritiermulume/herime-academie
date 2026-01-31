<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->boolean('is_in_person_program')
                ->default(false)
                ->after('is_downloadable');

            $table->string('whatsapp_number', 30)
                ->nullable()
                ->after('is_in_person_program');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn(['is_in_person_program', 'whatsapp_number']);
        });
    }
};

