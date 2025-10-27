<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $testimonials = [
            [
                'name' => 'Marie Kabila',
                'title' => 'Développeuse Full-Stack',
                'company' => 'Tech Solutions RDC',
                'photo' => 'https://i.pravatar.cc/300?img=1',
                'testimonial' => 'Herime Academie m\'a permis de maîtriser Laravel en seulement 3 mois. Les cours sont excellents et les formateurs très compétents.',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Jean-Pierre Mbuyi',
                'title' => 'Designer UX/UI',
                'company' => 'Creative Studio',
                'photo' => 'https://i.pravatar.cc/300?img=5',
                'testimonial' => 'Grâce aux cours de design UX/UI, j\'ai pu créer des interfaces utilisateur exceptionnelles et décrocher un poste de designer senior.',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Sarah Mukendi',
                'title' => 'Marketing Manager',
                'company' => 'Digital Agency',
                'photo' => 'https://i.pravatar.cc/300?img=3',
                'testimonial' => 'Les stratégies de marketing digital apprises sur Herime Academie ont transformé notre entreprise. ROI multiplié par 3 !',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Patrick Lumumba',
                'title' => 'Entrepreneur',
                'company' => 'Startup RDC',
                'photo' => 'https://i.pravatar.cc/300?img=4',
                'testimonial' => 'Les cours d\'entrepreneuriat m\'ont donné les outils nécessaires pour lancer ma startup avec succès.',
                'rating' => 4,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Grace Mwamba',
                'title' => 'Photographe',
                'company' => 'Studio Grace',
                'photo' => 'https://i.pravatar.cc/300?img=6',
                'testimonial' => 'Le cours de photographie professionnelle a révolutionné ma technique. Mes clients sont ravis de la qualité !',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'David Kabila',
                'title' => 'Chef d\'entreprise',
                'company' => 'Kabila Group',
                'photo' => 'https://i.pravatar.cc/300?img=8',
                'testimonial' => 'L\'anglais des affaires m\'a ouvert de nouvelles opportunités internationales. Formation de qualité exceptionnelle !',
                'rating' => 5,
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            \App\Models\Testimonial::updateOrCreate(
                ['name' => $testimonial['name'], 'company' => $testimonial['company']],
                $testimonial
            );
        }
    }
}
