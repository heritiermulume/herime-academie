<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        $now = now();

        \App\Models\User::updateOrCreate(
            ['email' => 'admin@herimeacademie.com'],
            [
                'name' => 'Admin Herime',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_verified' => true,
                'is_active' => true,
                'bio' => 'Administrateur de la plateforme Herime Academie',
                'phone' => '+243824449218',
                'avatar' => 'https://i.pravatar.cc/300?img=68',
                'email_verified_at' => $now,
                'last_login_at' => $now,
                'sso_id' => 'sso-admin-1',
                'sso_provider' => 'herime',
                'sso_metadata' => [
                    'synced_at' => $now->toIso8601String(),
                    'source' => 'seeder',
                ],
                'preferences' => [],
            ]
        );

        // Providers
        $providers = [
            [
                'name' => 'Jean-Pierre Mbuyi',
                'email' => 'jp.mbuyi@herimeacademie.com',
                'password' => Hash::make('password'),
                'role' => 'provider',
                'is_verified' => true,
                'is_active' => true,
                'bio' => 'Développeur Full-Stack avec 10 ans d\'expérience. Expert en Laravel, React et Vue.js.',
                'phone' => '+243999888777',
                'website' => 'https://jpmbuyi.dev',
                'linkedin' => 'https://linkedin.com/in/jpmbuyi',
                'avatar' => 'https://i.pravatar.cc/300?img=5',
            ],
            [
                'name' => 'Marie Kabila',
                'email' => 'marie.kabila@herimeacademie.com',
                'password' => Hash::make('password'),
                'role' => 'provider',
                'is_verified' => true,
                'is_active' => true,
                'bio' => 'Designer UX/UI et spécialiste en marketing digital. Fondatrice de Design Studio Kinshasa.',
                'phone' => '+243888777666',
                'website' => 'https://designstudiokinshasa.com',
                'linkedin' => 'https://linkedin.com/in/mariekabila',
                'avatar' => 'https://i.pravatar.cc/300?img=47',
            ],
            [
                'name' => 'Dr. Patrick Lumumba',
                'email' => 'p.lumumba@herimeacademie.com',
                'password' => Hash::make('password'),
                'role' => 'provider',
                'is_verified' => true,
                'is_active' => true,
                'bio' => 'Expert en business et entrepreneuriat. Coach certifié et consultant en stratégie d\'entreprise.',
                'phone' => '+243777666555',
                'linkedin' => 'https://linkedin.com/in/plumumba',
                'avatar' => 'https://i.pravatar.cc/300?img=23',
            ],
        ];

        foreach ($providers as $provider) {
            \App\Models\User::updateOrCreate(
                ['email' => $provider['email']],
                array_merge($provider, [
                    'email_verified_at' => $now,
                    'last_login_at' => $now->copy()->subDays(rand(1, 10)),
                    'sso_id' => 'sso-provider-' . Str::slug($provider['name']),
                    'sso_provider' => 'herime',
                    'sso_metadata' => [
                        'synced_at' => $now->toIso8601String(),
                        'source' => 'seeder',
                    ],
                    'preferences' => [],
                ])
            );
        }

        // Customers
        for ($i = 1; $i <= 20; $i++) {
            $email = 'customer' . $i . '@example.com';
            \App\Models\User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Client ' . $i,
                    'password' => Hash::make('password'),
                    'role' => 'customer',
                    'is_verified' => true,
                    'is_active' => true,
                    'bio' => 'Client passionné d\'apprentissage en ligne',
                    'phone' => '+243' . rand(800000000, 999999999),
                    'avatar' => 'https://i.pravatar.cc/300?img=' . ($i % 70), // Images différentes pour chaque client
                    'email_verified_at' => $now,
                    'last_login_at' => $now->copy()->subDays(rand(1, 30)),
                    'sso_id' => 'sso-customer-' . $i,
                    'sso_provider' => 'herime',
                    'sso_metadata' => [
                        'synced_at' => $now->toIso8601String(),
                        'source' => 'seeder',
                    ],
                    'preferences' => [],
                ]
            );
        }

        // Affiliates
        for ($i = 1; $i <= 5; $i++) {
            $email = 'affilie' . $i . '@example.com';
            \App\Models\User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => 'Affilié ' . $i,
                    'password' => Hash::make('password'),
                    'role' => 'affiliate',
                    'is_verified' => true,
                    'is_active' => true,
                    'bio' => 'Partenaire affilié de Herime Academie',
                    'phone' => '+243' . rand(800000000, 999999999),
                    'avatar' => 'https://i.pravatar.cc/300?img=' . ($i + 20),
                    'email_verified_at' => $now,
                    'last_login_at' => $now->copy()->subDays(rand(1, 20)),
                    'sso_id' => 'sso-affiliate-' . $i,
                    'sso_provider' => 'herime',
                    'sso_metadata' => [
                        'synced_at' => $now->toIso8601String(),
                        'source' => 'seeder',
                    ],
                    'preferences' => [],
                ]
            );
        }
    }
}
