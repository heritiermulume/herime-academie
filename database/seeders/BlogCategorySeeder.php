<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'E-learning',
                'slug' => 'e-learning',
                'description' => 'Articles sur l\'apprentissage en ligne et les tendances du e-learning',
                'color' => '#003366',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Technologie',
                'slug' => 'technologie',
                'description' => 'Actualités et articles sur les nouvelles technologies',
                'color' => '#28a745',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Formation',
                'slug' => 'formation',
                'description' => 'Conseils et astuces pour la formation professionnelle',
                'color' => '#ffc107',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Développement',
                'slug' => 'developpement',
                'description' => 'Tutoriels et guides de développement',
                'color' => '#dc3545',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'Articles sur l\'entrepreneuriat et le business',
                'color' => '#6f42c1',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            BlogCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}