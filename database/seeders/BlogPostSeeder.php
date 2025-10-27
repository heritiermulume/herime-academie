<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les catégories
        $eLearningCategory = BlogCategory::where('slug', 'e-learning')->first();
        $techCategory = BlogCategory::where('slug', 'technologie')->first();
        $formationCategory = BlogCategory::where('slug', 'formation')->first();
        $devCategory = BlogCategory::where('slug', 'developpement')->first();
        $businessCategory = BlogCategory::where('slug', 'business')->first();

        // Récupérer un utilisateur admin comme auteur
        $admin = User::where('role', 'admin')->first();

        $posts = [
            [
                'author_id' => $admin->id,
                'category_id' => $eLearningCategory->id,
                'title' => 'L\'avenir de l\'apprentissage en ligne',
                'slug' => 'avenir-apprentissage-en-ligne',
                'excerpt' => 'Découvrez les tendances qui façonnent l\'avenir de l\'éducation numérique.',
                'content' => '<p>L\'apprentissage en ligne a connu une croissance exponentielle ces dernières années, et cette tendance ne fait que s\'accélérer. Les plateformes d\'e-learning comme Herime Academie révolutionnent la façon dont nous apprenons et enseignons.</p>

<p>Les avantages de l\'apprentissage en ligne sont nombreux :</p>
<ul>
<li>Flexibilité temporelle et géographique</li>
<li>Accès à des experts du monde entier</li>
<li>Coûts réduits par rapport à la formation traditionnelle</li>
<li>Personnalisation de l\'expérience d\'apprentissage</li>
</ul>

<p>Les technologies émergentes comme l\'intelligence artificielle, la réalité virtuelle et l\'apprentissage adaptatif transforment l\'expérience éducative, rendant l\'apprentissage plus interactif et efficace.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=400&fit=crop',
                'tags' => ['e-learning', 'éducation', 'technologie', 'avenir'],
                'is_published' => true,
                'is_featured' => true,
                'views' => 1250,
                'published_at' => now()->subDays(5),
            ],
            [
                'author_id' => $admin->id,
                'category_id' => $techCategory->id,
                'title' => 'Les meilleures pratiques pour créer des cours en ligne',
                'slug' => 'meilleures-pratiques-cours-en-ligne',
                'excerpt' => 'Guide complet pour créer des cours en ligne engageants et efficaces.',
                'content' => '<p>Créer un cours en ligne de qualité nécessite une approche méthodique et une compréhension des principes pédagogiques modernes. Voici les étapes essentielles :</p>

<h3>1. Définir les objectifs d\'apprentissage</h3>
<p>Commencez par identifier clairement ce que vos étudiants doivent apprendre. Utilisez la taxonomie de Bloom pour structurer vos objectifs.</p>

<h3>2. Structurer le contenu</h3>
<p>Organisez votre cours en modules logiques avec des leçons progressives. Chaque leçon doit avoir un objectif spécifique.</p>

<h3>3. Créer du contenu multimédia</h3>
<p>Utilisez des vidéos, des images, des infographies et des quiz pour maintenir l\'engagement des étudiants.</p>

<h3>4. Intégrer l\'interactivité</h3>
<p>Ajoutez des exercices pratiques, des discussions et des projets pour renforcer l\'apprentissage.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&h=400&fit=crop',
                'tags' => ['création de cours', 'pédagogie', 'conseils', 'formation'],
                'is_published' => true,
                'is_featured' => false,
                'views' => 890,
                'published_at' => now()->subDays(3),
            ],
            [
                'author_id' => $admin->id,
                'category_id' => $formationCategory->id,
                'title' => 'Comment motiver vos étudiants en ligne',
                'slug' => 'motiver-etudiants-en-ligne',
                'excerpt' => 'Stratégies éprouvées pour maintenir l\'engagement et la motivation des apprenants.',
                'content' => '<p>La motivation est un élément clé du succès en apprentissage en ligne. Voici comment créer un environnement motivant :</p>

<h3>Gamification</h3>
<p>Intégrez des éléments de jeu comme des points, des badges et des classements pour rendre l\'apprentissage plus engageant.</p>

<h3>Feedback régulier</h3>
<p>Fournissez des retours constructifs et encourageants pour aider les étudiants à progresser.</p>

<h3>Communauté d\'apprentissage</h3>
<p>Créez des espaces de discussion et d\'échange entre les étudiants pour favoriser l\'apprentissage collaboratif.</p>

<h3>Progression visible</h3>
<p>Montrez clairement les progrès réalisés avec des barres de progression et des certificats de réussite.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800&h=400&fit=crop',
                'tags' => ['motivation', 'engagement', 'pédagogie', 'étudiants'],
                'is_published' => true,
                'is_featured' => false,
                'views' => 650,
                'published_at' => now()->subDays(7),
            ],
            [
                'author_id' => $admin->id,
                'category_id' => $devCategory->id,
                'title' => 'Développement web moderne : les tendances 2024',
                'slug' => 'developpement-web-tendances-2024',
                'excerpt' => 'Explorez les dernières tendances et technologies du développement web.',
                'content' => '<p>Le développement web évolue rapidement. Voici les tendances qui marquent 2024 :</p>

<h3>Frameworks JavaScript</h3>
<p>React, Vue.js et Angular continuent de dominer, avec de nouvelles fonctionnalités et améliorations de performance.</p>

<h3>JAMstack</h3>
<p>L\'architecture JAMstack (JavaScript, APIs, Markup) gagne en popularité pour sa performance et sa sécurité.</p>

<h3>Progressive Web Apps (PWA)</h3>
<p>Les PWA offrent une expérience native dans le navigateur avec des fonctionnalités offline.</p>

<h3>WebAssembly</h3>
<p>WebAssembly permet d\'exécuter du code compilé dans le navigateur pour des performances optimales.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?w=800&h=400&fit=crop',
                'tags' => ['développement web', 'JavaScript', 'tendances', 'technologie'],
                'is_published' => true,
                'is_featured' => true,
                'views' => 2100,
                'published_at' => now()->subDays(2),
            ],
            [
                'author_id' => $admin->id,
                'category_id' => $businessCategory->id,
                'title' => 'Monétiser vos compétences en ligne',
                'slug' => 'monetiser-competences-en-ligne',
                'excerpt' => 'Guide pratique pour transformer vos connaissances en revenus durables.',
                'content' => '<p>L\'économie de la connaissance offre de nombreuses opportunités pour monétiser vos compétences :</p>

<h3>Création de cours en ligne</h3>
<p>Transformez votre expertise en cours structurés et vendez-les sur des plateformes comme Herime Academie.</p>

<h3>Coaching et consulting</h3>
<p>Offrez des services personnalisés de coaching et de consulting dans votre domaine d\'expertise.</p>

<h3>Affiliation et partenariats</h3>
<p>Rejoignez des programmes d\'affiliation pour promouvoir des produits et services liés à votre niche.</p>

<h3>Création de contenu</h3>
<p>Développez une audience sur les réseaux sociaux et monétisez votre contenu éducatif.</p>',
                'featured_image' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&h=400&fit=crop',
                'tags' => ['monétisation', 'business', 'compétences', 'revenus'],
                'is_published' => true,
                'is_featured' => false,
                'views' => 1800,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }
    }
}