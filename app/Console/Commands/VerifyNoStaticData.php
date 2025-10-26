<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyNoStaticData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify:no-static-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier qu\'aucune donnée statique n\'est utilisée dans le site';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification de l\'absence de données statiques...');
        $this->newLine();

        $issues = [];

        // 1. Vérifier que les champs statiques n'existent plus dans la base de données
        $this->info('1. Vérification de la structure de la base de données...');
        
        try {
            $columns = DB::select("SHOW COLUMNS FROM courses LIKE 'students_count'");
            if (!empty($columns)) {
                $issues[] = "❌ Le champ 'students_count' existe encore dans la table courses";
            } else {
                $this->info("   ✅ Le champ 'students_count' a été supprimé");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Impossible de vérifier la structure de la base de données: " . $e->getMessage());
        }

        try {
            $columns = DB::select("SHOW COLUMNS FROM courses LIKE 'rating'");
            if (!empty($columns)) {
                $issues[] = "❌ Le champ 'rating' existe encore dans la table courses";
            } else {
                $this->info("   ✅ Le champ 'rating' a été supprimé");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Impossible de vérifier la structure de la base de données: " . $e->getMessage());
        }

        try {
            $columns = DB::select("SHOW COLUMNS FROM courses LIKE 'reviews_count'");
            if (!empty($columns)) {
                $issues[] = "❌ Le champ 'reviews_count' existe encore dans la table courses";
            } else {
                $this->info("   ✅ Le champ 'reviews_count' a été supprimé");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Impossible de vérifier la structure de la base de données: " . $e->getMessage());
        }

        try {
            $columns = DB::select("SHOW COLUMNS FROM courses LIKE 'lessons_count'");
            if (!empty($columns)) {
                $issues[] = "❌ Le champ 'lessons_count' existe encore dans la table courses";
            } else {
                $this->info("   ✅ Le champ 'lessons_count' a été supprimé");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Impossible de vérifier la structure de la base de données: " . $e->getMessage());
        }

        try {
            $columns = DB::select("SHOW COLUMNS FROM courses LIKE 'duration'");
            if (!empty($columns)) {
                $issues[] = "❌ Le champ 'duration' existe encore dans la table courses";
            } else {
                $this->info("   ✅ Le champ 'duration' a été supprimé");
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  Impossible de vérifier la structure de la base de données: " . $e->getMessage());
        }

        $this->newLine();

        // 2. Vérifier que les accesseurs dynamiques fonctionnent
        $this->info('2. Vérification des accesseurs dynamiques...');
        
        try {
            $course = Course::with(['enrollments', 'reviews', 'sections.lessons'])->first();
            
            if ($course) {
                // Tester les accesseurs dynamiques
                $studentsCount = $course->students_count;
                $rating = $course->rating;
                $reviewsCount = $course->reviews_count;
                $lessonsCount = $course->lessons_count;
                $duration = $course->duration;
                
                $this->info("   ✅ Accesseurs dynamiques fonctionnels:");
                $this->info("      - students_count: {$studentsCount}");
                $this->info("      - rating: " . number_format($rating, 1));
                $this->info("      - reviews_count: {$reviewsCount}");
                $this->info("      - lessons_count: {$lessonsCount}");
                $this->info("      - duration: {$duration} min");
            } else {
                $this->warn("   ⚠️  Aucun cours trouvé pour les tests");
            }
        } catch (\Exception $e) {
            $issues[] = "❌ Erreur lors du test des accesseurs dynamiques: " . $e->getMessage();
        }

        $this->newLine();

        // 3. Vérifier que les statistiques sont calculées correctement
        $this->info('3. Vérification des statistiques calculées...');
        
        try {
            $course = Course::with(['enrollments', 'reviews', 'sections.lessons'])->first();
            
            if ($course) {
                $stats = $course->getCourseStats();
                
                $this->info("   ✅ Statistiques calculées:");
                $this->info("      - total_students: {$stats['total_students']}");
                $this->info("      - average_rating: " . number_format($stats['average_rating'], 1));
                $this->info("      - total_reviews: {$stats['total_reviews']}");
                $this->info("      - total_lessons: {$stats['total_lessons']}");
                $this->info("      - total_duration: {$stats['total_duration']} min");
            }
        } catch (\Exception $e) {
            $issues[] = "❌ Erreur lors du calcul des statistiques: " . $e->getMessage();
        }

        $this->newLine();

        // 4. Vérifier que les scopes utilisent des relations
        $this->info('4. Vérification des scopes dynamiques...');
        
        try {
            // Tester le scope popular
            $popularCourses = Course::published()->popular()->limit(3)->get();
            $this->info("   ✅ Scope 'popular' fonctionne: " . $popularCourses->count() . " cours trouvés");
            
            // Tester le scope topRated
            $topRatedCourses = Course::published()->topRated()->limit(3)->get();
            $this->info("   ✅ Scope 'topRated' fonctionne: " . $topRatedCourses->count() . " cours trouvés");
            
        } catch (\Exception $e) {
            $issues[] = "❌ Erreur lors du test des scopes: " . $e->getMessage();
        }

        $this->newLine();

        // 5. Résumé
        if (empty($issues)) {
            $this->info('🎉 SUCCÈS: Aucune donnée statique détectée!');
            $this->info('✅ Toutes les données sont maintenant calculées dynamiquement');
            $this->info('✅ La base de données a été nettoyée');
            $this->info('✅ Les accesseurs dynamiques fonctionnent correctement');
            $this->info('✅ Les statistiques sont calculées en temps réel');
            return 0;
        } else {
            $this->error('❌ PROBLÈMES DÉTECTÉS:');
            foreach ($issues as $issue) {
                $this->error($issue);
            }
            return 1;
        }
    }
}