<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Toggle global: permet d'activer/désactiver tout le tracking sans supprimer la config.
        DB::table('settings')->updateOrInsert(
            ['key' => 'meta_tracking_enabled'],
            [
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Activer le tracking Meta (Facebook Pixel) globalement',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'meta_tracking_enabled')->delete();
    }
};

