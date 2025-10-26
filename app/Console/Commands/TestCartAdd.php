<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use App\Http\Controllers\CartController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestCartAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cart-add {--user-id= : ID de l\'utilisateur à tester} {--course-id= : ID du cours à ajouter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester l\'ajout d\'un cours au panier';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id') ?? 5;
        $courseId = $this->option('course-id') ?? 6;
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("Utilisateur avec l'ID {$userId} non trouvé.");
            return 1;
        }
        
        auth()->login($user);
        $this->info("Test avec l'utilisateur: {$user->name} (ID: {$user->id})");
        
        $course = Course::find($courseId);
        if (!$course) {
            $this->error("Cours avec l'ID {$courseId} non trouvé.");
            return 1;
        }
        
        $this->info("Test d'ajout du cours: {$course->title} (ID: {$course->id})");
        $this->newLine();
        
        // Vérifier l'état initial
        $this->info('1. État initial du panier:');
        $cartItems = $user->cartItems()->with('course')->get();
        $this->info("   Nombre d'articles: " . $cartItems->count());
        foreach ($cartItems as $item) {
            $this->line("   - {$item->course->title} (ID: {$item->course_id})");
        }
        
        // Vérifier si le cours est déjà dans le panier
        $this->newLine();
        $this->info('2. Vérification des conditions:');
        $isInCart = $user->cartItems()->where('course_id', $courseId)->exists();
        $this->info("   Cours déjà dans le panier: " . ($isInCart ? 'Oui' : 'Non'));
        
        $isFree = $course->is_free;
        $this->info("   Cours gratuit: " . ($isFree ? 'Oui' : 'Non'));
        
        $isEnrolled = $course->isEnrolledBy($user->id);
        $this->info("   Utilisateur inscrit: " . ($isEnrolled ? 'Oui' : 'Non'));
        
        if ($isInCart) {
            $this->warn("   ⚠️  Le cours est déjà dans le panier - l'ajout devrait échouer");
        } elseif ($isFree) {
            $this->warn("   ⚠️  Le cours est gratuit - l'ajout devrait échouer");
        } elseif ($isEnrolled) {
            $this->warn("   ⚠️  L'utilisateur est déjà inscrit - l'ajout devrait échouer");
        } else {
            $this->info("   ✅ Toutes les conditions sont remplies - l'ajout devrait réussir");
        }
        
        // Simuler l'ajout via le contrôleur
        $this->newLine();
        $this->info('3. Simulation de l\'ajout via le contrôleur:');
        
        $cartController = new CartController();
        $request = new Request();
        $request->merge(['course_id' => $courseId]);
        
        try {
            $response = $cartController->add($request);
            $data = json_decode($response->getContent(), true);
            
            $this->info("   Succès: " . ($data['success'] ? 'Oui' : 'Non'));
            $this->info("   Message: " . $data['message']);
            if (isset($data['cart_count'])) {
                $this->info("   Nombre d'articles: " . $data['cart_count']);
            }
            
            // Vérifier l'état final
            $this->newLine();
            $this->info('4. État final du panier:');
            $cartItemsFinal = $user->cartItems()->with('course')->get();
            $this->info("   Nombre d'articles: " . $cartItemsFinal->count());
            foreach ($cartItemsFinal as $item) {
                $this->line("   - {$item->course->title} (ID: {$item->course_id})");
            }
            
        } catch (\Exception $e) {
            $this->error("   Erreur lors de l'ajout: " . $e->getMessage());
        }
        
        return 0;
    }
}

