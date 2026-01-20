<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use App\Http\Controllers\CartController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class TestCartRecommendations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cart-recommendations {--user-id= : ID de l\'utilisateur à tester}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester les recommandations du panier et le filtrage des cours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Test des recommandations du panier...');
        $this->newLine();

        $userId = $this->option('user-id');
        
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("Utilisateur avec l'ID {$userId} non trouvé.");
                return 1;
            }
            auth()->login($user);
            $this->info("Test avec l'utilisateur: {$user->name} (ID: {$user->id})");
        } else {
            $this->info("Test sans utilisateur connecté");
        }

        $this->newLine();

        // Test 1: Vérifier les cours populaires
        $this->info('1. Test des cours populaires...');
        $cartController = new CartController();
        $reflection = new \ReflectionClass($cartController);
        $method = $reflection->getMethod('getPopularCoursesForCart');
        $method->setAccessible(true);
        
        $popularCourses = $method->invoke($cartController);
        
        $this->info("   📊 Nombre de cours populaires trouvés: " . $popularCourses->count());
        
        foreach ($popularCourses as $course) {
            $this->line("   - {$course->title} (ID: {$course->id})");
            $this->line("     Gratuit: " . ($course->is_free ? 'Oui' : 'Non'));
            $this->line("     Clients: {$course->stats['total_customers']}");
            $this->line("     Note: " . number_format($course->stats['average_rating'], 1));
        }

        $this->newLine();

        // Test 2: Vérifier les cours exclus
        $this->info('2. Test des cours exclus...');
        $excludeMethod = $reflection->getMethod('getExcludedCourseIds');
        $excludeMethod->setAccessible(true);
        
        // Simuler un panier vide
        $excludedIds = $excludeMethod->invoke($cartController, []);
        $this->info("   🚫 Nombre de cours exclus: " . count($excludedIds));
        
        if (auth()->check()) {
            $enrolledCourses = auth()->user()->enrollments()
                ->whereIn('status', ['active', 'completed'])
                
                ->with('course')
                ->get();
            
            $this->info("   👤 Cours auxquels l'utilisateur est inscrit:");
            foreach ($enrolledCourses as $enrollment) {
                $this->line("     - {$enrollment->course->title} (ID: {$enrollment->course->id})");
            }
        }

        $this->newLine();

        // Test 3: Vérifier les recommandations
        $this->info('3. Test des recommandations...');
        $recommendMethod = $reflection->getMethod('getSmartRecommendations');
        $recommendMethod->setAccessible(true);
        
        $recommendedCourses = $recommendMethod->invoke($cartController, []);
        
        $this->info("   💡 Nombre de cours recommandés: " . $recommendedCourses->count());
        
        foreach ($recommendedCourses as $course) {
            $this->line("   - {$course->title} (ID: {$course->id})");
            $this->line("     Gratuit: " . ($course->is_free ? 'Oui' : 'Non'));
            $this->line("     Clients: {$course->stats['total_customers']}");
        }

        $this->newLine();

        // Test 4: Vérifier qu'aucun cours exclu n'apparaît dans les recommandations
        $this->info('4. Vérification de l\'exclusion...');
        
        $recommendedIds = $recommendedCourses->pluck('id')->toArray();
        $popularIds = $popularCourses->pluck('id')->toArray();
        
        $conflicts = array_intersect($excludedIds, array_merge($recommendedIds, $popularIds));
        
        if (empty($conflicts)) {
            $this->info("   ✅ Aucun cours exclu n'apparaît dans les recommandations");
        } else {
            $this->error("   ❌ Cours exclus trouvés dans les recommandations: " . implode(', ', $conflicts));
        }

        // Test 5: Vérifier les cours gratuits
        $this->info('5. Vérification des cours gratuits...');
        
        $freeInRecommendations = $recommendedCourses->where('is_free', true)->count();
        $freeInPopular = $popularCourses->where('is_free', true)->count();
        
        if ($freeInRecommendations == 0 && $freeInPopular == 0) {
            $this->info("   ✅ Aucun cours gratuit dans les recommandations");
        } else {
            $this->error("   ❌ Cours gratuits trouvés: {$freeInRecommendations} dans les recommandations, {$freeInPopular} dans les cours populaires");
        }

        $this->newLine();
        $this->info('🎉 Test terminé!');
        
        return 0;
    }
}