<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorApplication;
use App\Models\Ambassador;
use App\Models\User;
use App\Models\AmbassadorCommission;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AmbassadorApplicationStatusUpdated;

class AmbassadorController extends Controller
{
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

        return view('admin.ambassadors.index', compact('ambassadors', 'applications', 'commissions', 'tab'));
    }

    /**
     * Afficher les détails d'un ambassadeur
     */
    public function show(Ambassador $ambassador)
    {
        $ambassador->load(['user', 'application', 'promoCodes', 'commissions.order', 'orders']);

        $commissionsStats = [
            'total' => $ambassador->commissions()->sum('commission_amount'),
            'pending' => $ambassador->commissions()->where('status', 'pending')->sum('commission_amount'),
            'paid' => $ambassador->commissions()->where('status', 'paid')->sum('commission_amount'),
        ];

        return view('admin.ambassadors.show', compact('ambassador', 'commissionsStats'));
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
    public function generatePromoCode(Ambassador $ambassador)
    {
        $promoCode = $ambassador->generatePromoCode();

        return back()->with('success', 'Code promo généré avec succès: ' . $promoCode->code);
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
}
