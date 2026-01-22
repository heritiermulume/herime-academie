<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorApplication;
use App\Models\Ambassador;
use App\Models\User;
use App\Models\AmbassadorCommission;
use App\Models\AmbassadorPromoCode;
use App\Models\Setting;
use App\Traits\HandlesBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AmbassadorApplicationStatusUpdated;

class AmbassadorController extends Controller
{
    use HandlesBulkActions;
    /**
     * Afficher la liste des candidatures d'ambassadeur
     */
    public function applications(Request $request)
    {
        $query = AmbassadorApplication::with(['user', 'reviewer'])
            ->latest();

        // Filtres
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(20);

        return view('admin.ambassadors.applications.index', compact('applications'));
    }

    /**
     * Afficher les détails d'une candidature
     */
    public function showApplication(AmbassadorApplication $application)
    {
        $application->load(['user', 'reviewer']);

        return view('admin.ambassadors.applications.show', compact('application'));
    }

    /**
     * Supprimer une candidature
     */
    public function destroyApplication(Request $request, $application)
    {
        // Gérer le binding de route - peut être un ID ou un modèle
        if (is_numeric($application)) {
            $application = AmbassadorApplication::findOrFail($application);
        } elseif (!$application instanceof AmbassadorApplication) {
            \Log::error('Type de paramètre invalide pour destroyApplication', [
                'type' => gettype($application),
                'value' => $application
            ]);
            return back()->with('error', 'Candidature introuvable.');
        }

        \Log::info('=== TENTATIVE DE SUPPRESSION DE CANDIDATURE ===', [
            'application_id' => $application->id,
            'user_id' => $application->user_id,
            'method' => $request->method(),
            'route' => $request->route()?->getName(),
            'url' => $request->fullUrl(),
            'auth_user' => Auth::id(),
            'all_params' => $request->all()
        ]);

        DB::beginTransaction();
        try {
            // Vérifier si un ambassadeur a été créé à partir de cette candidature
            $ambassador = Ambassador::where('application_id', $application->id)->first();
            if ($ambassador) {
                DB::rollBack();
                \Log::warning('BLOCAGE: Candidature liée à un ambassadeur', [
                    'application_id' => $application->id,
                    'ambassador_id' => $ambassador->id
                ]);
                return redirect()->route('admin.ambassadors.index', ['tab' => 'applications'])
                    ->with('error', 'Impossible de supprimer cette candidature car un ambassadeur a été créé à partir de celle-ci. Veuillez d\'abord supprimer l\'ambassadeur associé si vous souhaitez supprimer cette candidature.');
            }

            // Supprimer le fichier document si présent
            if ($application->document_path) {
                try {
                    $fileUploadService = app(\App\Services\FileUploadService::class);
                    $fileUploadService->delete($application->document_path, 'ambassador-applications');
                    \Log::info('Fichier document supprimé', ['path' => $application->document_path]);
                } catch (\Exception $e) {
                    \Log::warning('Erreur lors de la suppression du fichier de candidature: ' . $e->getMessage());
                }
            }

            $applicationId = $application->id;
            $application->delete();

            DB::commit();

            \Log::info('=== CANDIDATURE SUPPRIMÉE AVEC SUCCÈS ===', ['application_id' => $applicationId]);

            return redirect()->route('admin.ambassadors.index', ['tab' => 'applications'])
                ->with('success', 'Candidature supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== ERREUR LORS DE LA SUPPRESSION ===', [
                'application_id' => $application->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.ambassadors.index', ['tab' => 'applications'])
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour le statut d'une candidature
     */
    public function updateApplicationStatus(Request $request, AmbassadorApplication $application)
    {
        $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();
        try {
            $application->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Si approuvée, créer l'ambassadeur et générer un code promo
            if ($request->status === 'approved') {
                $ambassador = Ambassador::firstOrCreate(
                    ['user_id' => $application->user_id],
                    [
                        'application_id' => $application->id,
                        'is_active' => true,
                        'activated_at' => now(),
                    ]
                );

                // Générer un code promo si l'ambassadeur n'en a pas déjà un actif
                if (!$ambassador->activePromoCode()) {
                    $ambassador->generatePromoCode();
                }
            }

            // Envoyer une notification à l'utilisateur
            if ($application->relationLoaded('user') === false) {
                $application->load('user');
            }

            if ($application->user) {
                Notification::sendNow(
                    $application->user,
                    new AmbassadorApplicationStatusUpdated($application, $request->status)
                );

                // Envoyer un email si approuvé
                if ($request->status === 'approved' && $ambassador) {
                    $promoCode = $ambassador->activePromoCode();
                    try {
                        $mailable = new \App\Mail\AmbassadorApplicationApproved($ambassador, $application, $promoCode);
                        $communicationService = app(\App\Services\CommunicationService::class);
                        $communicationService->sendEmailAndWhatsApp($application->user, $mailable);
                    } catch (\Exception $e) {
                        \Log::error('Error sending ambassador approval email: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.ambassadors.applications.show', $application)
                ->with('success', 'Statut de la candidature mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Afficher la liste des ambassadeurs avec onglets
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'ambassadors'); // Par défaut, onglet Ambassadeurs

        // Données pour l'onglet Ambassadeurs
        $ambassadorsQuery = Ambassador::with(['user', 'application'])
            ->latest();

        if ($request->filled('status') && $tab === 'ambassadors') {
            if ($request->status === 'active') {
                $ambassadorsQuery->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $ambassadorsQuery->where('is_active', false);
            }
        }

        if ($request->filled('search') && $tab === 'ambassadors') {
            $search = $request->search;
            $ambassadorsQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $ambassadors = $ambassadorsQuery->paginate(20, ['*'], 'ambassadors_page');

        // Données pour l'onglet Candidatures
        $applicationsQuery = AmbassadorApplication::with(['user', 'reviewer'])
            ->latest();

        if ($request->filled('status') && $tab === 'applications' && $request->status !== 'all') {
            $applicationsQuery->where('status', $request->status);
        }

        if ($request->filled('search') && $tab === 'applications') {
            $search = $request->search;
            $applicationsQuery->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $applicationsQuery->paginate(20, ['*'], 'applications_page');

        // Données pour l'onglet Commissions
        $commissionsQuery = AmbassadorCommission::with(['ambassador.user', 'order', 'promoCode'])
            ->latest();

        if ($request->filled('status') && $tab === 'commissions' && $request->status !== 'all') {
            $commissionsQuery->where('status', $request->status);
        }

        if ($request->filled('search') && $tab === 'commissions') {
            $search = $request->search;
            $commissionsQuery->whereHas('order', function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            });
        }

        $commissions = $commissionsQuery->paginate(20, ['*'], 'commissions_page');

        // Statistiques pour l'onglet Ambassadeurs
        $ambassadorStats = [
            'total' => Ambassador::count(),
            'active' => Ambassador::where('is_active', true)->count(),
            'inactive' => Ambassador::where('is_active', false)->count(),
            'total_earnings' => (float) AmbassadorCommission::sum('commission_amount'),
            'paid_earnings' => (float) AmbassadorCommission::where('status', 'paid')->sum('commission_amount'),
            'pending_earnings' => (float) AmbassadorCommission::where('status', 'pending')->sum('commission_amount'),
            'total_referrals' => (int) \App\Models\Order::whereNotNull('ambassador_id')->distinct('user_id')->count('user_id'),
            'total_sales' => (int) \App\Models\Order::whereNotNull('ambassador_id')->count(),
        ];

        // Statistiques pour l'onglet Candidatures
        $applicationStats = [
            'total' => AmbassadorApplication::count(),
            'pending' => AmbassadorApplication::where('status', 'pending')->count(),
            'under_review' => AmbassadorApplication::where('status', 'under_review')->count(),
            'approved' => AmbassadorApplication::where('status', 'approved')->count(),
            'rejected' => AmbassadorApplication::where('status', 'rejected')->count(),
        ];

        // Statistiques pour l'onglet Commissions
        $commissionStats = [
            'total' => AmbassadorCommission::count(),
            'pending' => AmbassadorCommission::where('status', 'pending')->count(),
            'approved' => AmbassadorCommission::where('status', 'approved')->count(),
            'paid' => AmbassadorCommission::where('status', 'paid')->count(),
            'total_amount' => (float) AmbassadorCommission::sum('commission_amount'),
            'paid_amount' => (float) AmbassadorCommission::where('status', 'paid')->sum('commission_amount'),
            'pending_amount' => (float) AmbassadorCommission::where('status', 'pending')->sum('commission_amount'),
        ];

        $currencyCode = Setting::getBaseCurrency();
        $currencyCode = is_array($currencyCode) ? ($currencyCode['code'] ?? 'USD') : ($currencyCode ?? 'USD');

        return view('admin.ambassadors.index', compact(
            'ambassadors', 
            'applications', 
            'commissions', 
            'tab',
            'ambassadorStats',
            'applicationStats',
            'commissionStats',
            'currencyCode'
        ));
    }

    /**
     * Afficher les détails d'un ambassadeur
     */
    public function show(Ambassador $ambassador)
    {
        $ambassador->load(['user', 'application', 'promoCodes', 'commissions.order', 'orders']);

        // Calculer toutes les statistiques en temps réel à partir de la base de données
        $stats = [
            // Gains totaux : somme de toutes les commissions depuis la table ambassador_commissions
            'total_earnings' => (float) $ambassador->commissions()->sum('commission_amount'),
            
            // Gains en attente : somme des commissions avec statut 'pending'
            'pending_earnings' => (float) $ambassador->commissions()
                ->where('status', 'pending')
                ->sum('commission_amount'),
            
            // Gains payés : somme des commissions avec statut 'paid'
            'paid_earnings' => (float) $ambassador->commissions()
                ->where('status', 'paid')
                ->sum('commission_amount'),
            
            // Total des références : nombre d'utilisateurs uniques ayant utilisé le code promo de l'ambassadeur
            'total_referrals' => (int) \App\Models\Order::where('ambassador_id', $ambassador->id)
                ->distinct()
                ->count('user_id'),
            
            // Total des ventes : nombre total de commandes générées par cet ambassadeur depuis la table orders
            'total_sales' => (int) $ambassador->orders()->count(),
            
            // Statistiques des commissions détaillées
            'commissions' => [
                'total' => (float) $ambassador->commissions()->sum('commission_amount'),
                'pending' => (float) $ambassador->commissions()->where('status', 'pending')->sum('commission_amount'),
                'paid' => (float) $ambassador->commissions()->where('status', 'paid')->sum('commission_amount'),
                'approved' => (float) $ambassador->commissions()->where('status', 'approved')->sum('commission_amount'),
            ],
        ];

        return view('admin.ambassadors.show', compact('ambassador', 'stats'));
    }

    /**
     * Activer/Désactiver un ambassadeur
     */
    public function toggleActive(Ambassador $ambassador)
    {
        $ambassador->update([
            'is_active' => !$ambassador->is_active,
        ]);

        return back()->with('success', 'Statut de l\'ambassadeur mis à jour avec succès.');
    }

    /**
     * Afficher les commissions d'un ambassadeur
     */
    public function commissions(Request $request, Ambassador $ambassador = null)
    {
        $query = AmbassadorCommission::with(['ambassador.user', 'order', 'promoCode'])
            ->latest();

        if ($ambassador) {
            $query->where('ambassador_id', $ambassador->id);
        }

        // Filtres
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            });
        }

        $commissions = $query->paginate(20);

        return view('admin.ambassadors.commissions', compact('commissions', 'ambassador'));
    }

    /**
     * Approuver une commission
     */
    public function approveCommission(AmbassadorCommission $commission)
    {
        $commission->update(['status' => 'approved']);

        return back()->with('success', 'Commission approuvée avec succès.');
    }

    /**
     * Marquer une commission comme payée
     */
    public function markCommissionAsPaid(AmbassadorCommission $commission)
    {
        DB::beginTransaction();
        try {
            $commission->markAsPaid();

            DB::commit();

            return back()->with('success', 'Commission marquée comme payée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Générer un nouveau code promo pour un ambassadeur
     */
    public function generatePromoCode(Request $request, Ambassador $ambassador)
    {
        DB::beginTransaction();
        try {
            // Récupérer l'ancien code promo actif avant de le désactiver
            $oldPromoCode = $ambassador->activePromoCode();
            
            // Désactiver tous les codes promo existants de cet ambassadeur
            $ambassador->promoCodes()->update(['is_active' => false]);
            
            // Générer un nouveau code promo
            $promoCode = $ambassador->generatePromoCode();

            DB::commit();

            // Envoyer un email à l'ambassadeur
            try {
                $ambassador->load('user');
                $mailable = new \App\Mail\AmbassadorPromoCodeUpdated(
                    $ambassador, 
                    $promoCode, 
                    $oldPromoCode,
                    true // isNewCode
                );
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($ambassador->user, $mailable);
            } catch (\Exception $e) {
                \Log::error('Error sending ambassador promo code email: ' . $e->getMessage());
                // Ne pas bloquer la génération si l'email échoue
            }

            $message = 'Code promo généré avec succès: ' . $promoCode->code;
            
            // Retourner une réponse JSON pour les requêtes AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'promo_code' => [
                        'id' => $promoCode->id,
                        'code' => $promoCode->code,
                        'usage_count' => $promoCode->usage_count,
                        'max_usage' => $promoCode->max_usage,
                        'expires_at' => $promoCode->expires_at ? $promoCode->expires_at->format('d/m/Y H:i') : null,
                    ]
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = 'Erreur lors de la génération du code promo: ' . $e->getMessage();
            
            // Retourner une réponse JSON pour les requêtes AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Vérifier l'unicité d'un code promo
     */
    public function checkPromoCodeUnique(Request $request, Ambassador $ambassador)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'promo_code_id' => 'nullable|exists:ambassador_promo_codes,id',
        ]);

        $code = strtoupper($request->code);
        $promoCodeId = $request->promo_code_id;

        // Vérifier si le code existe déjà (en excluant le code promo actuel si fourni)
        $exists = AmbassadorPromoCode::where('code', $code);
        
        if ($promoCodeId) {
            $exists->where('id', '!=', $promoCodeId);
        }
        
        $codeExists = $exists->exists();

        return response()->json([
            'available' => !$codeExists,
            'message' => $codeExists 
                ? 'Ce code promo est déjà utilisé par un autre ambassadeur.' 
                : 'Ce code promo est disponible.'
        ]);
    }

    /**
     * Mettre à jour le code promo d'un ambassadeur
     */
    public function updatePromoCode(Request $request, Ambassador $ambassador)
    {
        try {
            $request->validate([
                'promo_code_id' => 'required|exists:ambassador_promo_codes,id',
                'code' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[A-Z0-9\-]+$/',
                    function ($attribute, $value, $fail) use ($request) {
                        // Vérifier l'unicité en excluant le code promo actuel
                        $exists = AmbassadorPromoCode::where('code', strtoupper($value))
                            ->where('id', '!=', $request->promo_code_id)
                            ->exists();
                        
                        if ($exists) {
                            $fail('Ce code promo est déjà utilisé par un autre ambassadeur.');
                        }
                    },
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first('code') ?? 'Erreur de validation',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            throw $e;
        }

        DB::beginTransaction();
        try {
            $promoCode = AmbassadorPromoCode::where('id', $request->promo_code_id)
                ->where('ambassador_id', $ambassador->id)
                ->firstOrFail();

            // Sauvegarder l'ancien code avant la mise à jour
            $oldCode = $promoCode->code;
            $oldPromoCode = clone $promoCode;
            $oldPromoCode->code = $oldCode;

            $promoCode->update([
                'code' => strtoupper($request->code),
            ]);

            // Recharger le code promo pour avoir les données à jour
            $promoCode->refresh();

            DB::commit();

            // Envoyer un email à l'ambassadeur seulement si le code a vraiment changé
            if ($oldCode !== strtoupper($request->code)) {
                try {
                    $ambassador->load('user');
                    $mailable = new \App\Mail\AmbassadorPromoCodeUpdated(
                        $ambassador, 
                        $promoCode, 
                        $oldPromoCode,
                        false // isNewCode = false car c'est une modification
                    );
                    $communicationService = app(\App\Services\CommunicationService::class);
                    $communicationService->sendEmailAndWhatsApp($ambassador->user, $mailable);
                } catch (\Exception $e) {
                    \Log::error('Error sending ambassador promo code update email: ' . $e->getMessage());
                    // Ne pas bloquer la mise à jour si l'email échoue
                }
            }

            $message = 'Code promo mis à jour avec succès: ' . $promoCode->code;
            
            // Retourner une réponse JSON pour les requêtes AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'code' => $promoCode->code
                ]);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = 'Erreur lors de la mise à jour: ' . $e->getMessage();
            
            // Retourner une réponse JSON pour les requêtes AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return back()->with('error', $errorMessage);
        }
    }

    /**
     * Supprimer un ambassadeur
     * Met à jour la candidature associée au statut "rejected"
     */
    public function destroy(Request $request, Ambassador $ambassador)
    {
        \Log::info('=== TENTATIVE DE SUPPRESSION D\'AMBASSADEUR ===', [
            'ambassador_id' => $ambassador->id,
            'user_id' => $ambassador->user_id,
            'application_id' => $ambassador->application_id,
            'method' => $request->method(),
            'auth_user' => Auth::id(),
        ]);

        DB::beginTransaction();
        try {
            // Mettre à jour la candidature associée au statut "rejected"
            if ($ambassador->application_id) {
                $application = AmbassadorApplication::find($ambassador->application_id);
                if ($application) {
                    $application->update([
                        'status' => 'rejected',
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                        'admin_notes' => ($application->admin_notes ?? '') . "\n\n[Note automatique] Candidature rejetée suite à la suppression de l'ambassadeur.",
                    ]);
                    \Log::info('Candidature mise à jour au statut rejected', [
                        'application_id' => $application->id,
                        'ambassador_id' => $ambassador->id,
                    ]);
                }
            }

            $ambassadorId = $ambassador->id;
            $ambassador->delete();

            DB::commit();

            \Log::info('=== AMBASSADEUR SUPPRIMÉ AVEC SUCCÈS ===', ['ambassador_id' => $ambassadorId]);

            return redirect()->route('admin.ambassadors.index', ['tab' => 'ambassadors'])
                ->with('success', 'Ambassadeur supprimé avec succès. La candidature associée a été mise à jour au statut "rejeté".');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== ERREUR LORS DE LA SUPPRESSION D\'AMBASSADEUR ===', [
                'ambassador_id' => $ambassador->id ?? null,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.ambassadors.index', ['tab' => 'ambassadors'])
                ->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Actions en lot sur les ambassadeurs
     */
    public function bulkAction(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    $ambassador = Ambassador::find($id);
                    if ($ambassador) {
                        $this->destroy(new Request(), $ambassador);
                        $count++;
                    }
                }
                return [
                    'message' => "{$count} ambassadeur(s) supprimé(s) avec succès.",
                    'count' => $count
                ];
            },
            'activate' => function($ids) {
                $count = Ambassador::whereIn('id', $ids)
                    ->where('is_active', false)
                    ->update(['is_active' => true]);
                return [
                    'message' => "{$count} ambassadeur(s) activé(s) avec succès.",
                    'count' => $count
                ];
            },
            'deactivate' => function($ids) {
                $count = Ambassador::whereIn('id', $ids)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
                return [
                    'message' => "{$count} ambassadeur(s) désactivé(s) avec succès.",
                    'count' => $count
                ];
            }
        ];

        return $this->handleBulkAction($request, Ambassador::class, $actions);
    }

    /**
     * Exporter les ambassadeurs
     */
    public function export(Request $request)
    {
        $query = Ambassador::with(['user', 'application'])
            ->latest();

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $columns = [
            'user.name' => 'Nom',
            'user.email' => 'Email',
            'is_active' => 'Actif',
            'total_earnings' => 'Gains totaux',
            'created_at' => 'Date de création'
        ];

        return $this->exportData($request, $query, $columns, 'ambassadeurs');
    }

    /**
     * Actions en lot sur les candidatures
     */
    public function bulkActionApplications(Request $request)
    {
        $actions = [
            'delete' => function($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    $application = AmbassadorApplication::find($id);
                    if ($application) {
                        $this->destroyApplication(new Request(), $application);
                        $count++;
                    }
                }
                return [
                    'message' => "{$count} candidature(s) supprimée(s) avec succès.",
                    'count' => $count
                ];
            }
        ];

        return $this->handleBulkAction($request, AmbassadorApplication::class, $actions);
    }

    /**
     * Exporter les candidatures
     */
    public function exportApplications(Request $request)
    {
        $query = AmbassadorApplication::with(['user', 'reviewer'])
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $columns = [
            'user.name' => 'Nom',
            'user.email' => 'Email',
            'status' => 'Statut',
            'created_at' => 'Date de candidature'
        ];

        return $this->exportData($request, $query, $columns, 'candidatures-ambassadeurs');
    }

    /**
     * Actions en lot sur les commissions
     */
    public function bulkActionCommissions(Request $request)
    {
        $actions = [
            'approve' => function($ids) {
                $count = AmbassadorCommission::whereIn('id', $ids)
                    ->where('status', 'pending')
                    ->update(['status' => 'approved']);
                return [
                    'message' => "{$count} commission(s) approuvée(s) avec succès.",
                    'count' => $count
                ];
            },
            'mark-paid' => function($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    $commission = AmbassadorCommission::find($id);
                    if ($commission && $commission->status === 'approved') {
                        $commission->markAsPaid();
                        $count++;
                    }
                }
                return [
                    'message' => "{$count} commission(s) marquée(s) comme payée(s).",
                    'count' => $count
                ];
            }
        ];

        return $this->handleBulkAction($request, AmbassadorCommission::class, $actions);
    }

    /**
     * Exporter les commissions
     */
    public function exportCommissions(Request $request)
    {
        $query = AmbassadorCommission::with(['ambassador.user', 'order', 'promoCode'])
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('order', function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%");
            });
        }

        $columns = [
            'ambassador.user.name' => 'Ambassadeur',
            'order.order_number' => 'Commande',
            'order_total' => 'Montant commande',
            'commission_amount' => 'Commission',
            'status' => 'Statut',
            'created_at' => 'Date'
        ];

        return $this->exportData($request, $query, $columns, 'commissions-ambassadeurs');
    }
}
