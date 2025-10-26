<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WhatsAppOrderController extends Controller
{
    /**
     * Créer une commande WhatsApp
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'cart_items' => 'required|array',
            'total_amount' => 'required|numeric|min:0',
            'billing_info' => 'required|array',
        ]);

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour passer une commande.'
            ], 401);
        }

        // Générer un numéro de commande unique
        $orderNumber = 'WHATSAPP-' . strtoupper(Str::random(8)) . '-' . time();

        // Préparer les détails de la commande
        $orderItems = [];
        foreach ($request->cart_items as $item) {
            $orderItems[] = [
                'course_id' => $item['course']['id'],
                'course_title' => $item['course']['title'],
                'instructor_name' => $item['course']['instructor']['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'] ?? 1,
            ];
        }

        // Créer la commande
        $order = Order::create([
            'order_number' => $orderNumber,
            'user_id' => auth()->id(),
            'payment_method' => 'whatsapp',
            'status' => 'pending',
            'subtotal' => $request->total_amount, // Même valeur que total_amount pour les commandes WhatsApp
            'total' => $request->total_amount,
            'total_amount' => $request->total_amount,
            'currency' => 'USD',
            'order_items' => $orderItems,
            'billing_info' => $request->billing_info,
            'notes' => 'Commande WhatsApp - En attente de confirmation',
        ]);

        // Vider le panier de l'utilisateur après création de la commande
        auth()->user()->cartItems()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Commande créée avec succès. Votre panier a été vidé. Notre équipe vous contactera bientôt.',
            'order_number' => $orderNumber,
            'order_id' => $order->id,
        ]);
    }

    /**
     * Afficher les commandes en attente (pour l'admin)
     */
    public function pendingOrders()
    {
        $orders = Order::where('payment_method', 'whatsapp')
            ->where('status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.orders.whatsapp-pending', compact('orders'));
    }

    /**
     * Confirmer une commande WhatsApp (pour l'admin)
     */
    public function confirmOrder(Request $request, Order $order)
    {
        $request->validate([
            'payment_reference' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Mettre à jour le statut de la commande
        $order->update([
            'status' => 'confirmed',
            'payment_reference' => $request->payment_reference,
            'notes' => $request->notes,
            'confirmed_at' => now(),
        ]);

        // Créer les inscriptions pour chaque cours
        foreach ($order->order_items as $item) {
            Enrollment::create([
                'user_id' => $order->user_id,
                'course_id' => $item['course_id'],
                'order_id' => $order->id,
                'status' => 'active',
                'enrolled_at' => now(),
            ]);
        }

        // Vider le panier de l'utilisateur
        auth()->user()->cartItems()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Commande confirmée avec succès. L\'utilisateur a maintenant accès aux cours.',
        ]);
    }

    /**
     * Marquer une commande comme payée (pour l'admin)
     */
    public function markAsPaid(Order $order)
    {
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commande marquée comme payée.',
        ]);
    }

    /**
     * Marquer une commande comme terminée (pour l'admin)
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
     * Annuler une commande (pour l'admin)
     */
    public function cancelOrder(Order $order)
    {
        $order->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Commande annulée.',
        ]);
    }

    /**
     * Afficher les détails d'une commande
     */
    public function show(Order $order)
    {
        $order->load('user');
        
        return view('admin.orders.whatsapp-details', compact('order'));
    }
}