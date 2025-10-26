<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $instructors = \App\Models\User::instructors()->get();
        $categories = \App\Models\Category::all();

        $courses = [
            [
                'title' => 'Développement Web Complet avec Laravel',
                'description' => 'Apprenez le développement web moderne avec Laravel, le framework PHP le plus populaire. Ce cours complet vous enseignera tout ce dont vous avez besoin pour créer des applications web professionnelles.',
                'short_description' => 'Maîtrisez Laravel et créez des applications web professionnelles',
                'price' => 99.99,
                'sale_price' => 79.99,
                'level' => 'intermediate',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => true,
                'is_downloadable' => true,
                'tags' => ['Laravel', 'PHP', 'Web Development', 'Backend'],
                'requirements' => [
                    'Connaissances de base en PHP',
                    'Familiarité avec HTML/CSS',
                    'Concepts de base de base de données'
                ],
                'what_you_will_learn' => [
                    'Créer des applications web avec Laravel',
                    'Gérer les bases de données avec Eloquent',
                    'Implémenter l\'authentification et l\'autorisation',
                    'Créer des APIs RESTful',
                    'Déployer des applications Laravel'
                ]
            ],
            [
                'title' => 'Design UX/UI Moderne',
                'description' => 'Découvrez les principes du design UX/UI moderne et créez des interfaces utilisateur exceptionnelles. Ce cours couvre tous les aspects du design digital, de la recherche utilisateur aux prototypes interactifs.',
                'short_description' => 'Créez des interfaces utilisateur exceptionnelles',
                'price' => 149.99,
                'sale_price' => null,
                'level' => 'beginner',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => true,
                'is_downloadable' => false,
                'tags' => ['UX Design', 'UI Design', 'Figma', 'Prototyping'],
                'requirements' => [
                    'Aucune expérience préalable requise',
                    'Ordinateur avec accès à Figma',
                    'Motivation pour apprendre'
                ],
                'what_you_will_learn' => [
                    'Principes fondamentaux du design UX/UI',
                    'Recherche utilisateur et personas',
                    'Wireframing et prototypage',
                    'Design systems et composants',
                    'Tests utilisateur et itération'
                ]
            ],
            [
                'title' => 'Marketing Digital Complet',
                'description' => 'Maîtrisez toutes les stratégies de marketing digital pour faire croître votre entreprise en ligne. Ce cours couvre le SEO, les réseaux sociaux, la publicité payante, l\'email marketing et bien plus.',
                'short_description' => 'Stratégies complètes de marketing digital',
                'price' => 199.99,
                'sale_price' => 149.99,
                'level' => 'intermediate',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => false,
                'is_downloadable' => true,
                'tags' => ['Marketing Digital', 'SEO', 'Social Media', 'Google Ads'],
                'requirements' => [
                    'Connaissances de base en marketing',
                    'Accès à Google Analytics',
                    'Compte Facebook Business'
                ],
                'what_you_will_learn' => [
                    'Stratégies SEO avancées',
                    'Gestion des réseaux sociaux',
                    'Publicité Google et Facebook',
                    'Email marketing et automation',
                    'Analytics et mesure de performance'
                ]
            ],
            [
                'title' => 'Photographie Professionnelle',
                'description' => 'Apprenez les techniques de photographie professionnelle et développez votre style artistique. Ce cours couvre la composition, l\'éclairage, la retouche photo et la gestion d\'entreprise.',
                'short_description' => 'Techniques de photographie professionnelle',
                'price' => 79.99,
                'sale_price' => null,
                'level' => 'beginner',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => false,
                'is_downloadable' => false,
                'tags' => ['Photographie', 'Composition', 'Éclairage', 'Retouche'],
                'requirements' => [
                    'Appareil photo (DSLR ou smartphone)',
                  'Logiciel de retouche (Lightroom recommandé)',
                    'Passion pour la photographie'
                ],
                'what_you_will_learn' => [
                    'Maîtrise de l\'exposition et de la composition',
                    'Techniques d\'éclairage professionnel',
                    'Retouche photo avec Lightroom',
                    'Gestion d\'entreprise photographique',
                    'Développement de style personnel'
                ]
            ],
            [
                'title' => 'Anglais des Affaires',
                'description' => 'Développez vos compétences en anglais des affaires pour réussir dans l\'environnement professionnel international. Ce cours couvre la communication, les présentations, les négociations et la correspondance.',
                'short_description' => 'Anglais professionnel pour le monde des affaires',
                'price' => 0,
                'sale_price' => null,
                'level' => 'intermediate',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => true,
                'is_free' => true,
                'is_downloadable' => true,
                'tags' => ['Anglais', 'Business', 'Communication', 'Professionnel'],
                'requirements' => [
                    'Niveau d\'anglais intermédiaire',
                    'Motivation pour pratiquer régulièrement',
                    'Accès à un microphone pour les exercices'
                ],
                'what_you_will_learn' => [
                    'Communication professionnelle en anglais',
                    'Présentations et pitchs efficaces',
                    'Négociation et réunions',
                    'Correspondance commerciale',
                    'Vocabulaire spécialisé des affaires'
                ]
            ]
        ];

        foreach ($courses as $index => $courseData) {
            $course = \App\Models\Course::create(array_merge($courseData, [
                'instructor_id' => $instructors->random()->id,
                'category_id' => $categories->random()->id,
                'slug' => \Illuminate\Support\Str::slug($courseData['title']),
                'thumbnail' => "courses/thumbnails/course-" . ($index + 1) . ".jpg",
            ]));

            // Créer quelques sections et leçons pour chaque cours
            $sections = [
                ['title' => 'Introduction', 'description' => 'Introduction au cours'],
                ['title' => 'Concepts fondamentaux', 'description' => 'Les bases essentielles'],
                ['title' => 'Pratique avancée', 'description' => 'Techniques avancées'],
                ['title' => 'Projet final', 'description' => 'Mise en pratique complète']
            ];

            foreach ($sections as $index => $sectionData) {
                $section = \App\Models\CourseSection::create([
                    'course_id' => $course->id,
                    'title' => $sectionData['title'],
                    'description' => $sectionData['description'],
                    'sort_order' => $index + 1,
                ]);

                // Créer quelques leçons pour chaque section
                $lessonsCount = rand(3, 8);
                for ($i = 1; $i <= $lessonsCount; $i++) {
                    \App\Models\CourseLesson::create([
                        'course_id' => $course->id,
                        'section_id' => $section->id,
                        'title' => "Leçon {$i}: " . fake()->sentence(4),
                        'description' => fake()->paragraph(),
                        'type' => fake()->randomElement(['video', 'text', 'pdf']),
                        'duration' => rand(5, 45),
                        'sort_order' => $i,
                        'is_preview' => $i === 1, // Première leçon en aperçu
                    ]);
                }
            }

        }
    }
}
