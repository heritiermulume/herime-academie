<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Afficher le contenu du panier
     */
    public function index()
    {
        if (auth()->check()) {
            // Utilisateur connecté : utiliser la base de données
            $cartItems = $this->getDatabaseCartItems();
        } else {
            // Utilisateur non connecté : utiliser la session
            $cartItems = $this->getSessionCartItems();
        }

        // S'assurer que $cartItems est un tableau
        $cartItems = is_array($cartItems) ? $cartItems : $cartItems->toArray();

        // Calculer les totaux
        $subtotal = collect($cartItems)->sum('subtotal');
        $tax = 0; // Pour l'instant, pas de taxes
        $total = $subtotal + $tax;

        // Obtenir des recommandations intelligentes basées sur le contenu du panier
        $recommendedCourses = $this->getSmartRecommendations($cartItems);

        // Obtenir les cours populaires pour le panier vide
        $popularCourses = $this->getPopularCoursesForCart();
        
        return view('cart.index', compact('cartItems', 'subtotal', 'tax', 'total', 'recommendedCourses', 'popularCourses'));
    }

    /**
     * Ajouter un cours au panier
     */
public function add(Request $request)
    {
        try {
            // Validation des données
            try {
                $request->validate([
                    'course_id' => 'required|exists:courses,id'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Retourner une réponse JSON même en cas d'erreur de validation
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }

            $courseId = $request->course_id;
            
            // Récupérer le cours
            try {
                $course = Course::findOrFail($courseId);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cours introuvable.'
                ], 404);
            }

            // Vérifier que le cours est publié
            if (!$course->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce cours n\'est pas disponible.'
                ], 404);
            }

            // Vérifier si le cours est gratuit
            if ($course->is_free) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les cours gratuits ne peuvent pas être ajoutés au panier. Inscrivez-vous directement.'
                ]);
            }

            // Vérifier si l'utilisateur est déjà inscrit
            if (auth()->check() && $course->isEnrolledBy(auth()->id())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous êtes déjà inscrit à ce cours.'
                ]);
            }

            if (auth()->check()) {
                // Vérifier si le cours est déjà dans le panier
                if (auth()->user()->cartItems()->where('course_id', $courseId)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce cours est déjà dans votre panier.'
                    ]);
                }

                // Utilisateur connecté : sauvegarder en base de données
                $this->addToDatabaseCart($courseId);
                $cartCount = auth()->user()->cartItems()->count();
            } else {
                // Vérifier si le cours est déjà dans le panier de session
                $cart = $this->getSessionCart();
                if (isset($cart[$courseId])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce cours est déjà dans votre panier.'
                    ]);
                }

                // Utilisateur non connecté : utiliser la session
                $this->addToSessionCart($courseId);
                $cartCount = count($cart) + 1;
            }

            return response()->json([
                'success' => true,
                'message' => 'Cours ajouté au panier avec succès.',
                'cart_count' => $cartCount
            ]);
        } catch (\Exception $e) {
            // Gérer toutes les autres erreurs potentielles
            \Log::error('Error adding course to cart', [
                'error' => $e->getMessage(),
                'course_id' => $request->course_id ?? null,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout au panier. Veuillez réessayer.'
            ], 500);
        }
    }


    /**
     * Supprimer un cours du panier
     */
    public function remove(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id'
        ]);

        $courseId = $request->course_id;

        if (auth()->check()) {
            // Utilisateur connecté : supprimer de la base de données
            CartItem::where('user_id', auth()->id())
                ->where('course_id', $courseId)
                ->delete();
            $cartCount = auth()->user()->cartItems()->count();
        } else {
            // Utilisateur non connecté : utiliser la session
            $cart = $this->getSessionCart();
            $cart = array_values(array_filter($cart, function($id) use ($courseId) {
                return $id != $courseId;
            }));
            $this->saveSessionCart($cart);
            $cartCount = count($cart);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cours supprimé du panier.',
            'cart_count' => $cartCount
        ]);
    }

    /**
     * Vider le panier
     */
    public function clear()
    {
        if (auth()->check()) {
            // Utilisateur connecté : vider la base de données
            auth()->user()->cartItems()->delete();
        } else {
            // Utilisateur non connecté : vider la session
        Session::forget('cart');
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Panier vidé avec succès.',
            'cart_count' => 0
        ]);
    }


    /**
     * Obtenir le nombre d'articles dans le panier
     */
    public function count()
    {
        try {
            if (auth()->check()) {
                // Utilisateur connecté : compter depuis la base de données
                $count = auth()->user()->cartItems()->count();
            } else {
                // Utilisateur non connecté : compter depuis la session
                $cart = $this->getSessionCart();
                $count = is_array($cart) ? count($cart) : 0;
            }

            return response()->json([
                'count' => $count
            ]);
        } catch (\Exception $e) {
            // Logger l'erreur pour le débogage
            \Log::error('Error getting cart count', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Retourner 0 en cas d'erreur pour éviter de casser l'interface
            return response()->json([
                'count' => 0
            ], 200);
        }
    }

    /**
     * Afficher la page de checkout
     */
    public function checkout()
    {
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour procéder au paiement.');
        }

        if (auth()->check()) {
            // Utilisateur connecté : utiliser la base de données
            $cartItems = $this->getDatabaseCartItems();
        } else {
            // Utilisateur non connecté : utiliser la session
            $cartItems = $this->getSessionCartItems();
        }

        // S'assurer que $cartItems est un tableau
        $cartItems = is_array($cartItems) ? $cartItems : [];

        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('error', 'Votre panier est vide.');
        }

        // Calculer le total dans la devise de base du site
        // Les prix des cours sont stockés dans la devise de base configurée dans /admin/settings
        $total = collect($cartItems)->sum('subtotal');

        // Récupérer la devise de base
        $baseCurrency = \App\Models\Setting::getBaseCurrency();
        $currencyCode = is_array($baseCurrency) ? ($baseCurrency['code'] ?? 'USD') : ($baseCurrency ?? 'USD');

        return view('cart.checkout', compact('cartItems', 'total', 'baseCurrency', 'currencyCode'));
    }

    /**
     * Vérifier si un cours a été acheté ou si l'utilisateur y est inscrit
     */
    private function isCoursePurchased($course)
    {
        if (!auth()->check()) {
            return false;
        }
        
        $userId = auth()->id();
        
        // Vérifier si l'utilisateur est inscrit au cours
        $isEnrolled = $course->isEnrolledBy($userId);
        if ($isEnrolled) {
            return true;
        }
        
        // Vérifier si l'utilisateur a acheté le cours (pour les cours payants)
        if (!$course->is_free) {
            $hasPurchased = \App\Models\Order::where('user_id', $userId)
                ->where('status', 'paid')
                ->whereHas('orderItems', function($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->exists();
            
            if ($hasPurchased) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtenir les IDs des cours à exclure des recommandations
     */
    private function getExcludedCourseIds($cartItems)
    {
        $excludedIds = collect();
        
        // 1. Exclure les cours déjà dans le panier
        if (!empty($cartItems) && is_array($cartItems)) {
            $cartCourseIds = collect($cartItems)->map(function($item) {
                // Gérer les deux structures possibles : avec 'course.id' ou directement 'id'
                if (isset($item['course']['id'])) {
                    return $item['course']['id'];
                } elseif (isset($item['id'])) {
                    return $item['id'];
                } elseif (is_object($item) && isset($item->course->id)) {
                    return $item->course->id;
                } elseif (is_object($item) && isset($item->id)) {
                    return $item->id;
                }
                return null;
            })->filter()->toArray();
            
            $excludedIds = $excludedIds->merge($cartCourseIds);
        }
        
        // 2. Exclure les cours gratuits (toujours)
        $freeCourseIds = Course::published()
            ->where('is_free', true)
            ->pluck('id')
            ->toArray();
        $excludedIds = $excludedIds->merge($freeCourseIds);
        
        // 3. Si l'utilisateur est connecté, exclure les cours déjà achetés ou auxquels il est inscrit
        if (auth()->check()) {
            $purchasedCourseIds = auth()->user()
                ->enrollments()
                ->whereIn('status', ['active', 'completed']) // Inclure les cours actifs ET complétés
                ->pluck('course_id')
                ->toArray();
            $excludedIds = $excludedIds->merge($purchasedCourseIds);
            
            // Debug: Log des cours exclus
            \Log::info('Cours exclus pour l\'utilisateur ' . auth()->id() . ':', [
                'cart_course_ids' => $cartCourseIds ?? [],
                'purchased_course_ids' => $purchasedCourseIds,
                'free_course_ids' => $freeCourseIds,
                'total_excluded' => count($excludedIds->toArray())
            ]);
        }
        
        $result = $excludedIds->unique()->values()->toArray();
        
        return $result;
    }

    /**
     * Obtenir les cours populaires pour le panier (excluant les cours déjà achetés/inscrits et dans le panier)
     */
    private function getPopularCoursesForCart()
    {
        // Obtenir les IDs des cours à exclure (dans le panier + achetés/inscrits + gratuits)
        $cartItems = session('cart', []);
        $excludedCourseIds = $this->getExcludedCourseIds($cartItems);
        
        // Obtenir les cours populaires avec filtrage approprié
        $popularCourses = Course::published()
            ->where('is_free', false) // Exclure les cours gratuits
            ->whereNotIn('id', $excludedCourseIds) // Exclure les cours déjà dans le panier, achetés, etc.
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->withCount('enrollments') // Pour le tri par nombre d'étudiants
            ->orderBy('enrollments_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20) // Augmenter la limite pour compenser le filtrage
            ->get();
        
        // Filtrage manuel supplémentaire pour s'assurer qu'aucun cours indésirable ne passe
        $popularCourses = $popularCourses->filter(function($course) {
            // Exclure les cours gratuits (double vérification)
            if ($course->is_free) {
                return false;
            }
            
            // Si l'utilisateur est connecté, exclure les cours déjà achetés ou auxquels il est inscrit
            if (auth()->check()) {
                $isPurchased = auth()->user()
                    ->enrollments()
                    ->where('course_id', $course->id)
                    ->whereIn('status', ['active', 'completed'])
                    
                    ->exists();
                
                if ($isPurchased) {
                    return false;
                }
            }
            
            return true;
        });
        
        // Ajouter les statistiques à chaque cours
        $popularCourses = $popularCourses->map(function($course) {
            $course->stats = [
                'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->count() : 0;
                }) : 0,
                'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->sum('duration') : 0;
                }) : 0,
                'total_students' => $course->enrollments ? $course->enrollments->count() : 0,
                'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
            ];
            return $course;
        })->take(8); // Limiter à 8 cours après filtrage
        
        return $popularCourses;
    }

    /**
     * Obtenir des recommandations intelligentes basées sur le contenu du panier
     * Utilise uniquement des données dynamiques de la base de données
     */
    private function getSmartRecommendations($cartItems)
    {
        // Obtenir les IDs des cours à exclure
        $excludedCourseIds = $this->getExcludedCourseIds($cartItems);
        
        if (empty($cartItems) || !is_array($cartItems)) {
            // Si le panier est vide, recommander des cours populaires
            $courses = Course::published()
                ->where('is_free', false) // Force l'exclusion des cours gratuits
                ->whereNotIn('id', $excludedCourseIds)
                ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();
                
            // Double vérification : filtrer manuellement les cours gratuits et achetés
            $courses = $courses->filter(function($course) {
                // Exclure les cours gratuits
                if ($course->is_free) {
                    return false;
                }
                
                // Exclure les cours achetés ou auxquels l'utilisateur est inscrit
                if (auth()->check()) {
                    $isPurchased = auth()->user()
                        ->enrollments()
                        ->where('course_id', $course->id)
                        ->whereIn('status', ['active', 'completed'])
                        
                        ->exists();
                    
                    if ($isPurchased) {
                        return false;
                    }
                }
                
                return true;
            });
                
            
            // Ajouter les statistiques calculées
            return $courses->map(function($course) {
                $course->stats = [
                    'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                        return $section->lessons ? $section->lessons->count() : 0;
                    }) : 0,
                    'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                        return $section->lessons ? $section->lessons->sum('duration') : 0;
                    }) : 0,
                    'total_students' => $course->enrollments ? $course->enrollments->count() : 0,
                    'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                    'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
                ];
                return $course;
            });
        }

        $recommendations = collect();
        $cartCourseIds = collect($cartItems)->pluck('course.id')->toArray();
        $cartCategories = collect($cartItems)->pluck('course.category_id')->unique()->toArray();
        $cartLevels = collect($cartItems)->pluck('course.level')->unique()->toArray();
        $cartInstructors = collect($cartItems)->pluck('course.instructor_id')->unique()->toArray();

        // 1. Cours complémentaires de la même catégorie
        $categoryRecommendations = Course::published()
            ->where('is_free', false)
            ->whereIn('category_id', $cartCategories)
            ->whereNotIn('id', $excludedCourseIds)
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $categoryRecommendations = $categoryRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($categoryRecommendations);

        // 2. Cours du même niveau de difficulté (pour progression)
        $levelRecommendations = Course::published()
            ->where('is_free', false)
            ->whereIn('level', $cartLevels)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $levelRecommendations = $levelRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($levelRecommendations);

        // 3. Cours du même instructeur (si l'utilisateur aime le style)
        $instructorRecommendations = Course::published()
            ->where('is_free', false)
            ->whereIn('instructor_id', $cartInstructors)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $instructorRecommendations = $instructorRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($instructorRecommendations);

        // 4. Cours populaires récents (tendance)
        $trendingRecommendations = Course::published()
            ->where('is_free', false)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->where('created_at', '>=', now()->subMonth())
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $trendingRecommendations = $trendingRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($trendingRecommendations);

        // 5. Si l'utilisateur est connecté, recommandations basées sur ses préférences
        if (auth()->check()) {
            $userEnrollments = auth()->user()->enrollments()
                ->with('course')
                ->get()
                ->pluck('course.category_id')
                ->unique()
                ->toArray();

            if (!empty($userEnrollments)) {
                $userPreferenceRecommendations = Course::published()
                    ->where('is_free', false)
                    ->whereIn('category_id', $userEnrollments)
                    ->whereNotIn('id', $excludedCourseIds)
                    ->whereNotIn('id', $recommendations->pluck('id'))
                    ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->get();

                // Filtrer manuellement les cours gratuits et achetés
                $userPreferenceRecommendations = $userPreferenceRecommendations->filter(function($course) {
                    return !$course->is_free && !$this->isCoursePurchased($course);
                });

                $recommendations = $recommendations->merge($userPreferenceRecommendations);
            }
        }

        // 6. Si on n'a pas assez de recommandations, ajouter des cours populaires
        if ($recommendations->count() < 4) {
            $popularRecommendations = Course::published()
                ->where('is_free', false)
                ->whereNotIn('id', $excludedCourseIds)
                ->whereNotIn('id', $recommendations->pluck('id'))
                ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(4 - $recommendations->count())
                ->get();

            // Filtrer manuellement les cours gratuits et achetés
            $popularRecommendations = $popularRecommendations->filter(function($course) {
                return !$course->is_free && !$this->isCoursePurchased($course);
            });

            $recommendations = $recommendations->merge($popularRecommendations);
        }

        // Filtrage final pour s'assurer qu'aucun cours gratuit ou acheté ne passe
        $finalRecommendations = $recommendations->filter(function($course) {
            // Exclure les cours gratuits
            if ($course->is_free) {
                return false;
            }
            
            // Exclure les cours achetés ou auxquels l'utilisateur est inscrit
            if (auth()->check()) {
                $isPurchased = auth()->user()
                    ->enrollments()
                    ->where('course_id', $course->id)
                    ->whereIn('status', ['active', 'completed'])
                    
                    ->exists();
                
                if ($isPurchased) {
                    return false;
                }
            }
            
            return true;
        })->shuffle()->take(4);
        
        return $finalRecommendations->map(function($course) {
            // Charger les relations nécessaires si elles ne sont pas déjà chargées
            if (!$course->relationLoaded('sections')) {
                $course->load(['sections.lessons', 'reviews', 'enrollments']);
            }
            
            // Ajouter les statistiques calculées
            $course->stats = [
                'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->count() : 0;
                }) : 0,
                'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->sum('duration') : 0;
                }) : 0,
                'total_students' => $course->enrollments ? $course->enrollments->count() : 0,
                'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
            ];
            
            return $course;
        });
    }

    /**
     * Obtenir les recommandations pour le panier (AJAX)
     */
    public function getRecommendations()
    {
        if (auth()->check()) {
            // Utilisateur connecté : utiliser la base de données
            $cartItems = $this->getDatabaseCartItems();
        } else {
            // Utilisateur non connecté : utiliser la session
            $cartItems = $this->getSessionCartItems();
        }

        // S'assurer que $cartItems est un tableau
        $cartItems = is_array($cartItems) ? $cartItems : $cartItems->toArray();

        $recommendedCourses = $this->getSmartRecommendations($cartItems);

        $html = view('cart.partials.recommendations', compact('recommendedCourses'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    /**
     * Obtenir le contenu du panier pour l'affichage (AJAX)
     */
    public function getCartContent()
    {
        $cart = $this->getCart();
        $cartItems = [];
        $total = 0;

        foreach ($cart as $courseId => $quantity) {
            $course = Course::find($courseId);
            // Ne pas inclure les cours non publiés
            if ($course && $course->is_published) {
                $cartItems[] = [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'thumbnail' => $course->thumbnail,
                    'price' => $course->current_price,
                    'quantity' => $quantity,
                    'subtotal' => $course->current_price * $quantity
                ];
                $total += $course->current_price * $quantity;
            }
        }

        return response()->json([
            'items' => $cartItems,
            'total' => $total,
            'count' => array_sum($cart)
        ]);
    }

    /**
     * Obtenir les articles du panier depuis la base de données
     */
    private function getDatabaseCartItems()
    {
        $cartItems = auth()->user()->cartItems()->with([
            'course.category', 
            'course.instructor', 
            'course.reviews', 
            'course.enrollments',
            'course.sections.lessons'
        ])->get();
        
        return $cartItems->map(function ($item) {
            $course = $item->course;
            
            // Ajouter les statistiques calculées avec vérifications
            $course->stats = [
                'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->count() : 0;
                }) : 0,
                'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->sum('duration') : 0;
                }) : 0,
                'total_students' => $course->enrollments ? $course->enrollments->count() : 0,
                'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
            ];
            
            return [
                'course' => $course,
                'quantity' => 1,
                'price' => $course->current_price,
                'subtotal' => $course->current_price
            ];
        })->toArray();
    }

    /**
     * Obtenir les articles du panier depuis la session
     */
    private function getSessionCartItems()
    {
        $cart = $this->getSessionCart();
        $cartItems = [];

        foreach ($cart as $courseId) {
            $course = Course::with([
                'category', 
                'instructor', 
                'reviews', 
                'enrollments',
                'sections.lessons'
            ])->find($courseId);
            
            // Ne pas inclure les cours non publiés
            if ($course && $course->is_published) {
                // Ajouter les statistiques calculées avec vérifications
                $course->stats = [
                    'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                        return $section->lessons ? $section->lessons->count() : 0;
                    }) : 0,
                    'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                        return $section->lessons ? $section->lessons->sum('duration') : 0;
                    }) : 0,
                    'total_students' => $course->enrollments ? $course->enrollments->count() : 0,
                    'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                    'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
                ];
                
                $cartItems[] = [
                    'course' => $course,
                    'quantity' => 1,
                    'price' => $course->current_price,
                    'subtotal' => $course->current_price
                ];
            }
        }

        return $cartItems;
    }

    /**
     * Ajouter un cours au panier en base de données
     */
    private function addToDatabaseCart($courseId)
    {
        CartItem::create([
            'user_id' => auth()->id(),
            'course_id' => $courseId
        ]);
    }

    /**
     * Ajouter un cours au panier en session
     */
    private function addToSessionCart($courseId)
    {
        $cart = $this->getSessionCart();
        
        if (!in_array($courseId, $cart)) {
            $cart[] = $courseId;
            $this->saveSessionCart($cart);
        }
    }

    /**
     * Synchroniser le panier de session avec la base de données lors de la connexion
     */
    public function syncSessionToDatabase()
    {
        if (!auth()->check()) {
            return;
        }

        $sessionCart = $this->getSessionCart();
        
        foreach ($sessionCart as $courseId) {
            // Vérifier si le cours n'est pas déjà dans le panier de l'utilisateur
            if (!auth()->user()->cartItems()->where('course_id', $courseId)->exists()) {
                $this->addToDatabaseCart($courseId);
            }
        }

        // Vider la session après synchronisation
        Session::forget('cart');
    }

    /**
     * Obtenir le panier depuis la session
     */
    private function getSessionCart()
    {
        return Session::get('cart', []);
    }

    /**
     * Obtenir seulement le résumé du panier (pour mise à jour AJAX)
     */
    public function getSummary(Request $request)
    {
        if (auth()->check()) {
            $cartItems = auth()->user()->cartItems()->with('course')->get();
        } else {
            $cartItems = $this->getSessionCartItems();
        }

        // S'assurer que $cartItems est un tableau
        $cartItems = is_array($cartItems) ? $cartItems : $cartItems->toArray();
        $total = collect($cartItems)->sum('subtotal');
        $itemCount = count($cartItems);

        return response()->json([
            'success' => true,
            'subtotal' => $total,
            'total' => $total,
            'item_count' => $itemCount,
            'formatted_subtotal' => '$' . number_format($total, 2),
            'formatted_total' => '$' . number_format($total, 2),
            'item_text' => $itemCount . ' article' . ($itemCount > 1 ? 's' : '')
        ]);
    }

    /**
     * Sauvegarder le panier en session
     */
    private function saveSessionCart($cart)
    {
        Session::put('cart', $cart);
    }
}