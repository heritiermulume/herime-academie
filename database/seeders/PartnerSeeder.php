<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partners = [
            [
                'name' => 'Microsoft',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg',
                'website' => 'https://microsoft.com',
                'description' => 'Partenaire technologique officiel',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Google',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg',
                'website' => 'https://google.com',
                'description' => 'Partenaire éducatif',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'IBM',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg',
                'website' => 'https://ibm.com',
                'description' => 'Partenaire en intelligence artificielle',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Oracle',
                'logo' => 'https://upload.wikimedia.org/wikipedia/commons/5/50/Oracle_logo.svg',
                'website' => 'https://oracle.com',
                'description' => 'Partenaire en bases de données',
                'is_active' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($partners as $partner) {
            \App\Models\Partner::create($partner);
        }
    }
}
