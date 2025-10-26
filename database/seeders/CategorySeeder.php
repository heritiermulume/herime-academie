<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Développement Web',
                'slug' => 'developpement-web',
                'description' => 'Apprenez les technologies web modernes et créez des sites et applications web professionnels.',
                'icon' => 'fas fa-code',
                'color' => '#e74c3c',
                'sort_order' => 1,
            ],
            [
                'name' => 'Design Graphique',
                'slug' => 'design-graphique',
                'description' => 'Maîtrisez les outils de design et créez des visuels percutants.',
                'icon' => 'fas fa-paint-brush',
                'color' => '#9b59b6',
                'sort_order' => 2,
            ],
            [
                'name' => 'Marketing Digital',
                'slug' => 'marketing-digital',
                'description' => 'Découvrez les stratégies de marketing en ligne et boostez votre présence digitale.',
                'icon' => 'fas fa-bullhorn',
                'color' => '#f39c12',
                'sort_order' => 3,
            ],
            [
                'name' => 'Business & Entrepreneuriat',
                'slug' => 'business-entrepreneuriat',
                'description' => 'Développez vos compétences entrepreneuriales et gérez votre entreprise.',
                'icon' => 'fas fa-briefcase',
                'color' => '#2ecc71',
                'sort_order' => 4,
            ],
            [
                'name' => 'Langues',
                'slug' => 'langues',
                'description' => 'Apprenez de nouvelles langues et améliorez votre communication.',
                'icon' => 'fas fa-language',
                'color' => '#3498db',
                'sort_order' => 5,
            ],
            [
                'name' => 'Photographie',
                'slug' => 'photographie',
                'description' => 'Maîtrisez l\'art de la photographie et capturez des moments inoubliables.',
                'icon' => 'fas fa-camera',
                'color' => '#1abc9c',
                'sort_order' => 6,
            ],
            [
                'name' => 'Musique',
                'slug' => 'musique',
                'description' => 'Apprenez à jouer d\'un instrument ou développez vos compétences musicales.',
                'icon' => 'fas fa-music',
                'color' => '#e67e22',
                'sort_order' => 7,
            ],
            [
                'name' => 'Santé & Bien-être',
                'slug' => 'sante-bien-etre',
                'description' => 'Prenez soin de votre santé physique et mentale.',
                'icon' => 'fas fa-heart',
                'color' => '#e91e63',
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
