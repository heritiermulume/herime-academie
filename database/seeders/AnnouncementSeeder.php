<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $announcements = [
            [
                'title' => 'Nouvelle session de formation Laravel',
                'content' => 'Rejoignez notre nouvelle session de formation Laravel qui commence le 15 janvier. Apprenez le développement web moderne avec des experts.',
                'button_text' => 'S\'inscrire maintenant',
                'button_url' => '#',
                'type' => 'info',
                'is_active' => true,
            ],
            [
                'title' => 'Réduction de 50% sur tous les cours',
                'content' => 'Profitez de notre promotion exceptionnelle : 50% de réduction sur tous les cours jusqu\'à la fin du mois !',
                'button_text' => 'Voir les offres',
                'button_url' => '#',
                'type' => 'success',
                'is_active' => true,
            ],
            [
                'title' => 'Certification professionnelle disponible',
                'content' => 'Obtenez votre certification professionnelle reconnue dans votre domaine. Nos cours sont maintenant certifiants !',
                'button_text' => 'En savoir plus',
                'button_url' => '#',
                'type' => 'warning',
                'is_active' => true,
            ],
        ];

        foreach ($announcements as $announcement) {
            \App\Models\Announcement::updateOrCreate(
                ['title' => $announcement['title']],
                $announcement
            );
        }
    }
}
