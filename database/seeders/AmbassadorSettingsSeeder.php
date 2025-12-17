<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class AmbassadorSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Taux de commission des ambassadeurs
        Setting::set(
            'ambassador_commission_rate',
            10.0,
            'number',
            'Pourcentage de commission pour les ambassadeurs (en %)'
        );

        $this->command->info('✅ Ambassador settings initialized successfully');
        $this->command->info('   - Commission rate: 10%');
    }
}

