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
        
        // Phrases fixes pour les leçons au lieu de Faker
        $lessonTitles = [
            'Introduction au concept',
            'Les bases fondamentales',
            'Pratique interactive',
            'Exercices avancés',
            'Cas d\'étude réel',
            'Optimisation et amélioration',
            'Mise en pratique complète',
            'Résumé et conclusion'
        ];
        
        $lessonDescriptions = [
            'Découvrez les concepts clés de cette leçon',
            'Apprenez les techniques essentielles',
            'Mettez en pratique vos connaissances',
            'Approfondissez vos compétences',
            'Analysez des exemples concrets',
            'Explorez les techniques avancées',
            'Réalisez un projet complet',
            'Consolidez vos acquis'
        ];

        $courses = [
            [
                'title' => 'Développement Web Complet avec Laravel',
                'description' => 'Apprenez le développement web moderne avec Laravel, le framework PHP le plus populaire. Ce cours complet vous enseignera tout ce dont vous avez besoin pour créer des applications web professionnelles.',
                'short_description' => 'Maîtrisez Laravel et créez des applications web professionnelles',
                'price' => 99.99,
                'sale_price' => 79.99,
                'sale_end_at' => now()->addDays(7), // Promotion de 7 jours
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
                'sale_end_at' => now()->addDays(14), // Promotion de 14 jours
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
            ],
            // Nouveaux cours en promotion
            [
                'title' => 'React JS Avancé - Hooks et Redux',
                'description' => 'Maîtrisez React JS avec les hooks modernes et Redux pour créer des applications web interactives et performantes. Ce cours avancé vous apprendra les meilleures pratiques et patterns React.',
                'short_description' => 'React JS avancé avec hooks et Redux',
                'price' => 129.99,
                'sale_price' => 89.99,
                'sale_end_at' => now()->addDays(5), // Promotion de 5 jours
                'level' => 'advanced',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => true,
                'is_downloadable' => true,
                'tags' => ['React', 'JavaScript', 'Redux', 'Frontend'],
                'requirements' => [
                    'Connaissances de base en JavaScript',
                    'Familiarité avec React (niveau débutant)',
                    'Node.js installé sur votre machine'
                ],
                'what_you_will_learn' => [
                    'Maîtriser les hooks React (useState, useEffect, etc.)',
                    'Implémenter Redux pour la gestion d\'état',
                    'Optimiser les performances de vos applications',
                    'Créer des composants réutilisables',
                    'Tests et déploiement d\'applications React'
                ]
            ],
            [
                'title' => 'Python pour la Data Science',
                'description' => 'Découvrez Python pour l\'analyse de données et la science des données. Apprenez à manipuler des données, créer des visualisations et construire des modèles de machine learning.',
                'short_description' => 'Python pour l\'analyse de données et le ML',
                'price' => 179.99,
                'sale_price' => 129.99,
                'sale_end_at' => now()->addDays(10), // Promotion de 10 jours
                'level' => 'intermediate',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => false,
                'is_downloadable' => true,
                'tags' => ['Python', 'Data Science', 'Machine Learning', 'Pandas'],
                'requirements' => [
                    'Connaissances de base en programmation',
                    'Python 3.x installé',
                    'Jupyter Notebook (optionnel mais recommandé)'
                ],
                'what_you_will_learn' => [
                    'Manipuler des données avec Pandas',
                    'Créer des visualisations avec Matplotlib',
                    'Construire des modèles de machine learning',
                    'Nettoyer et préparer des données',
                    'Analyser des données réelles avec des projets pratiques'
                ]
            ],
            [
                'title' => 'Adobe Photoshop Maîtrise Complète',
                'description' => 'Devenez expert en retouche photo et design graphique avec Adobe Photoshop. Ce cours complet couvre toutes les techniques professionnelles de retouche, montage et création graphique.',
                'short_description' => 'Maîtrise complète d\'Adobe Photoshop',
                'price' => 89.99,
                'sale_price' => 59.99,
                'sale_end_at' => now()->addDays(3), // Promotion de 3 jours (urgence)
                'level' => 'beginner',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => true,
                'is_downloadable' => false,
                'tags' => ['Photoshop', 'Design', 'Retouche Photo', 'Graphisme'],
                'requirements' => [
                    'Adobe Photoshop installé',
                    'Ordinateur avec au moins 8GB RAM',
                    'Motivation pour apprendre'
                ],
                'what_you_will_learn' => [
                    'Interface et outils de Photoshop',
                    'Techniques de retouche professionnelle',
                    'Montage photo avancé',
                    'Création de designs graphiques',
                    'Optimisation pour le web et l\'impression'
                ]
            ],
            [
                'title' => 'Développement Mobile Flutter',
                'description' => 'Créez des applications mobiles natives pour iOS et Android avec Flutter et Dart. Développez une fois, déployez partout avec un seul codebase.',
                'short_description' => 'Applications mobiles avec Flutter et Dart',
                'price' => 149.99,
                'sale_price' => 109.99,
                'sale_end_at' => now()->addDays(21), // Promotion de 21 jours
                'level' => 'intermediate',
                'language' => 'fr',
                'is_published' => true,
                'is_featured' => false,
                'is_downloadable' => true,
                'tags' => ['Flutter', 'Dart', 'Mobile Development', 'iOS', 'Android'],
                'requirements' => [
                    'Connaissances de base en programmation',
                    'Flutter SDK installé',
                    'Android Studio ou Xcode'
                ],
                'what_you_will_learn' => [
                    'Fondamentaux de Dart et Flutter',
                    'Créer des interfaces utilisateur fluides',
                    'Gérer l\'état avec Provider et Bloc',
                    'Accéder aux APIs et bases de données',
                    'Publier sur App Store et Google Play'
                ]
            ]
        ];

        // URLs d'images de placeholder pour les cours
        $courseImages = [
            'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1485856407642-7f9ba0268b51?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1517732306149-e8f829eb588a?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=800&h=600&fit=crop',
        ];
        
        foreach ($courses as $index => $courseData) {
            $slug = \Illuminate\Support\Str::slug($courseData['title']);
            $course = \App\Models\Course::updateOrCreate(
                ['slug' => $slug],
                array_merge($courseData, [
                    'instructor_id' => $instructors->random()->id,
                    'category_id' => $categories->random()->id,
                    'thumbnail' => $courseImages[$index % count($courseImages)],
                ])
            );

            // Créer quelques sections et leçons pour chaque cours
            $sections = [
                ['title' => 'Introduction', 'description' => 'Introduction au cours'],
                ['title' => 'Concepts fondamentaux', 'description' => 'Les bases essentielles'],
                ['title' => 'Pratique avancée', 'description' => 'Techniques avancées'],
                ['title' => 'Projet final', 'description' => 'Mise en pratique complète']
            ];

            foreach ($sections as $index => $sectionData) {
                $section = \App\Models\CourseSection::updateOrCreate(
                    [
                        'course_id' => $course->id,
                        'sort_order' => $index + 1,
                    ],
                    [
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'],
                    ]
                );

                // Créer quelques leçons pour chaque section
                $lessonsCount = rand(3, 8);
                $types = ['video', 'text', 'pdf'];
                
                for ($i = 1; $i <= $lessonsCount; $i++) {
                    $titleIndex = ($i - 1) % count($lessonTitles);
                    $descIndex = ($i - 1) % count($lessonDescriptions);
                    $typeIndex = array_rand($types);
                    
                    \App\Models\CourseLesson::updateOrCreate(
                        [
                            'course_id' => $course->id,
                            'section_id' => $section->id,
                            'sort_order' => $i,
                        ],
                        [
                            'title' => "Leçon {$i}: " . $lessonTitles[$titleIndex],
                            'description' => $lessonDescriptions[$descIndex],
                            'type' => $types[$typeIndex],
                            'duration' => rand(5, 45),
                            'is_preview' => $i === 1, // Première leçon en aperçu
                        ]
                    );
                }
            }

        }
    }
}
