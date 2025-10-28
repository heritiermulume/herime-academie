<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Convertir les images en base64 si elles existent dans public/images/hero
        $getImageBase64 = function($imagePath) {
            $fullPath = public_path($imagePath);
            if (file_exists($fullPath)) {
                $imageData = file_get_contents($fullPath);
                $mimeType = mime_content_type($fullPath);
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
            // Si l'image n'existe pas, télécharger depuis Unsplash
            return null;
        };

        $banners = [
            [
                'title' => 'Apprenez sans limites avec Herime Académie',
                'subtitle' => 'Découvrez des milliers de cours en ligne de qualité, créés par des experts.',
                'image' => $getImageBase64('images/hero/banner-1.jpg'),
                'mobile_image' => null,
                'button1_text' => 'Commencer',
                'button1_url' => '/courses',
                'button1_style' => 'warning',
                'button2_text' => 'Explorer',
                'button2_url' => '#categories',
                'button2_style' => 'outline-light',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Développez vos compétences professionnelles',
                'subtitle' => 'Formations professionnelles certifiantes à votre rythme.',
                'image' => $getImageBase64('images/hero/banner-2.jpg'),
                'mobile_image' => null,
                'button1_text' => 'Voir les cours',
                'button1_url' => '/courses',
                'button1_style' => 'warning',
                'button2_text' => 'En savoir plus',
                'button2_url' => '/about',
                'button2_style' => 'outline-light',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Formez-vous avec les meilleurs',
                'subtitle' => 'Des cours pratiques et concrets pour votre réussite.',
                'image' => $getImageBase64('images/hero/banner-3.jpg'),
                'mobile_image' => null,
                'button1_text' => 'Nos instructeurs',
                'button1_url' => '/instructors',
                'button1_style' => 'warning',
                'button2_text' => 'Nos cours',
                'button2_url' => '/courses',
                'button2_style' => 'outline-light',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            if ($banner['image']) { // Créer uniquement si l'image existe
                Banner::create($banner);
            }
        }
    }
}
