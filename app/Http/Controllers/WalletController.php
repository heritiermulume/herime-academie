<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletPayout;
use App\Models\Ambassador;
use App\Services\MonerooPayoutService;
use App\Services\MonerooPayoutMethodsService;
use App\Services\WalletAutoReleaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    protected $monerooPayoutService;
    protected $autoReleaseService;
    protected $payoutMethodsService;

    public function __construct(
        MonerooPayoutService $monerooPayoutService,
        WalletAutoReleaseService $autoReleaseService,
        MonerooPayoutMethodsService $payoutMethodsService
    ) {
        $this->middleware('auth');
        $this->monerooPayoutService = $monerooPayoutService;
        $this->autoReleaseService = $autoReleaseService;
        $this->payoutMethodsService = $payoutMethodsService;
    }

    /**
     * Afficher le dashboard du wallet
     */
    public function index()
    {
        $user = Auth::user();
        
        // Les administrateurs (admin) et super-utilisateurs (super_user) ont accès à toutes les sections
        // La méthode isAdmin() vérifie à la fois 'admin' et 'super_user'
        $isAdmin = $user->isAdmin();
        
        // Vérifier que l'utilisateur est un ambassadeur actif, un provider, ou un administrateur/super admin
        $ambassador = null;
        if ($isAdmin) {
            // Les admins et super admins peuvent accéder, pas besoin de vérifier le rôle ambassador
            // Mais on peut essayer de récupérer l'ambassadeur s'il existe
            $ambassador = Ambassador::where('user_id', $user->id)
                ->where('is_active', true)
                ->first();
        } elseif ($user->hasRole('ambassador')) {
            $ambassador = Ambassador::where('user_id', $user->id)
                ->where('is_active', true)
                ->firstOrFail();
        } elseif (!$user->hasRole('provider')) {
            abort(403, 'Accès réservé aux ambassadeurs, providers, administrateurs et super administrateurs');
        }

        // Créer un wallet si l'utilisateur n'en a pas
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            [
                'currency' => config('services.moneroo.default_currency', 'USD'),
                'balance' => 0,
                'pending_balance' => 0,
                'total_earned' => 0,
                'total_withdrawn' => 0,
                'is_active' => true,
            ]
        );

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès au wallet
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        // Recharger le wallet si des fonds ont été libérés
        if ($releasedCount > 0) {
            $wallet->refresh();
            session()->flash('success', "{$releasedCount} fond(s) ont été automatiquement libérés et sont maintenant disponibles au retrait !");
        }

        // Récupérer les statistiques du wallet
        $stats = $wallet->getStats();

        // Récupérer les transactions récentes (20 dernières)
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Récupérer les payouts récents (10 derniers)
        $payouts = $wallet->payouts()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Récupérer les payouts en attente
        $pendingPayouts = $wallet->pendingPayouts();

        // Données fournisseur (pays et opérateurs) depuis l'API Moneroo
        $monerooData = $this->payoutMethodsService->getPayoutMethods();

        return view('wallet.index', compact(
            'wallet',
            'ambassador',
            'stats',
            'transactions',
            'payouts',
            'pendingPayouts',
            'monerooData'
        ));
    }

    /**
     * Afficher les transactions du wallet
     */
    public function transactions(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès aux transactions
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
        }

        // Validation des entrées
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:credit,debit,commission,payout,refund,bonus',
            'status' => 'nullable|string|in:completed,pending,failed,cancelled',
            'from' => 'nullable|date|before_or_equal:today',
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:today',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0|gte:min_amount',
            'sort_by' => 'nullable|string|in:created_at,amount,balance_after',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|in:10,20,30,50,100',
        ]);

        $query = $wallet->transactions();

        // 🔒 PROTECTION : S'assurer que seules les transactions de l'utilisateur sont accessibles
        $query->whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Recherche globale
        if ($request->filled('search')) {
            $searchTerm = $validated['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('reference', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtrer par type
        if ($request->filled('type')) {
            $query->where('type', $validated['type']);
        }

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        // Filtrer par période
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        // Filtrer par montant
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $validated['min_amount']);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $validated['max_amount']);
        }

        // Tri
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $transactions = $query->paginate($perPage)->withQueryString();

        return view('wallet.transactions', compact('wallet', 'transactions'));
    }

    /**
     * Afficher les payouts du wallet
     */
    public function payouts(Request $request)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés lors de l'accès aux payouts
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
        }

        // Validation des entrées
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:pending,processing,completed,failed,cancelled',
            'from' => 'nullable|date|before_or_equal:today',
            'to' => 'nullable|date|after_or_equal:from|before_or_equal:today',
            'sort_by' => 'nullable|string|in:created_at,amount',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|in:10,20,30,50,100',
        ]);

        $query = $wallet->payouts();

        // 🔒 PROTECTION : S'assurer que seuls les payouts de l'utilisateur sont accessibles
        $query->whereHas('wallet', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });

        // Recherche globale
        if ($request->filled('search')) {
            $searchTerm = $validated['search'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('moneroo_id', 'like', '%' . $searchTerm . '%')
                  ->orWhere('phone', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filtrer par statut
        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        // Filtrer par période
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        // Tri
        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = $validated['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $validated['per_page'] ?? 20;
        $payouts = $query->paginate($perPage)->withQueryString();

        return view('wallet.payouts', compact('wallet', 'payouts'));
    }

    /**
     * Afficher le formulaire de retrait
     */
    public function createPayout()
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés avant de créer un payout
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
            session()->flash('success', "{$releasedCount} fond(s) ont été automatiquement libérés et sont maintenant disponibles au retrait !");
        }

        $minPayout = (float) \App\Models\Setting::get('wallet_minimum_payout_amount', 5);
        if ($wallet->available_balance < $minPayout) {
            return redirect()->route('wallet.index')
                ->with('warning', "Le montant minimum de retrait est de {$minPayout} {$wallet->currency}. Votre solde disponible ({$wallet->available_balance} {$wallet->currency}) ne permet pas encore de demander un retrait.");
        }

        // Données fournisseur (pays et opérateurs) depuis l'API Moneroo
        $monerooData = $this->payoutMethodsService->getPayoutMethods();

        return view('wallet.create-payout', compact('wallet', 'monerooData'));
    }

    /**
     * Initier un retrait
     */
    public function storePayout(Request $request)
    {
        $minPayout = (float) \App\Models\Setting::get('wallet_minimum_payout_amount', 5);

        // 🔒 PROTECTION : Validation stricte des entrées
        $validated = $request->validate([
            'amount' => "required|numeric|min:{$minPayout}|max:100000",
            'method' => 'required|string|max:64|regex:/^[a-zA-Z0-9_-]+$/',
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'country' => 'required|string|size:2|alpha',
            'currency' => 'required|string|size:3|alpha',
            'description' => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => "Le montant minimum est de {$minPayout}.",
            'amount.max' => 'Le montant maximum est de 100,000.',
            'method.required' => 'La méthode de paiement est obligatoire.',
            'method.regex' => 'La méthode de paiement sélectionnée n\'est pas valide.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.regex' => 'Le format du numéro de téléphone n\'est pas valide.',
            'country.required' => 'Le pays est obligatoire.',
            'country.alpha' => 'Le pays sélectionné n\'est pas valide.',
            'currency.required' => 'La devise est obligatoire.',
            'currency.alpha' => 'La devise sélectionnée n\'est pas valide.',
        ]);

        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
            
        $wallet = Wallet::where('user_id', $user->id)->firstOrFail();

        // 🔓 LIBÉRATION AUTOMATIQUE : Libérer les fonds expirés avant de vérifier le solde
        $releasedCount = $this->autoReleaseService->releaseExpiredHoldsForWallet($wallet);
        
        if ($releasedCount > 0) {
            $wallet->refresh();
            Log::info('Fonds automatiquement libérés avant retrait', [
                'wallet_id' => $wallet->id,
                'released_count' => $releasedCount,
            ]);
        }

        // Vérifier que le solde atteint le montant minimum de retrait
        if ($wallet->available_balance < $minPayout) {
            return redirect()->back()
                ->with('error', "Le montant minimum de retrait est de {$minPayout} {$wallet->currency}. Votre solde disponible ({$wallet->available_balance} {$wallet->currency}) ne permet pas encore de demander un retrait.")
                ->withInput();
        }

        // Vérifier que le wallet a suffisamment de solde DISPONIBLE
        if (!$wallet->hasBalance($request->amount)) {
            $heldInfo = '';
            if ($wallet->held_balance > 0) {
                $heldInfo = " Vous avez {$wallet->held_balance} {$wallet->currency} en période de blocage qui seront bientôt disponibles.";
            }
            
            return redirect()->back()
                ->with('error', "Solde disponible insuffisant. Vous avez {$wallet->available_balance} {$wallet->currency} disponibles, mais vous essayez de retirer {$request->amount} {$request->currency}.{$heldInfo}")
                ->withInput();
        }

        // Initier le payout via Moneroo avec les données validées
        $result = $this->monerooPayoutService->initiateWalletPayout(
            $wallet,
            $validated['amount'],
            $validated['currency'],
            $validated['phone'],
            $validated['method'],
            $validated['country'],
            $validated['description'] ?? null
        );

        if ($result['success']) {
            return redirect()->route('wallet.index')
                ->with('success', 'Votre demande de retrait a été initiée avec succès ! Elle sera traitée dans les prochaines minutes.');
        } else {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'initiation du retrait : ' . ($result['error'] ?? 'Erreur inconnue'))
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'un payout
     */
    public function showPayout(WalletPayout $payout)
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔒 PROTECTION : Vérifier que le payout appartient bien à l'utilisateur
        if ($payout->wallet->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce retrait.');
        }

        return view('wallet.show-payout', compact('payout'));
    }

    /**
     * Annuler un payout en attente
     */
    public function cancelPayout(WalletPayout $payout)
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔒 PROTECTION : Vérifier que le payout appartient bien à l'utilisateur
        if ($payout->wallet->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce retrait.');
        }

        // Vérifier que le payout peut être annulé
        if (!$payout->canBeCancelled()) {
            return redirect()->back()
                ->with('error', 'Ce retrait ne peut pas être annulé car il est déjà en cours de traitement ou terminé.');
        }

        // Annuler le payout
        if ($payout->cancel('Annulé par l\'utilisateur')) {
            return redirect()->route('wallet.index')
                ->with('success', 'Le retrait a été annulé avec succès. Le montant a été remboursé dans votre wallet.');
        } else {
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'annulation du retrait. Veuillez réessayer ou contacter le support.');
        }
    }

    /**
     * Vérifier le statut d'un payout auprès de Moneroo
     */
    public function checkPayoutStatus(WalletPayout $payout)
    {
        $user = Auth::user();
        
        // 🔒 PROTECTION : Vérifier que l'utilisateur est un ambassadeur actif
        $ambassador = Ambassador::where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();

        // 🔒 PROTECTION : Vérifier que le payout appartient bien à l'utilisateur
        if ($payout->wallet->user_id !== $user->id) {
            abort(403, 'Vous n\'avez pas accès à ce retrait.');
        }

        if (!$payout->moneroo_id) {
            return redirect()->back()
                ->with('error', 'Ce retrait n\'a pas encore été envoyé à Moneroo.');
        }

        // Vérifier le statut auprès de Moneroo
        $result = $this->monerooPayoutService->checkWalletPayoutStatus($payout->moneroo_id);

        if ($result['success']) {
            return redirect()->back()
                ->with('success', 'Le statut du retrait a été mis à jour : ' . ($result['status'] ?? 'Inconnu'));
        } else {
            return redirect()->back()
                ->with('error', 'Erreur lors de la vérification du statut : ' . ($result['error'] ?? 'Erreur inconnue'));
        }
    }

    /**
     * Webhook Moneroo pour les payouts wallet
     */
    public function webhookPayout(Request $request)
    {
        // Vérifier la signature du webhook si nécessaire
        // TODO: Implémenter la vérification de la signature Moneroo

        Log::info('Moneroo webhook payout reçu', [
            'data' => $request->all(),
        ]);

        // Traiter le callback
        $success = $this->monerooPayoutService->handleWalletPayoutCallback($request->all());

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Webhook traité avec succès',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du webhook',
            ], 400);
        }
    }
}
