<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        \App\Models\User::create([
            'name' => 'Admin Herime',
            'email' => 'admin@herimeacademie.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_verified' => true,
            'bio' => 'Administrateur de la plateforme Herime Academie',
            'phone' => '+243824449218',
        ]);

        // Instructors
        $instructors = [
            [
                'name' => 'Jean-Pierre Mbuyi',
                'email' => 'jp.mbuyi@herimeacademie.com',
                'password' => bcrypt('password'),
                'role' => 'instructor',
                'is_verified' => true,
                'bio' => 'Développeur Full-Stack avec 10 ans d\'expérience. Expert en Laravel, React et Vue.js.',
                'phone' => '+243999888777',
                'website' => 'https://jpmbuyi.dev',
                'linkedin' => 'https://linkedin.com/in/jpmbuyi',
            ],
            [
                'name' => 'Marie Kabila',
                'email' => 'marie.kabila@herimeacademie.com',
                'password' => bcrypt('password'),
                'role' => 'instructor',
                'is_verified' => true,
                'bio' => 'Designer UX/UI et spécialiste en marketing digital. Fondatrice de Design Studio Kinshasa.',
                'phone' => '+243888777666',
                'website' => 'https://designstudiokinshasa.com',
                'linkedin' => 'https://linkedin.com/in/mariekabila',
            ],
            [
                'name' => 'Dr. Patrick Lumumba',
                'email' => 'p.lumumba@herimeacademie.com',
                'password' => bcrypt('password'),
                'role' => 'instructor',
                'is_verified' => true,
                'bio' => 'Expert en business et entrepreneuriat. Coach certifié et consultant en stratégie d\'entreprise.',
                'phone' => '+243777666555',
                'linkedin' => 'https://linkedin.com/in/plumumba',
            ],
        ];

        foreach ($instructors as $instructor) {
            \App\Models\User::create($instructor);
        }

        // Students
        for ($i = 1; $i <= 20; $i++) {
            \App\Models\User::create([
                'name' => 'Étudiant ' . $i,
                'email' => 'etudiant' . $i . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'student',
                'is_verified' => true,
                'bio' => 'Étudiant passionné d\'apprentissage en ligne',
                'phone' => '+243' . rand(800000000, 999999999),
            ]);
        }

        // Affiliates
        for ($i = 1; $i <= 5; $i++) {
            \App\Models\User::create([
                'name' => 'Affilié ' . $i,
                'email' => 'affilie' . $i . '@example.com',
                'password' => bcrypt('password'),
                'role' => 'affiliate',
                'is_verified' => true,
                'bio' => 'Partenaire affilié de Herime Academie',
                'phone' => '+243' . rand(800000000, 999999999),
            ]);
        }
    }
}
