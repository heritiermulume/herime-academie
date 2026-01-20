<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CartItem;
use App\Models\AmbassadorPromoCode;
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
        
        // Récupérer le code promo appliqué depuis la session
        $appliedPromoCode = Session::get('applied_promo_code');
        
        return view('cart.index', compact('cartItems', 'subtotal', 'tax', 'total', 'recommendedCourses', 'popularCourses', 'appliedPromoCode'));
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
                    'content_id' => 'required|exists:contents,id'
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                // Retourner une réponse JSON même en cas d'erreur de validation
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }

            $contentId = $request->content_id;
            
            // Récupérer le cours
            try {
                $course = Course::findOrFail($contentId);
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

            // Vérifier si la vente/inscription est activée
            if (!$course->is_sale_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce cours n\'est pas actuellement disponible à l\'achat.'
                ], 403);
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
                if (auth()->user()->cartItems()->where('content_id', $contentId)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce cours est déjà dans votre panier.'
                    ]);
                }

                // Utilisateur connecté : sauvegarder en base de données
                $this->addToDatabaseCart($contentId);
                $cartCount = auth()->user()->cartItems()->count();
            } else {
                // Vérifier si le cours est déjà dans le panier de session
                $cart = $this->getSessionCart();
                if (isset($cart[$contentId])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce cours est déjà dans votre panier.'
                    ]);
                }

                // Utilisateur non connecté : utiliser la session
                $this->addToSessionCart($contentId);
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
                'content_id' => $request->content_id ?? null,
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
            'content_id' => 'required|exists:contents,id'
        ]);

        $contentId = $request->content_id;

        if (auth()->check()) {
            // Utilisateur connecté : supprimer de la base de données
            CartItem::where('user_id', auth()->id())
                ->where('content_id', $contentId)
                ->delete();
            $cartCount = auth()->user()->cartItems()->count();
        } else {
            // Utilisateur non connecté : utiliser la session
            $cart = $this->getSessionCart();
            $cart = array_values(array_filter($cart, function($id) use ($contentId) {
                return $id != $contentId;
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

        // Filtrer les cours qui ne sont plus disponibles à la vente
        $unavailableCourses = [];
        $cartItems = array_filter($cartItems, function($item) use (&$unavailableCourses) {
            $course = $item['course'] ?? null;
            if (!$course) {
                return false;
            }
            
            // Ne pas inclure les cours non publiés ou non disponibles à la vente
            if (!$course->is_published || !$course->is_sale_enabled) {
                $unavailableCourses[] = $course->title;
                return false;
            }
            return true;
        });

        // Réindexer le tableau après filtrage
        $cartItems = array_values($cartItems);

        if (!empty($unavailableCourses)) {
            $message = 'Certains cours de votre panier ne sont plus disponibles : ' . implode(', ', $unavailableCourses) . '. Veuillez les retirer du panier.';
            return redirect()->route('cart.index')->with('warning', $message);
        }

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
                    $query->where('content_id', $course->id);
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
            $cartContentIds = collect($cartItems)->map(function($item) {
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
            
            $excludedIds = $excludedIds->merge($cartContentIds);
        }
        
        // 2. Exclure les cours gratuits (toujours)
        $freeContentIds = Course::published()
            ->where('is_free', true)
            ->pluck('id')
            ->toArray();
        $excludedIds = $excludedIds->merge($freeContentIds);
        
        // 3. Si l'utilisateur est connecté, exclure les cours déjà achetés ou auxquels il est inscrit
        if (auth()->check()) {
            $purchasedCourseIds = auth()->user()
                ->enrollments()
                ->whereIn('status', ['active', 'completed']) // Inclure les cours actifs ET complétés
                ->pluck('content_id')
                ->toArray();
            $excludedIds = $excludedIds->merge($purchasedCourseIds);
            
            // Debug: Log des cours exclus
            \Log::info('Cours exclus pour l\'utilisateur ' . auth()->id() . ':', [
                'cart_content_ids' => $cartContentIds ?? [],
                'purchased_content_ids' => $purchasedContentIds,
                'free_content_ids' => $freeContentIds,
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
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->withCount('enrollments') // Pour le tri par nombre de clients
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
                    ->where('content_id', $course->id)
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
                'total_customers' => $course->enrollments ? $course->enrollments->count() : 0,
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
                ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
                        ->where('content_id', $course->id)
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
                    'total_customers' => $course->enrollments ? $course->enrollments->count() : 0,
                    'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                    'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
                ];
                return $course;
            });
        }

        $recommendations = collect();
        $cartContentIds = collect($cartItems)->pluck('course.id')->toArray();
        $cartCategories = collect($cartItems)->pluck('course.category_id')->unique()->toArray();
        $cartLevels = collect($cartItems)->pluck('course.level')->unique()->toArray();
        $cartInstructors = collect($cartItems)->pluck('course.provider_id')->unique()->toArray();

        // 1. Cours complémentaires de la même catégorie
        $categoryRecommendations = Course::published()
            ->where('is_free', false)
            ->whereIn('category_id', $cartCategories)
            ->whereNotIn('id', $excludedCourseIds)
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $levelRecommendations = $levelRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($levelRecommendations);

        // 3. Contenus du même prestataire (si l'utilisateur aime le style)
        $providerRecommendations = Course::published()
            ->where('is_free', false)
            ->whereIn('provider_id', $cartInstructors)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $providerRecommendations = $providerRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($providerRecommendations);

        // 4. Cours populaires récents (tendance)
        $trendingRecommendations = Course::published()
            ->where('is_free', false)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
                    ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
                ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
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
                    ->where('content_id', $course->id)
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
                'total_customers' => $course->enrollments ? $course->enrollments->count() : 0,
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

        foreach ($cart as $contentId => $quantity) {
            $course = Course::find($contentId);
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
            'course.provider', 
            'course.reviews', 
            'course.enrollments',
            'course.sections.lessons'
        ])->get();
        
        return $cartItems->filter(function ($item) {
            $course = $item->course;
            // Ne pas inclure les cours non publiés ou non disponibles à la vente
            return $course && $course->is_published && $course->is_sale_enabled;
        })->map(function ($item) {
            $course = $item->course;
            
            // Ajouter les statistiques calculées avec vérifications
            $course->stats = [
                'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->count() : 0;
                }) : 0,
                'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->sum('duration') : 0;
                }) : 0,
                'total_customers' => $course->enrollments ? $course->enrollments->count() : 0,
                'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
            ];
            
            return [
                'course' => $course,
                'quantity' => 1,
                'price' => $course->current_price,
                'subtotal' => $course->current_price
            ];
        })->values()->toArray();
    }

    /**
     * Obtenir les articles du panier depuis la session
     */
    private function getSessionCartItems()
    {
        $cart = $this->getSessionCart();
        $cartItems = [];

        foreach ($cart as $contentId) {
            $course = Course::with([
                'category', 
                'provider', 
                'reviews', 
                'enrollments',
                'sections.lessons'
            ])->find($contentId);
            
            // Ne pas inclure les cours non publiés ou non disponibles à la vente
            if ($course && $course->is_published && $course->is_sale_enabled) {
                // Ajouter les statistiques calculées avec vérifications
                $course->stats = [
                    'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                        return $section->lessons ? $section->lessons->count() : 0;
                    }) : 0,
                    'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                        return $section->lessons ? $section->lessons->sum('duration') : 0;
                    }) : 0,
                    'total_customers' => $course->enrollments ? $course->enrollments->count() : 0,
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
    private function addToDatabaseCart($contentId)
    {
        CartItem::create([
            'user_id' => auth()->id(),
            'content_id' => $contentId
        ]);
    }

    /**
     * Ajouter un cours au panier en session
     */
    private function addToSessionCart($contentId)
    {
        $cart = $this->getSessionCart();
        
        if (!in_array($contentId, $cart)) {
            $cart[] = $contentId;
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
        
        foreach ($sessionCart as $contentId) {
            // Vérifier si le cours n'est pas déjà dans le panier de l'utilisateur
            if (!auth()->user()->cartItems()->where('content_id', $contentId)->exists()) {
                $this->addToDatabaseCart($contentId);
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

    /**
     * Appliquer un code promo
     */
    public function applyPromoCode(Request $request)
    {
        try {
            $request->validate([
                'promo_code' => 'required|string|max:50'
            ]);

            $code = strtoupper(trim($request->promo_code));

            // Rechercher le code promo
            $promoCode = AmbassadorPromoCode::where('code', $code)
                ->with('ambassador.user')
                ->first();

            if (!$promoCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code promo invalide ou introuvable.'
                ], 404);
            }

            // Vérifier si le code promo est valide
            if (!$promoCode->isValid()) {
                $message = 'Ce code promo n\'est plus valide.';
                
                if (!$promoCode->is_active) {
                    $message = 'Ce code promo a été désactivé.';
                } elseif ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
                    $message = 'Ce code promo a expiré.';
                } elseif ($promoCode->max_usage && $promoCode->usage_count >= $promoCode->max_usage) {
                    $message = 'Ce code promo a atteint sa limite d\'utilisation.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
            }

            // Vérifier si l'ambassadeur est actif
            if (!$promoCode->ambassador->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'ambassadeur associé à ce code n\'est plus actif.'
                ], 400);
            }

            // Stocker le code promo dans la session
            Session::put('applied_promo_code', [
                'code' => $promoCode->code,
                'ambassador_id' => $promoCode->ambassador_id,
                'promo_code_id' => $promoCode->id,
                'ambassador_name' => $promoCode->ambassador->user->name ?? 'Ambassadeur'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code promo appliqué avec succès!',
                'promo_code' => $promoCode->code,
                'ambassador_name' => $promoCode->ambassador->user->name ?? 'Ambassadeur',
                'discount' => 0 // Pour l'instant pas de réduction, juste le tracking
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez entrer un code promo valide.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error applying promo code', [
                'error' => $e->getMessage(),
                'code' => $request->promo_code ?? null,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'application du code promo.'
            ], 500);
        }
    }

    /**
     * Retirer un code promo
     */
    public function removePromoCode(Request $request)
    {
        try {
            // Retirer le code promo de la session
            Session::forget('applied_promo_code');

            return response()->json([
                'success' => true,
                'message' => 'Code promo retiré avec succès.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error removing promo code', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du retrait du code promo.'
            ], 500);
        }
    }
}