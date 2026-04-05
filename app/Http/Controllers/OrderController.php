<?php

namespace App\Http\Controllers;

use App\Mail\CourseAccessRevokedMail;
use App\Mail\InvoiceMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\CourseAccessRevoked;
use App\Notifications\PaymentReceived;
use App\Services\OrderEnrollmentService;
use App\Services\SubscriptionCheckoutOrderService;
use App\Services\SubscriptionService;
use App\Traits\HandlesBulkActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class OrderController extends Controller
{
    use HandlesBulkActions;

    /**
     * Lignes de commande visibles côté client : cours publiés ou achat de pack (content_package_id).
     *
     * @return array<string, mixed>
     */
    protected function withCustomerOrderListRelations(): array
    {
        $orderItemsConstraint = fn ($q) => $q->forCustomerListing();

        return [
            'enrollments' => function ($q) {
                $q->whereHas('content', function ($q2) {
                    $q2->where('is_published', true);
                });
            },
            'enrollments.course',
            'payments',
            'orderItems' => $orderItemsConstraint,
            'orderItems.course',
            'orderItems.contentPackage',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function withCustomerOrderDetailRelations(): array
    {
        $orderItemsConstraint = fn ($q) => $q->forCustomerListing();

        return [
            'enrollments' => function ($q) {
                $q->whereHas('content', function ($q2) {
                    $q2->where('is_published', true);
                });
            },
            'enrollments.course',
            'user',
            'payments',
            'orderItems' => $orderItemsConstraint,
            'orderItems.course.provider',
            'orderItems.course.category',
            'orderItems.contentPackage',
        ];
    }

    /**
     * Afficher les commandes de l'utilisateur connecté (étudiant)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $status = $request->get('status', 'all');
        $search = $request->get('q');

        $ordersQuery = Order::where('user_id', $user->id)
            ->with($this->withCustomerOrderListRelations())
            ->latest();

        if ($status !== 'all') {
            $ordersQuery->where('status', $status);
        }

        if (! empty($search)) {
            $ordersQuery->where(function ($query) use ($search) {
                $query->where('order_number', 'like', '%'.$search.'%')
                    ->orWhere('payment_reference', 'like', '%'.$search.'%');
            });
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        $summaryBase = Order::where('user_id', $user->id);

        $summary = [
            'total' => (clone $summaryBase)->count(),
            'pending' => (clone $summaryBase)->where('status', 'pending')->count(),
            'confirmed' => (clone $summaryBase)->where('status', 'confirmed')->count(),
            'paid' => (clone $summaryBase)->where('status', 'paid')->count(),
            'completed' => (clone $summaryBase)->where('status', 'completed')->count(),
            'cancelled' => (clone $summaryBase)->where('status', 'cancelled')->count(),
            'total_spent' => (clone $summaryBase)
                ->whereIn('status', ['paid', 'completed'])
                ->get()
                ->sum(function ($order) {
                    return $order->total_amount ?? $order->total ?? 0;
                }),
            'last_order' => (clone $summaryBase)->latest('created_at')->first(),
        ];

        return view('customers.orders', [
            'orders' => $orders,
            'status' => $status,
            'search' => $search,
            'summary' => $summary,
        ]);
    }

    /**
     * Afficher les détails d'une commande (étudiant)
     */
    public function show(Request $request, Order $order)
    {
        // Vérifier que l'utilisateur peut voir cette commande
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette commande.');
        }

        $order->load($this->withCustomerOrderDetailRelations());

        // Si c'est une requête AJAX, retourner le statut en JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'currency' => $order->currency,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }

        return view('orders.show', compact('order'));
    }

    /**
     * Afficher toutes les commandes (administrateur)
     */
    public function adminIndex(Request $request)
    {
        $query = Order::with(['user'])->latest();

        // Filtres
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate(15);

        // Statistiques dynamiques basées sur les filtres appliqués
        $statsBase = clone $query;
        $stats = [
            'total' => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->where('status', 'pending')->count(),
            'confirmed' => (clone $statsBase)->where('status', 'confirmed')->count(),
            'paid' => (clone $statsBase)->where('status', 'paid')->count(),
            'completed' => (clone $statsBase)->where('status', 'completed')->count(),
            'cancelled' => (clone $statsBase)->where('status', 'cancelled')->count(),
            // Somme sur total_amount avec repli vers total si total_amount nul
            'total_revenue' => (clone $statsBase)
                ->whereIn('status', ['paid', 'completed'])
                ->get()
                ->sum(function ($o) {
                    return $o->total_amount ?? $o->total ?? 0;
                }),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Afficher les détails d'une commande (administrateur)
     */
    public function adminShow(Order $order)
    {
        $order->load([
            'user',
            'enrollments.course',
            'orderItems.course.provider',
            'orderItems.course.category',
            'orderItems.contentPackage',
        ]);

        $orderSubscriptionPlan = $this->resolveSubscriptionPlanForAdminOrder($order);
        $orderLinkedUserSubscription = $this->resolveUserSubscriptionLinkedToAdminOrder($order);

        return view('admin.orders.show', compact('order', 'orderSubscriptionPlan', 'orderLinkedUserSubscription'));
    }

    /**
     * Confirmer une commande (administrateur)
     */
    public function confirm(Request $request, Order $order)
    {
        try {
            // Vérifier que l'utilisateur est admin ou super_user
            if (! auth()->check() || ! auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Vous devez être administrateur ou super utilisateur.',
                ], 403);
            }

            // Validation optionnelle - si des données sont fournies, les valider
            if ($request->has('payment_reference') || $request->has('notes')) {
                $request->validate([
                    'payment_reference' => 'nullable|string|max:255',
                    'notes' => 'nullable|string|max:1000',
                ]);
            }

            // Mettre à jour le statut de la commande
            $order->update([
                'status' => 'confirmed',
                'payment_reference' => $request->payment_reference ?? $order->payment_reference,
                'notes' => $request->notes ?? $order->notes,
                'confirmed_at' => now(),
            ]);

            // Créer une inscription pour TOUS les contenus de la commande (téléchargeable, présentiel, en ligne)
            // pour envoyer le reçu par mail et permettre le téléchargement du reçu.
            if (! $order->relationLoaded('orderItems')) {
                $order->load(['orderItems.course', 'orderItems.contentPackage']);
            }

            $order->orderItems->loadMissing(['course', 'contentPackage']);
            app(OrderEnrollmentService::class)->syncEnrollmentsFromOrderItems($order, $order->orderItems);

            return response()->json([
                'success' => true,
                'message' => 'Commande confirmée avec succès. L\'utilisateur a maintenant accès aux cours.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la confirmation de la commande: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Marquer une commande comme payée (administrateur)
     */
    public function markAsPaid(Request $request, Order $order)
    {
        $request->validate([
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $isSubscriptionCheckout = $this->isSubscriptionCheckoutOrder($order);

        $order->update([
            'status' => 'paid',
            'payment_reference' => $request->payment_reference ?? $order->payment_reference,
            'notes' => $request->notes ?? $order->notes,
            'paid_at' => now(),
        ]);

        if ($isSubscriptionCheckout) {
            $this->finalizeSubscriptionOrderAfterAdminMarkPaid($order->fresh());

            return response()->json([
                'success' => true,
                'message' => 'Commande d’abonnement marquée comme payée. Facture, abonnement et accès ont été alignés.',
            ]);
        }

        // Charger les relations nécessaires pour les emails et notifications
        $order->load(array_merge(
            ['user', 'coupon', 'affiliate', 'payments'],
            Order::eagerLoadOrderItemsWithPackages()
        ));

        $order->orderItems->loadMissing(['course', 'contentPackage']);
        app(OrderEnrollmentService::class)->syncEnrollmentsFromOrderItems($order, $order->orderItems);

        // Envoyer la notification de confirmation de paiement
        try {
            if ($order->user) {
                // Vérifier si la notification n'a pas déjà été envoyée
                $alreadyNotified = $order->user->notifications()
                    ->where('type', PaymentReceived::class)
                    ->where('data->order_id', $order->id)
                    ->exists();

                if (! $alreadyNotified) {
                    // Envoyer l'email et WhatsApp en parallèle
                    try {
                        $mailable = new \App\Mail\PaymentReceivedMail($order);
                        $communicationService = app(\App\Services\CommunicationService::class);
                        $communicationService->sendEmailAndWhatsApp($order->user, $mailable);
                        \Log::info("Email et WhatsApp PaymentReceivedMail envoyés pour la commande {$order->order_number}");
                    } catch (\Exception $emailException) {
                        \Log::error("Erreur lors de l'envoi de l'email PaymentReceivedMail", [
                            'order_id' => $order->id,
                            'user_id' => $order->user->id,
                            'error' => $emailException->getMessage(),
                            'trace' => $emailException->getTraceAsString(),
                        ]);
                    }

                    // Envoyer la notification en base de données (sans email car déjà envoyé)
                    // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
                    Notification::sendNow($order->user, new PaymentReceived($order));
                    \Log::info("Notification de confirmation de paiement envoyée pour la commande {$order->order_number}");
                }
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi de la notification de confirmation de paiement pour la commande {$order->id}: ".$e->getMessage());
        }

        // Envoyer la facture par email et WhatsApp
        try {
            if ($order->user && $order->user->email) {
                $mailable = new InvoiceMail($order);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($order->user, $mailable);
                \Log::info("Facture envoyée par email et WhatsApp pour la commande {$order->order_number}");
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi de la facture pour la commande {$order->id}: ".$e->getMessage());
        }

        // Notifier les admins du paiement reçu
        try {
            app(\App\Services\AdminPaymentNotifier::class)->notify($order);
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la notification admin (markAsPaid) pour la commande {$order->id}: ".$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Commande marquée comme payée.',
        ]);
    }

    /**
     * Marquer une commande comme terminée (administrateur)
     */
    public function markAsCompleted(Order $order)
    {
        $order->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commande marquée comme terminée.',
        ]);
    }

    /**
     * Annuler une commande (administrateur)
     */
    public function cancel(Request $request, Order $order)
    {
        try {
            // Vérifier que l'utilisateur est admin ou super_user
            if (! auth()->check() || ! auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Vous devez être administrateur ou super utilisateur.',
                ], 403);
            }

            // Validation optionnelle - si une raison est fournie, la valider
            if ($request->has('reason')) {
                $request->validate([
                    'reason' => 'string|max:1000',
                ]);
            }

            $order->update([
                'status' => 'cancelled',
                'notes' => $request->reason ?? 'Commande annulée par l\'administrateur',
            ]);

            // Supprimer les inscriptions associées
            $order->enrollments()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Commande annulée.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'annulation de la commande: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprimer complètement une commande (administrateur)
     * Cette méthode supprime la commande même si elle était payée
     */
    public function destroy(Request $request, Order $order)
    {
        try {
            // Vérifier que l'utilisateur est admin ou super_user
            if (! auth()->check() || ! auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé. Vous devez être administrateur ou super utilisateur.',
                ], 403);
            }

            $orderNumber = $order->order_number;
            $userId = $order->user_id;
            $orderId = $order->id;

            // Charger les relations nécessaires
            $order->load(array_merge(
                ['enrollments.course', 'enrollments.user', 'payments', 'user'],
                Order::eagerLoadOrderItemsWithPackages()
            ));

            // Envoyer l'email de notification de suppression de commande à l'utilisateur
            if ($order->user && $order->user->email) {
                try {
                    $mailable = new \App\Mail\OrderDeletedMail($order);
                    $communicationService = app(\App\Services\CommunicationService::class);
                    $communicationService->sendEmailAndWhatsApp($order->user, $mailable);
                    \Log::info("Email OrderDeletedMail envoyé à {$order->user->email} pour la commande {$orderNumber}", [
                        'order_id' => $orderId,
                        'user_id' => $userId,
                        'user_email' => $order->user->email,
                    ]);
                } catch (\Exception $emailException) {
                    \Log::error("Erreur lors de l'envoi de l'email OrderDeletedMail", [
                        'order_id' => $orderId,
                        'user_id' => $userId,
                        'user_email' => $order->user->email,
                        'error' => $emailException->getMessage(),
                        'trace' => $emailException->getTraceAsString(),
                    ]);
                    // Ne pas bloquer la suppression si l'email échoue
                }
            }

            // Supprimer toutes les inscriptions associées à cette commande
            foreach ($order->enrollments as $enrollment) {
                // Envoyer une notification à l'utilisateur si nécessaire
                try {
                    if ($enrollment->course && $enrollment->user) {
                        // Envoyer l'email de notification
                        $mailable = new CourseAccessRevokedMail($enrollment->course);
                        $communicationService = app(\App\Services\CommunicationService::class);
                        $communicationService->sendEmailAndWhatsApp($enrollment->user, $mailable);
                        // Envoyer la notification
                        Notification::sendNow($enrollment->user, new CourseAccessRevoked($enrollment->course));
                    }
                } catch (\Exception $e) {
                    \Log::error("Erreur lors de l'envoi de notification de suppression d'inscription: ".$e->getMessage());
                }

                $enrollment->delete();
            }

            // Supprimer les orderItems
            $order->orderItems()->delete();

            // Supprimer les paiements associés
            $order->payments()->delete();

            // Supprimer la commande elle-même
            $order->delete();

            \Log::info("Commande {$orderNumber} supprimée par l'administrateur", [
                'order_id' => $orderId,
                'user_id' => $userId,
                'admin_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Commande supprimée avec succès. Toutes les inscriptions associées ont été retirées.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la commande: '.$e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Filtrer les commandes (administrateur)
     */
    public function filter(Request $request)
    {
        $query = Order::with(['user', 'enrollments.course']);

        // Filtres
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.orders.partials.orders-table', compact('orders'));
    }

    /**
     * Exporter les commandes (administrateur)
     */
    public function export(Request $request)
    {
        try {
            $query = Order::with(array_merge(
                ['user', 'enrollments.course'],
                Order::eagerLoadOrderItemsWithPackages()
            ));

            // Appliquer les mêmes filtres que dans adminIndex
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhere('payment_reference', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            // Filtrer par IDs si fournis (pour export sélectif)
            if ($request->filled('ids')) {
                $ids = explode(',', $request->ids);
                $query->whereIn('id', $ids);
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Infos pour l'en-tête (filtres appliqués)
            $filtersApplied = [];
            if ($request->filled('status')) {
                $filtersApplied[] = 'Statut : '.$this->getStatusLabel($request->status);
            }
            if ($request->filled('payment_method')) {
                $filtersApplied[] = 'Mode de paiement : '.$this->getPaymentMethodLabel($request->payment_method);
            }
            if ($request->filled('date_from')) {
                $filtersApplied[] = 'À partir du : '.\Carbon\Carbon::parse($request->date_from)->format('d/m/Y');
            }
            if ($request->filled('date_to')) {
                $filtersApplied[] = 'Jusqu\'au : '.\Carbon\Carbon::parse($request->date_to)->format('d/m/Y');
            }
            if ($request->filled('search')) {
                $filtersApplied[] = 'Recherche : "'.$request->search.'"';
            }
            if ($request->filled('ids')) {
                $filtersApplied[] = 'Export sélectif (IDs fournis)';
            }
            $filtersLine = empty($filtersApplied) ? 'Aucun filtre' : implode(' ; ', $filtersApplied);

            // Générer le CSV
            $filename = 'commandes_'.now()->format('Y-m-d_H-i-s').'.csv';

            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ];

            $callback = function () use ($orders, $filtersLine) {
                $file = fopen('php://output', 'w');

                // BOM pour UTF-8
                fwrite($file, "\xEF\xBB\xBF");

                // --- En-tête : titre et informations ---
                fputcsv($file, ['Export des commandes - Herime Académie']);
                fputcsv($file, ['Date d\'export', now()->format('d/m/Y à H:i')]);
                fputcsv($file, ['Filtres appliqués', $filtersLine]);
                fputcsv($file, []); // ligne vide

                // En-têtes du tableau
                fputcsv($file, [
                    'Numéro de commande',
                    'Client',
                    'Email',
                    'Montant',
                    'Statut',
                    'Mode de paiement',
                    'Référence de paiement',
                    'Date de création',
                    'Date de confirmation',
                    'Date de paiement',
                    'Date de finalisation',
                    'Notes',
                ]);

                // Données
                $totalsByCurrency = [];
                $countByStatus = [];
                foreach ($orders as $order) {
                    $total = (float) ($order->total_amount ?? $order->total ?? 0);
                    $currency = $order->currency ?? 'USD';
                    $totalsByCurrency[$currency] = ($totalsByCurrency[$currency] ?? 0) + $total;
                    $countByStatus[$order->status] = ($countByStatus[$order->status] ?? 0) + 1;

                    fputcsv($file, [
                        $order->order_number ?? '',
                        $order->user?->name ?? 'N/A',
                        $order->user?->email ?? '',
                        \App\Helpers\CurrencyHelper::formatWithSymbol($total, $currency),
                        $this->getStatusLabel($order->status),
                        $this->getPaymentMethodLabel($order->payment_method),
                        $order->payment_reference ?? '',
                        $order->created_at?->format('d/m/Y H:i') ?? '',
                        $order->confirmed_at?->format('d/m/Y H:i') ?? '',
                        $order->paid_at?->format('d/m/Y H:i') ?? '',
                        $order->completed_at?->format('d/m/Y H:i') ?? '',
                        $order->notes ?? '',
                    ]);
                }

                // --- Ligne vide puis résumé / calculs ---
                fputcsv($file, []);
                fputcsv($file, ['Résumé']);
                fputcsv($file, ['Nombre total de commandes', $orders->count()]);

                foreach ($totalsByCurrency as $currency => $sum) {
                    fputcsv($file, ['Montant total ('.$currency.')', \App\Helpers\CurrencyHelper::formatWithSymbol($sum, $currency)]);
                }

                fputcsv($file, []);
                fputcsv($file, ['Répartition par statut']);
                foreach ($countByStatus as $status => $count) {
                    fputcsv($file, [$this->getStatusLabel($status), $count]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'exportation des commandes: '.$e->getMessage());

            return redirect()->back()->with('error', 'Erreur lors de l\'exportation des commandes.');
        }
    }

    private function isSubscriptionCheckoutOrder(Order $order): bool
    {
        return filled(data_get($order->billing_info, 'subscription_invoice_id'));
    }

    /**
     * Finalisation commande checkout abonnement / réabonnement (billing_info.subscription_invoice_id) :
     * aligner Payment Moneroo, panier / wallets / emails comme après webhook, puis facture + abonnement
     * (même logique que « Vérifier le paiement » Moneroo).
     */
    private function finalizeSubscriptionOrderAfterAdminMarkPaid(Order $order): void
    {
        $order->refresh();

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('payment_method', 'moneroo')
            ->whereIn('status', ['pending', 'processing'])
            ->latest('id')
            ->first();

        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
                'payment_data' => array_merge($payment->payment_data ?? [], [
                    'admin_marked_order_paid_at' => now()->toIso8601String(),
                    'admin_marked_order_id' => $order->id,
                ]),
            ]);
        }

        app(MonerooController::class)->finalizeOrderAfterSuccessfulPayment($order->fresh());

        app(SubscriptionService::class)->applyPaidStateFromVerifiedSubscriptionOrder($order->fresh());

        $invoiceId = data_get($order->fresh()->billing_info, 'subscription_invoice_id');
        if (! $invoiceId) {
            return;
        }

        $invoice = SubscriptionInvoice::query()->find($invoiceId);
        $user = $order->user ?? User::query()->find($order->user_id);
        if ($invoice && $user) {
            app(SubscriptionCheckoutOrderService::class)
                ->cancelOtherPendingSubscriptionCheckoutsForInvoice($invoice, $user, $order->id);
        }
    }

    /**
     * Obtenir le libellé du statut
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'paid' => 'Payée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
            'failed' => 'Échouée',
            'refunded' => 'Remboursée',
            default => ucfirst($status)
        };
    }

    /**
     * Obtenir le libellé du mode de paiement
     */
    private function getPaymentMethodLabel($method)
    {
        return match ($method) {
            'card' => 'Carte bancaire',
            'paypal' => 'PayPal',
            'mobile' => 'Mobile Money',
            'bank' => 'Virement bancaire',
            default => ucfirst($method ?? 'Non défini')
        };
    }

    /**
     * Actions en lot sur les commandes
     */
    public function bulkAction(Request $request)
    {
        $actions = [
            'delete' => function ($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    $order = Order::find($id);
                    if ($order) {
                        $this->destroy(new Request, $order);
                        $count++;
                    }
                }

                return [
                    'message' => "{$count} commande(s) supprimée(s) avec succès.",
                    'count' => $count,
                ];
            },
            'mark-paid' => function ($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    $order = Order::query()->find($id);
                    if (! $order || in_array($order->status, ['paid', 'completed'], true)) {
                        continue;
                    }
                    $isSubscriptionCheckout = $this->isSubscriptionCheckoutOrder($order);
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    if ($isSubscriptionCheckout) {
                        $this->finalizeSubscriptionOrderAfterAdminMarkPaid($order->fresh());
                    }
                    $count++;
                }

                return [
                    'message' => "{$count} commande(s) marquée(s) comme payée(s).",
                    'count' => $count,
                ];
            },
            'mark-completed' => function ($ids) {
                $count = Order::whereIn('id', $ids)
                    ->where('status', '!=', 'completed')
                    ->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                return [
                    'message' => "{$count} commande(s) marquée(s) comme terminée(s).",
                    'count' => $count,
                ];
            },
            'cancel' => function ($ids) {
                $count = 0;
                foreach ($ids as $id) {
                    $order = Order::find($id);
                    if ($order && $order->status !== 'cancelled') {
                        $this->cancel(new Request(['reason' => 'Annulation en lot']), $order);
                        $count++;
                    }
                }

                return [
                    'message' => "{$count} commande(s) annulée(s) avec succès.",
                    'count' => $count,
                ];
            },
        ];

        return $this->handleBulkAction($request, Order::class, $actions);
    }

    /**
     * Plan d’abonnement associé à une commande Moneroo abonnement (billing_info).
     */
    private function resolveSubscriptionPlanForAdminOrder(Order $order): ?SubscriptionPlan
    {
        $billing = $order->billing_info ?? [];
        $planId = (int) ($billing['subscription_plan_id'] ?? 0);
        if ($planId > 0) {
            $plan = SubscriptionPlan::query()->find($planId);
            if ($plan) {
                return $plan;
            }
        }

        $invoiceId = (int) ($billing['subscription_invoice_id'] ?? 0);
        if ($invoiceId <= 0) {
            return null;
        }

        $invoice = SubscriptionInvoice::query()
            ->with('subscription.plan')
            ->find($invoiceId);

        return $invoice?->subscription?->plan;
    }

    /**
     * Abonnement utilisateur lié à cette commande (billing_info), pour actions admin (ex. annulation).
     */
    private function resolveUserSubscriptionLinkedToAdminOrder(Order $order): ?UserSubscription
    {
        $billing = $order->billing_info ?? [];
        $subId = (int) ($billing['user_subscription_id'] ?? 0);
        if ($subId <= 0) {
            $invoiceId = (int) ($billing['subscription_invoice_id'] ?? 0);
            if ($invoiceId > 0) {
                $inv = SubscriptionInvoice::query()->find($invoiceId);
                $subId = (int) ($inv?->user_subscription_id ?? 0);
            }
        }
        if ($subId <= 0) {
            return null;
        }

        $sub = UserSubscription::query()->with('plan')->find($subId);
        if (! $sub || (int) $sub->user_id !== (int) $order->user_id) {
            return null;
        }

        return $sub;
    }
}
