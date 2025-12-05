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
     * Afficher la liste des ambassadeurs
     */
    public function index(Request $request)
    {
        $query = Ambassador::with(['user', 'application'])
            ->latest();

        // Filtres
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

        $ambassadors = $query->paginate(20);

        return view('admin.ambassadors.index', compact('ambassadors'));
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
}
