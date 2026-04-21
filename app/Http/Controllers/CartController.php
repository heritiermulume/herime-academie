<?php

namespace App\Http\Controllers;

use App\Mail\GuestCheckoutPasswordMail;
use App\Models\AmbassadorPromoCode;
use App\Models\CartItem;
use App\Models\CartPackage;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\User;
use App\Services\CartGuestCheckoutService;
use App\Services\ContentPackageRecommendationService;
use App\Services\SSOService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public const GUEST_PAY_USER_ID_KEY = 'cart_guest_pay_user_id';

    public const GUEST_PAY_READY_KEY = 'cart_guest_pay_ready';

    /**
     * Retirer le mode « paiement invité compte existant » (session).
     */
    public static function clearGuestMonerooPayIntent(): void
    {
        Session::forget([
            self::GUEST_PAY_USER_ID_KEY,
            self::GUEST_PAY_READY_KEY,
        ]);
    }

    private function guestPayIntentUserId(): ?int
    {
        if (! Session::get(self::GUEST_PAY_READY_KEY)) {
            return null;
        }
        $id = (int) Session::get(self::GUEST_PAY_USER_ID_KEY);

        return $id > 0 ? $id : null;
    }

    /**
     * Lignes du panier pour l’affichage et le résumé (session invitée, BDD si intent paiement compte existant, ou connecté).
     */
    private function getCartItemsForCurrentRequest(): array
    {
        if (auth()->check()) {
            return $this->getDatabaseCartItems();
        }
        $guestUid = $this->guestPayIntentUserId();
        if ($guestUid !== null) {
            $user = User::find($guestUid);
            if ($user) {
                return $this->getDatabaseCartItems($user);
            }
        }

        return $this->getSessionCartItems();
    }

    /**
     * Afficher le contenu du panier
     */
    public function index()
    {
        $cartItems = $this->getCartItemsForCurrentRequest();

        // S'assurer que $cartItems est un tableau
        $cartItems = is_array($cartItems) ? $cartItems : $cartItems->toArray();

        // Calculer les totaux
        $subtotal = collect($cartItems)->sum('subtotal');
        $tax = 0; // Pour l'instant, pas de taxes
        $total = $subtotal + $tax;

        // Obtenir des recommandations intelligentes basées sur le contenu du panier
        $recommendedCourses = $this->getSmartRecommendations($cartItems);
        $recommendedPackages = app(ContentPackageRecommendationService::class)->forCartLines($cartItems);
        $popularPackages = app(ContentPackageRecommendationService::class)->popularForCartContext($cartItems);

        // Obtenir les cours populaires pour le panier vide
        $popularCourses = $this->getPopularCoursesForCart();

        // Récupérer le code promo appliqué depuis la session
        $appliedPromoCode = Session::get('applied_promo_code');

        $guestPayExistingAccountReady = ! Auth::check() && Session::get(self::GUEST_PAY_READY_KEY);

        return view('cart.index', compact(
            'cartItems',
            'subtotal',
            'tax',
            'total',
            'recommendedCourses',
            'recommendedPackages',
            'popularCourses',
            'popularPackages',
            'appliedPromoCode',
            'guestPayExistingAccountReady',
        ));
    }

    /**
     * Ajouter un cours au panier
     */
    public function add(Request $request)
    {
        try {
            try {
                $request->validate([
                    'content_id' => 'required_without:package_id|nullable|exists:contents,id',
                    'package_id' => 'required_without:content_id|nullable|exists:content_packages,id',
                ]);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($request->filled('package_id')) {
                return $this->addPackageToCart((int) $request->package_id);
            }

            $contentId = (int) $request->content_id;

            try {
                $course = Course::findOrFail($contentId);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contenu introuvable.',
                ], 404);
            }

            if (! $course->is_published) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce contenu n\'est pas disponible.',
                ], 404);
            }

            if (! $course->is_sale_enabled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce contenu n\'est pas actuellement disponible à l\'achat.',
                ], 403);
            }

            if ($course->is_free) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les contenus gratuits ne peuvent pas être ajoutés au panier. Inscrivez-vous directement.',
                ]);
            }

            if (auth()->check()) {
                if (auth()->user()->cartItems()->where('content_id', $contentId)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce contenu est déjà dans votre panier.',
                    ]);
                }

                $this->addToDatabaseCart($contentId);
                $cartCount = $this->totalCartLineCount();
            } else {
                $norm = $this->getSessionCartNormalized();
                if (in_array($contentId, $norm['contents'], true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce contenu est déjà dans votre panier.',
                    ]);
                }
                $norm['contents'][] = $contentId;
                $norm['contents'] = array_values(array_unique($norm['contents']));
                $this->saveSessionCartNormalized($norm);
                $cartCount = count($norm['contents']) + count($norm['packages']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Contenu ajouté au panier avec succès.',
                'cart_count' => $cartCount,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding content to cart', [
                'error' => $e->getMessage(),
                'content_id' => $request->content_id ?? null,
                'package_id' => $request->package_id ?? null,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'ajout au panier. Veuillez réessayer.',
            ], 500);
        }
    }

    /**
     * Supprimer un cours du panier
     */
    public function remove(Request $request)
    {
        $request->validate([
            'content_id' => 'required_without:package_id|nullable|exists:contents,id',
            'package_id' => 'required_without:content_id|nullable|exists:content_packages,id',
        ]);

        if ($request->filled('package_id')) {
            $packageId = (int) $request->package_id;
            if (auth()->check()) {
                CartPackage::where('user_id', auth()->id())
                    ->where('content_package_id', $packageId)
                    ->delete();
            } else {
                $norm = $this->getSessionCartNormalized();
                $norm['packages'] = array_values(array_filter($norm['packages'], fn ($id) => (int) $id !== $packageId));
                $this->saveSessionCartNormalized($norm);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pack retiré du panier.',
                'cart_count' => $this->totalCartLineCount(),
            ]);
        }

        $contentId = (int) $request->content_id;

        if (auth()->check()) {
            CartItem::where('user_id', auth()->id())
                ->where('content_id', $contentId)
                ->delete();
        } else {
            $norm = $this->getSessionCartNormalized();
            $norm['contents'] = array_values(array_filter($norm['contents'], fn ($id) => (int) $id !== $contentId));
            $this->saveSessionCartNormalized($norm);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contenu supprimé du panier.',
            'cart_count' => $this->totalCartLineCount(),
        ]);
    }

    /**
     * Vider le panier
     */
    public function clear()
    {
        if (auth()->check()) {
            auth()->user()->cartItems()->delete();
            auth()->user()->cartPackages()->delete();
        } else {
            Session::forget('cart');
        }

        return response()->json([
            'success' => true,
            'message' => 'Panier vidé avec succès.',
            'cart_count' => 0,
        ]);
    }

    /**
     * Obtenir le nombre d'articles dans le panier
     */
    public function count()
    {
        try {
            return response()->json([
                'count' => $this->totalCartLineCount(),
            ]);
        } catch (\Exception $e) {
            // Logger l'erreur pour le débogage
            \Log::error('Error getting cart count', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Retourner 0 en cas d'erreur pour éviter de casser l'interface
            return response()->json([
                'count' => 0,
            ], 200);
        }
    }

    /**
     * Préparer le paiement pour un invité : associer ou créer un compte, connecter, synchroniser le panier.
     */
    public function guestCheckoutPrepare(Request $request, CartGuestCheckoutService $guestCheckoutService, SSOService $ssoService)
    {
        if (auth()->check()) {
            return response()->json([
                'success' => true,
                'already_authenticated' => true,
                'csrf_token' => csrf_token(),
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Inclure le panier BDD pour l’intent « compte existant » (session déjà fusionnée et vidée).
        if (empty($this->getCartItemsForCurrentRequest())) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide.',
            ], 422);
        }

        try {
            $result = $guestCheckoutService->resolveUserForGuestCartCheckout(
                $validated['name'],
                $validated['email'],
                $validated['phone']
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez corriger les informations saisies.',
                'errors' => $e->errors(),
            ], 422);
        }

        $sync = $ssoService->syncGuestCheckoutUser($result['user'], $result['plain_password']);
        if (! $sync['ok']) {
            if ($result['plain_password'] !== null) {
                $result['user']->delete();

                return response()->json([
                    'success' => false,
                    'message' => $sync['message'] ?? 'Synchronisation avec Compte Herime impossible.',
                ], 422);
            }

            Log::warning('SSO guest sync failed for existing local user; continuing checkout', [
                'user_id' => $result['user']->id,
                'message' => $sync['message'] ?? null,
            ]);
        }

        $passwordEmailSent = null;
        if ($result['plain_password']) {
            try {
                Mail::to($result['user']->email)->send(
                    new GuestCheckoutPasswordMail($result['user'], $result['plain_password'])
                );
                $passwordEmailSent = true;
            } catch (\Throwable $e) {
                \Log::error('Guest checkout: échec envoi mail mot de passe (le parcours continue)', [
                    'user_id' => $result['user']->id,
                    'message' => $e->getMessage(),
                ]);
                $passwordEmailSent = false;
            }
        }

        $isNewAccount = $result['plain_password'] !== null;

        $this->mergeSessionCartIntoUser($result['user']);

        if ($isNewAccount) {
            // S'assurer qu'aucun ancien token SSO (potentiellement expiré/mauvais compte)
            // ne reste attaché à la nouvelle session locale.
            if ($request->hasSession()) {
                $request->session()->forget('sso_token');
            }
            Auth::login($result['user'], true);
            $request->session()->regenerate();
            // Marquer l'auto-login "checkout invité" afin d'éviter une déconnexion SSO stricte
            // immédiate avant la fin du paiement (pas encore de token SSO côté compte externe).
            if ($request->hasSession()) {
                $request->session()->put('guest_checkout_autologin_user_id', (int) $result['user']->id);
                $request->session()->put('guest_checkout_autologin_until', now()->addMinutes(20)->timestamp);
            }
        } else {
            Session::put(self::GUEST_PAY_USER_ID_KEY, $result['user']->id);
            Session::put(self::GUEST_PAY_READY_KEY, true);
            $request->session()->regenerate();
        }

        $message = $isNewAccount
            ? ($passwordEmailSent === false
                ? 'Compte créé. L’envoi du mot de passe par e-mail a échoué : utilisez « Mot de passe oublié » sur compte.herime.com ou contactez le support.'
                : 'Un compte a été créé. Consultez votre boîte e-mail pour votre mot de passe temporaire.')
            : 'Compte reconnu. Vous pouvez finaliser le paiement. Pour accéder à votre espace client, connectez-vous avec votre mot de passe.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'csrf_token' => csrf_token(),
            'password_email_sent' => $passwordEmailSent,
            'guest_pay_without_login' => ! $isNewAccount,
        ]);
    }

    /**
     * Copier le panier session vers le panier base du compte (sans exiger Auth::login).
     */
    private function mergeSessionCartIntoUser(User $user): void
    {
        $norm = $this->getSessionCartNormalized();

        foreach ($norm['contents'] as $contentId) {
            if (! $user->cartItems()->where('content_id', $contentId)->exists()) {
                CartItem::create([
                    'user_id' => $user->id,
                    'content_id' => (int) $contentId,
                ]);
            }
        }

        foreach ($norm['packages'] as $packageId) {
            if (! $user->cartPackages()->where('content_package_id', $packageId)->exists()) {
                CartPackage::create([
                    'user_id' => $user->id,
                    'content_package_id' => (int) $packageId,
                ]);
            }
        }

        Session::forget('cart');
    }

    /**
     * Afficher la page de checkout
     */
    public function checkout()
    {
        if (! auth()->check()) {
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

        $unavailableLabels = [];
        $cartItems = array_filter($cartItems, function ($item) use (&$unavailableLabels) {
            $type = $item['type'] ?? 'content';
            if ($type === 'package') {
                $package = $item['package'] ?? null;
                if (! $package) {
                    return false;
                }
                if (! $package->is_published || ! $package->is_sale_enabled) {
                    $unavailableLabels[] = $package->title;

                    return false;
                }

                return true;
            }
            $course = $item['course'] ?? null;
            if (! $course) {
                return false;
            }
            if (! $course->is_published || ! $course->is_sale_enabled) {
                $unavailableLabels[] = $course->title;

                return false;
            }

            return true;
        });
        $cartItems = array_values($cartItems);

        if (! empty($unavailableLabels)) {
            $message = 'Certains articles de votre panier ne sont plus disponibles : '.implode(', ', $unavailableLabels).'. Veuillez les retirer du panier.';

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
        if (! auth()->check()) {
            return false;
        }

        $userId = auth()->id();

        // Vérifier si l'utilisateur est inscrit au cours
        $isEnrolled = $course->isEnrolledBy($userId);
        if ($isEnrolled) {
            return true;
        }

        // Vérifier si l'utilisateur a acheté le cours (pour les cours payants)
        if (! $course->is_free) {
            $hasPurchased = \App\Models\Order::where('user_id', $userId)
                ->whereIn('status', ['paid', 'completed'])
                ->whereHas('orderItems', function ($query) use ($course) {
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
        $cartContentIds = [];

        // 1. Exclure les cours déjà dans le panier (y compris ceux contenus dans des packs)
        if (! empty($cartItems) && is_array($cartItems)) {
            $cartContentIds = collect($cartItems)->flatMap(function ($item) {
                if (($item['type'] ?? 'content') === 'package') {
                    $pkg = $item['package'] ?? null;
                    if ($pkg instanceof ContentPackage) {
                        return $pkg->contents->pluck('id')->all();
                    }

                    return [];
                }
                if (isset($item['course'])) {
                    $c = $item['course'];
                    $cid = is_object($c) ? ($c->id ?? null) : ($c['id'] ?? null);

                    return $cid ? [(int) $cid] : [];
                }
                if (isset($item['id'])) {
                    return [(int) $item['id']];
                }
                if (is_object($item) && isset($item->course->id)) {
                    return [(int) $item->course->id];
                }
                if (is_object($item) && isset($item->id)) {
                    return [(int) $item->id];
                }

                return [];
            })->filter()->unique()->values()->toArray();

            $excludedIds = $excludedIds->merge($cartContentIds);
        }

        // 2. Exclure les cours gratuits (toujours)
        $freeContentIds = Course::published()
            ->where('is_free', true)
            ->pluck('id')
            ->toArray();
        $excludedIds = $excludedIds->merge($freeContentIds);

        // 3. Utilisateur connecté ou invité en « paiement compte existant » : exclure contenus déjà accessibles
        $userForExclusions = auth()->user();
        if (! $userForExclusions) {
            $guestUid = $this->guestPayIntentUserId();
            if ($guestUid) {
                $userForExclusions = User::find($guestUid);
            }
        }
        if ($userForExclusions) {
            $purchasedCourseIds = $userForExclusions->getRecommendationExcludedContentIds();
            $excludedIds = $excludedIds->merge($purchasedCourseIds);

            \Log::info('Cours exclus pour l\'utilisateur '.$userForExclusions->id.':', [
                'cart_content_ids' => $cartContentIds,
                'purchased_content_ids' => $purchasedCourseIds,
                'free_content_ids' => $freeContentIds,
                'total_excluded' => count($excludedIds->toArray()),
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
        $pseudoCart = $this->getCartItemsForCurrentRequest();
        $excludedCourseIds = $this->getExcludedCourseIds($pseudoCart);

        // Obtenir les cours populaires avec filtrage approprié
        $popularCourses = Course::published()
            ->saleEnabled()
            ->where('is_free', false) // Exclure les cours gratuits
            ->whereNotIn('id', $excludedCourseIds) // Exclure les cours déjà dans le panier, achetés, etc.
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->withCount('enrollments') // Pour le tri par nombre de clients
            ->orderBy('enrollments_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20) // Augmenter la limite pour compenser le filtrage
            ->get();

        // Filtrage manuel supplémentaire pour s'assurer qu'aucun cours indésirable ne passe
        $popularCourses = $popularCourses->filter(function ($course) {
            // Exclure les cours gratuits (double vérification)
            if ($course->is_free) {
                return false;
            }

            if (! $course->is_sale_enabled) {
                return false;
            }

            if (auth()->check() && $this->isCoursePurchased($course)) {
                return false;
            }

            return true;
        });

        // Ajouter les statistiques à chaque cours
        $popularCourses = $popularCourses->map(function ($course) {
            $course->stats = [
                'total_lessons' => $course->sections ? $course->sections->sum(function ($section) {
                    return $section->lessons ? $section->lessons->count() : 0;
                }) : 0,
                'total_duration' => $course->sections ? $course->sections->sum(function ($section) {
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

        if (empty($cartItems) || ! is_array($cartItems)) {
            // Si le panier est vide, recommander des cours populaires
            $courses = Course::published()
                ->saleEnabled()
                ->where('is_free', false) // Force l'exclusion des cours gratuits
                ->whereNotIn('id', $excludedCourseIds)
                ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(4)
                ->get();

            // Double vérification : filtrer manuellement les cours gratuits et déjà accessibles
            $courses = $courses->filter(function ($course) {
                if ($course->is_free) {
                    return false;
                }

                if (! $course->is_sale_enabled) {
                    return false;
                }

                if (auth()->check() && $this->isCoursePurchased($course)) {
                    return false;
                }

                return true;
            });

            // Ajouter les statistiques calculées
            return $courses->map(function ($course) {
                $course->stats = [
                    'total_lessons' => $course->sections ? $course->sections->sum(function ($section) {
                        return $section->lessons ? $section->lessons->count() : 0;
                    }) : 0,
                    'total_duration' => $course->sections ? $course->sections->sum(function ($section) {
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
        $contentOnly = collect($cartItems)->filter(fn ($i) => ($i['type'] ?? 'content') === 'content');
        $cartContentIds = $contentOnly->pluck('course.id')->filter()->unique()->values()->toArray();
        $cartCategories = $contentOnly->pluck('course.category_id')->filter()->unique()->values()->toArray();
        $cartLevels = $contentOnly->pluck('course.level')->filter()->unique()->values()->toArray();
        $cartInstructors = $contentOnly->pluck('course.provider_id')->filter()->unique()->values()->toArray();

        // 1. Cours complémentaires de la même catégorie (ignoré si panier sans contenu seul : ex. que des packs)
        $categoryRecommendations = collect();
        if ($cartCategories !== []) {
            $categoryRecommendations = Course::published()
                ->saleEnabled()
                ->where('is_free', false)
                ->whereIn('category_id', $cartCategories)
                ->whereNotIn('id', $excludedCourseIds)
                ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(2)
                ->get();

            $categoryRecommendations = $categoryRecommendations->filter(function ($course) {
                return ! $course->is_free && $course->is_sale_enabled && ! $this->isCoursePurchased($course);
            });
        }

        $recommendations = $recommendations->merge($categoryRecommendations);

        // 2. Cours du même niveau de difficulté (pour progression)
        $levelRecommendations = collect();
        if ($cartLevels !== []) {
            $levelRecommendations = Course::published()
                ->saleEnabled()
                ->where('is_free', false)
                ->whereIn('level', $cartLevels)
                ->whereNotIn('id', $excludedCourseIds)
                ->whereNotIn('id', $recommendations->pluck('id'))
                ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->get();

            $levelRecommendations = $levelRecommendations->filter(function ($course) {
                return ! $course->is_free && $course->is_sale_enabled && ! $this->isCoursePurchased($course);
            });
        }

        $recommendations = $recommendations->merge($levelRecommendations);

        // 3. Contenus du même prestataire (si l'utilisateur aime le style)
        $providerRecommendations = collect();
        if ($cartInstructors !== []) {
            $providerRecommendations = Course::published()
                ->saleEnabled()
                ->where('is_free', false)
                ->whereIn('provider_id', $cartInstructors)
                ->whereNotIn('id', $excludedCourseIds)
                ->whereNotIn('id', $recommendations->pluck('id'))
                ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(1)
                ->get();

            $providerRecommendations = $providerRecommendations->filter(function ($course) {
                return ! $course->is_free && $course->is_sale_enabled && ! $this->isCoursePurchased($course);
            });
        }

        $recommendations = $recommendations->merge($providerRecommendations);

        // 4. Cours populaires récents (tendance)
        $trendingRecommendations = Course::published()
            ->saleEnabled()
            ->where('is_free', false)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->where('created_at', '>=', now()->subMonth())
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $trendingRecommendations = $trendingRecommendations->filter(function ($course) {
            return ! $course->is_free && $course->is_sale_enabled && ! $this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($trendingRecommendations);

        // 5. Si l'utilisateur est connecté, recommandations basées sur ses préférences
        if (auth()->check()) {
            $userEnrollments = auth()->user()->enrollments()
                ->with('course')
                ->get()
                ->pluck('course.category_id')
                ->filter(fn ($id) => $id !== null && $id !== '')
                ->unique()
                ->values()
                ->all();

            if ($userEnrollments !== []) {
                $userPreferenceRecommendations = Course::published()
                    ->saleEnabled()
                    ->where('is_free', false)
                    ->whereIn('category_id', $userEnrollments)
                    ->whereNotIn('id', $excludedCourseIds)
                    ->whereNotIn('id', $recommendations->pluck('id'))
                    ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->get();

                // Filtrer manuellement les cours gratuits et achetés
                $userPreferenceRecommendations = $userPreferenceRecommendations->filter(function ($course) {
                    return ! $course->is_free && $course->is_sale_enabled && ! $this->isCoursePurchased($course);
                });

                $recommendations = $recommendations->merge($userPreferenceRecommendations);
            }
        }

        // 6. Si on n'a pas assez de recommandations, ajouter des cours populaires
        if ($recommendations->count() < 4) {
            $popularRecommendations = Course::published()
                ->saleEnabled()
                ->where('is_free', false)
                ->whereNotIn('id', $excludedCourseIds)
                ->whereNotIn('id', $recommendations->pluck('id'))
                ->with(['provider', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(4 - $recommendations->count())
                ->get();

            // Filtrer manuellement les cours gratuits et achetés
            $popularRecommendations = $popularRecommendations->filter(function ($course) {
                return ! $course->is_free && $course->is_sale_enabled && ! $this->isCoursePurchased($course);
            });

            $recommendations = $recommendations->merge($popularRecommendations);
        }

        // Filtrage final : pas de gratuit ni de contenu déjà accessible (achat / inscription)
        $finalRecommendations = $recommendations->filter(function ($course) {
            if ($course->is_free) {
                return false;
            }

            if (! $course->is_sale_enabled) {
                return false;
            }

            if (auth()->check() && $this->isCoursePurchased($course)) {
                return false;
            }

            return true;
        })->shuffle()->take(4);

        return $finalRecommendations->map(function ($course) {
            // Charger les relations nécessaires si elles ne sont pas déjà chargées
            if (! $course->relationLoaded('sections')) {
                $course->load(['sections.lessons', 'reviews', 'enrollments']);
            }

            // Ajouter les statistiques calculées
            $course->stats = [
                'total_lessons' => $course->sections ? $course->sections->sum(function ($section) {
                    return $section->lessons ? $section->lessons->count() : 0;
                }) : 0,
                'total_duration' => $course->sections ? $course->sections->sum(function ($section) {
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
        $cartItems = $this->getCartItemsForCurrentRequest();

        // S'assurer que $cartItems est un tableau
        $cartItems = is_array($cartItems) ? $cartItems : $cartItems->toArray();

        $recommendedCourses = $this->getSmartRecommendations($cartItems);
        $recommendedPackages = app(ContentPackageRecommendationService::class)->forCartLines($cartItems);

        $html = view('cart.partials.recommendations', compact('recommendedCourses', 'recommendedPackages'))->render();

        return response()->json([
            'success' => true,
            'html' => $html,
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
                    'subtotal' => $course->current_price * $quantity,
                ];
                $total += $course->current_price * $quantity;
            }
        }

        return response()->json([
            'items' => $cartItems,
            'total' => $total,
            'count' => array_sum($cart),
        ]);
    }

    /**
     * Obtenir les articles du panier depuis la base de données
     */
    private function getDatabaseCartItems(?User $user = null): array
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return [];
        }

        $lines = [];

        $dbItems = $user->cartItems()->with([
            'course.category',
            'course.provider',
            'course.reviews',
            'course.enrollments',
            'course.sections.lessons',
        ])->get();

        foreach ($dbItems as $item) {
            $course = $item->course;
            if ($course && $course->is_published && $course->is_sale_enabled) {
                $course->stats = $this->buildCourseStats($course);
                $lines[] = [
                    'type' => 'content',
                    'course' => $course,
                    'quantity' => 1,
                    'price' => $course->current_price,
                    'subtotal' => $course->current_price,
                ];
            }
        }

        $pkgRows = $user->cartPackages()->with([
            'contentPackage' => fn ($q) => $q->withCount('contents'),
            'contentPackage.contents' => fn ($q) => $q->orderByPivot('sort_order'),
        ])->get();

        foreach ($pkgRows as $row) {
            $package = $row->contentPackage;
            if ($package && $package->is_published && $package->is_sale_enabled && $package->contents->isNotEmpty()) {
                $lines[] = [
                    'type' => 'package',
                    'package' => $package,
                    'quantity' => 1,
                    'price' => $package->effective_price,
                    'subtotal' => $package->effective_price,
                ];
            }
        }

        return $lines;
    }

    private function getSessionCartItems(): array
    {
        $norm = $this->getSessionCartNormalized();
        $lines = [];

        foreach ($norm['contents'] as $contentId) {
            $course = Course::with([
                'category',
                'provider',
                'reviews',
                'enrollments',
                'sections.lessons',
            ])->find($contentId);
            if ($course && $course->is_published && $course->is_sale_enabled) {
                $course->stats = $this->buildCourseStats($course);
                $lines[] = [
                    'type' => 'content',
                    'course' => $course,
                    'quantity' => 1,
                    'price' => $course->current_price,
                    'subtotal' => $course->current_price,
                ];
            }
        }

        foreach ($norm['packages'] as $packageId) {
            $package = ContentPackage::query()
                ->withCount('contents')
                ->with(['contents' => fn ($q) => $q->orderByPivot('sort_order')])
                ->find($packageId);
            if ($package && $package->is_published && $package->is_sale_enabled && $package->contents->isNotEmpty()) {
                $lines[] = [
                    'type' => 'package',
                    'package' => $package,
                    'quantity' => 1,
                    'price' => $package->effective_price,
                    'subtotal' => $package->effective_price,
                ];
            }
        }

        return $lines;
    }

    /**
     * Ajouter un cours au panier en base de données
     */
    private function addToDatabaseCart($contentId)
    {
        CartItem::create([
            'user_id' => auth()->id(),
            'content_id' => $contentId,
        ]);
    }

    /**
     * Synchroniser le panier de session avec la base de données lors de la connexion
     */
    public function syncSessionToDatabase()
    {
        if (! auth()->check()) {
            return;
        }

        $norm = $this->getSessionCartNormalized();

        foreach ($norm['contents'] as $contentId) {
            if (! auth()->user()->cartItems()->where('content_id', $contentId)->exists()) {
                $this->addToDatabaseCart((int) $contentId);
            }
        }

        foreach ($norm['packages'] as $packageId) {
            if (! auth()->user()->cartPackages()->where('content_package_id', $packageId)->exists()) {
                CartPackage::create([
                    'user_id' => auth()->id(),
                    'content_package_id' => (int) $packageId,
                ]);
            }
        }

        Session::forget('cart');
    }

    /**
     * @deprecated Utiliser getSessionCartNormalized()
     */
    private function getSessionCart()
    {
        $n = $this->getSessionCartNormalized();

        return $n['contents'];
    }

    /**
     * Obtenir seulement le résumé du panier (pour mise à jour AJAX)
     */
    public function getSummary(Request $request)
    {
        $cartItems = $this->getCartItemsForCurrentRequest();

        // S'assurer que $cartItems est un tableau
        $cartItems = is_array($cartItems) ? $cartItems : $cartItems->toArray();

        $cartItems = array_filter($cartItems, function ($item) {
            $type = $item['type'] ?? 'content';
            if ($type === 'package') {
                $package = $item['package'] ?? null;

                return $package && $package->is_published && $package->is_sale_enabled;
            }
            $course = $item['course'] ?? null;

            return $course && $course->is_published && $course->is_sale_enabled;
        });

        // Réindexer le tableau après filtrage
        $cartItems = array_values($cartItems);

        $total = collect($cartItems)->sum('subtotal');
        $itemCount = count($cartItems);

        // Utiliser CurrencyHelper pour le formatage cohérent
        $formattedSubtotal = \App\Helpers\CurrencyHelper::formatWithSymbol($total);
        $formattedTotal = \App\Helpers\CurrencyHelper::formatWithSymbol($total);

        return response()->json([
            'success' => true,
            'subtotal' => $total,
            'total' => $total,
            'item_count' => $itemCount,
            'formatted_subtotal' => $formattedSubtotal,
            'formatted_total' => $formattedTotal,
            'item_text' => $itemCount.' article'.($itemCount > 1 ? 's' : ''),
        ]);
    }

    /**
     * @deprecated Utiliser saveSessionCartNormalized()
     */
    private function saveSessionCart($cart)
    {
        $this->saveSessionCartNormalized([
            'contents' => is_array($cart) ? array_map('intval', $cart) : [],
            'packages' => [],
        ]);
    }

    private function buildCourseStats(Course $course): array
    {
        return [
            'total_lessons' => $course->sections ? $course->sections->sum(function ($section) {
                return $section->lessons ? $section->lessons->count() : 0;
            }) : 0,
            'total_duration' => $course->sections ? $course->sections->sum(function ($section) {
                return $section->lessons ? $section->lessons->sum('duration') : 0;
            }) : 0,
            'total_customers' => $course->enrollments ? $course->enrollments->count() : 0,
            'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
            'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
        ];
    }

    /**
     * @return array{contents: int[], packages: int[]}
     */
    private function getSessionCartNormalized(): array
    {
        $raw = Session::get('cart', []);
        if (is_array($raw) && (isset($raw['contents']) || isset($raw['packages']))) {
            $contents = array_values(array_unique(array_map('intval', $raw['contents'] ?? [])));
            $packages = array_values(array_unique(array_map('intval', $raw['packages'] ?? [])));

            return ['contents' => $contents, 'packages' => $packages];
        }
        if (is_array($raw) && array_is_list($raw)) {
            return [
                'contents' => array_values(array_unique(array_map('intval', $raw))),
                'packages' => [],
            ];
        }

        return ['contents' => [], 'packages' => []];
    }

    /**
     * @param  array{contents: int[], packages: int[]}  $normalized
     */
    private function saveSessionCartNormalized(array $normalized): void
    {
        Session::put('cart', [
            'contents' => array_values(array_unique($normalized['contents'] ?? [])),
            'packages' => array_values(array_unique($normalized['packages'] ?? [])),
        ]);
    }

    private function totalCartLineCount(): int
    {
        if (auth()->check()) {
            return auth()->user()->cartItems()->count() + auth()->user()->cartPackages()->count();
        }
        $guestUid = $this->guestPayIntentUserId();
        if ($guestUid !== null) {
            $u = User::find($guestUid);
            if ($u) {
                return $u->cartItems()->count() + $u->cartPackages()->count();
            }
        }
        $n = $this->getSessionCartNormalized();

        return count($n['contents']) + count($n['packages']);
    }

    private function addPackageToCart(int $packageId)
    {
        $package = ContentPackage::query()
            ->with(['contents'])
            ->find($packageId);

        if (! $package || ! $package->is_published || ! $package->is_sale_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Ce pack n\'est pas disponible à l\'achat.',
            ], 404);
        }

        if ($package->contents->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Ce pack ne contient aucun contenu.',
            ], 422);
        }

        foreach ($package->contents as $course) {
            if (! $course->is_published || ! $course->is_sale_enabled || $course->is_free) {
                return response()->json([
                    'success' => false,
                    'message' => 'Un ou plusieurs contenus du pack ne sont pas disponibles à l\'achat.',
                ], 422);
            }
        }

        if (auth()->check()) {
            if (auth()->user()->cartPackages()->where('content_package_id', $packageId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce pack est déjà dans votre panier.',
                ]);
            }
            CartPackage::create([
                'user_id' => auth()->id(),
                'content_package_id' => $packageId,
            ]);
        } else {
            $norm = $this->getSessionCartNormalized();
            if (in_array($packageId, $norm['packages'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce pack est déjà dans votre panier.',
                ]);
            }
            $norm['packages'][] = $packageId;
            $norm['packages'] = array_values(array_unique($norm['packages']));
            $this->saveSessionCartNormalized($norm);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pack ajouté au panier avec succès.',
            'cart_count' => $this->totalCartLineCount(),
        ]);
    }

    /**
     * Appliquer un code promo
     */
    public function applyPromoCode(Request $request)
    {
        try {
            $request->validate([
                'promo_code' => 'required|string|max:50',
            ]);

            $code = strtoupper(trim($request->promo_code));

            // Rechercher le code promo
            $promoCode = AmbassadorPromoCode::where('code', $code)
                ->with('ambassador.user')
                ->first();

            if (! $promoCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code promo invalide ou introuvable.',
                ], 404);
            }

            // Vérifier si le code promo est valide
            if (! $promoCode->isValid()) {
                $message = 'Ce code promo n\'est plus valide.';

                if (! $promoCode->is_active) {
                    $message = 'Ce code promo a été désactivé.';
                } elseif ($promoCode->expires_at && $promoCode->expires_at->isPast()) {
                    $message = 'Ce code promo a expiré.';
                } elseif ($promoCode->max_usage && $promoCode->usage_count >= $promoCode->max_usage) {
                    $message = 'Ce code promo a atteint sa limite d\'utilisation.';
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 400);
            }

            // Vérifier si l'ambassadeur est actif
            if (! $promoCode->ambassador->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'L\'ambassadeur associé à ce code n\'est plus actif.',
                ], 400);
            }

            // Stocker le code promo dans la session
            Session::put('applied_promo_code', [
                'code' => $promoCode->code,
                'ambassador_id' => $promoCode->ambassador_id,
                'promo_code_id' => $promoCode->id,
                'ambassador_name' => $promoCode->ambassador->user->name ?? 'Ambassadeur',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Code promo appliqué avec succès!',
                'promo_code' => $promoCode->code,
                'ambassador_name' => $promoCode->ambassador->user->name ?? 'Ambassadeur',
                'discount' => 0, // Pour l'instant pas de réduction, juste le tracking
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez entrer un code promo valide.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error applying promo code', [
                'error' => $e->getMessage(),
                'code' => $request->promo_code ?? null,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'application du code promo.',
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
                'message' => 'Code promo retiré avec succès.',
            ]);

        } catch (\Exception $e) {
            \Log::error('Error removing promo code', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du retrait du code promo.',
            ], 500);
        }
    }
}
