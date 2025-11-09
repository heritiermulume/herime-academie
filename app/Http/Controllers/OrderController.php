<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Afficher les commandes de l'utilisateur connecté (étudiant)
     */
    public function index()
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
            ->with(['enrollments.course'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('students.orders', compact('orders'));
    }

    /**
     * Afficher les détails d'une commande (étudiant)
     */
    public function show(Order $order)
    {
        // Vérifier que l'utilisateur peut voir cette commande
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Accès non autorisé à cette commande.');
        }

        $order->load(['enrollments.course', 'user']);
        
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
                ->sum(function ($o) { return $o->total_amount ?? $o->total ?? 0; }),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Afficher les détails d'une commande (administrateur)
     */
    public function adminShow(Order $order)
    {
        $order->load(['user', 'enrollments.course', 'orderItems.course']);
        
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Confirmer une commande (administrateur)
     */
    public function confirm(Request $request, Order $order)
    {
        try {
            // Vérifier que l'utilisateur est admin ou super_user
            if (!auth()->check() || !auth()->user()->isAdmin()) {
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

            // Créer les inscriptions pour chaque cours (si pas déjà inscrit)
            if (is_array($order->order_items)) {
                foreach ($order->order_items as $item) {
                    // Vérifier si l'utilisateur est déjà inscrit à ce cours
                    $existingEnrollment = Enrollment::where('user_id', $order->user_id)
                        ->where('course_id', $item['course_id'])
                        ->first();
                    
                    if (!$existingEnrollment) {
                        Enrollment::create([
                            'user_id' => $order->user_id,
                            'course_id' => $item['course_id'],
                            'order_id' => $order->id,
                            'status' => 'active',
                            'enrolled_at' => now(),
                        ]);
                    } else {
                        // Mettre à jour l'inscription existante avec l'order_id
                        $existingEnrollment->update([
                            'order_id' => $order->id,
                            'status' => 'active',
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Commande confirmée avec succès. L\'utilisateur a maintenant accès aux cours.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la confirmation de la commande: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la confirmation: ' . $e->getMessage(),
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

        $order->update([
            'status' => 'paid',
            'payment_reference' => $request->payment_reference,
            'notes' => $request->notes,
            'paid_at' => now(),
        ]);

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
            if (!auth()->check() || !auth()->user()->isAdmin()) {
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
            \Log::error('Erreur lors de l\'annulation de la commande: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage(),
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
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
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
            $query = Order::with(['user', 'enrollments.course']);

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
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('payment_reference', 'like', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $orders = $query->orderBy('created_at', 'desc')->get();

            // Générer le CSV
            $filename = 'commandes_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($orders) {
                $file = fopen('php://output', 'w');
                
                // BOM pour UTF-8
                fwrite($file, "\xEF\xBB\xBF");
                
                // En-têtes
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
                    'Notes'
                ]);

                // Données
                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->order_number,
                        $order->user->name,
                        $order->user->email,
                        number_format($order->total_amount, 2) . ' $',
                        $this->getStatusLabel($order->status),
                        $this->getPaymentMethodLabel($order->payment_method),
                        $order->payment_reference ?? '',
                        $order->created_at->format('d/m/Y H:i'),
                        $order->confirmed_at ? $order->confirmed_at->format('d/m/Y H:i') : '',
                        $order->paid_at ? $order->paid_at->format('d/m/Y H:i') : '',
                        $order->completed_at ? $order->completed_at->format('d/m/Y H:i') : '',
                        $order->notes ?? ''
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'exportation des commandes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors de l\'exportation des commandes.');
        }
    }

    /**
     * Obtenir le libellé du statut
     */
    private function getStatusLabel($status)
    {
        return match($status) {
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
        return match($method) {
            'card' => 'Carte bancaire',
            'paypal' => 'PayPal',
            'mobile' => 'Mobile Money',
            'bank' => 'Virement bancaire',
            'whatsapp' => 'WhatsApp',
            default => ucfirst($method ?? 'Non défini')
        };
    }
}