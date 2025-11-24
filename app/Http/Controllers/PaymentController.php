<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Coupon;
use App\Models\Affiliate;
use App\Mail\InvoiceMail;
use App\Notifications\PaymentReceived;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'payment_method' => 'required|in:stripe,paypal,mobile_money',
            'coupon_code' => 'nullable|string',
            'affiliate_code' => 'nullable|string',
        ]);

        $course = Course::findOrFail($request->course_id);
        
        // Vérifier que le cours est publié
        if (!$course->is_published) {
            return response()->json([
                'success' => false,
                'message' => 'Ce cours n\'est pas disponible.'
            ], 404);
        }
        
        $user = auth()->user();

        // Vérifier si l'utilisateur n'est pas déjà inscrit
        if ($course->isEnrolledBy($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà inscrit à ce cours.'
            ]);
        }

        // Calculer le prix
        $price = $course->current_price;
        $discount = 0;
        $coupon = null;
        $affiliate = null;

        // Appliquer le coupon si fourni
        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('starts_at')
                          ->orWhere('starts_at', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now());
                })
                ->first();

            if ($coupon) {
                if ($coupon->type === 'percentage') {
                    $discount = ($price * $coupon->value) / 100;
                } else {
                    $discount = $coupon->value;
                }
                $discount = min($discount, $price);
            }
        }

        // Appliquer le code d'affiliation si fourni
        if ($request->affiliate_code) {
            $affiliate = Affiliate::where('code', $request->affiliate_code)
                ->where('is_active', true)
                ->first();
        }

        $finalPrice = max(0, $price - $discount);

        // Créer la commande
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $user->id,
            'affiliate_id' => $affiliate?->id,
            'coupon_id' => $coupon?->id,
            'subtotal' => $price,
            'discount' => $discount,
            'total' => $finalPrice,
            'currency' => 'USD',
            'status' => 'pending',
            'billing_address' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);

        // Créer l'élément de commande
        $salePriceForOrder = $course->is_sale_active ? $course->active_sale_price : null;

        OrderItem::create([
            'order_id' => $order->id,
            'course_id' => $course->id,
            'price' => $price,
            'sale_price' => $salePriceForOrder,
            'total' => $finalPrice,
        ]);

        // Traiter le paiement selon la méthode
        switch ($request->payment_method) {
            case 'stripe':
                return $this->processStripePayment($order, $finalPrice);
            case 'paypal':
                return $this->processPayPalPayment($order, $finalPrice);
            case 'mobile_money':
                return $this->processMobileMoneyPayment($order, $finalPrice);
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Méthode de paiement non supportée.'
                ]);
        }
    }

    private function processStripePayment(Order $order, $amount)
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount * 100, // Stripe utilise les centimes
                'currency' => 'usd',
                'metadata' => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ],
            ]);

            // Créer l'enregistrement de paiement
            Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'stripe',
                'payment_id' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => 'USD',
                'status' => 'pending',
                'payment_data' => [
                    'client_secret' => $paymentIntent->client_secret,
                ],
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'order_id' => $order->id,
            ]);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de paiement Stripe: ' . $e->getMessage()
            ]);
        }
    }

    private function processPayPalPayment(Order $order, $amount)
    {
        // TODO: Implémenter l'intégration PayPal
        return response()->json([
            'success' => false,
            'message' => 'Paiement PayPal en cours de développement.'
        ]);
    }

    private function processMobileMoneyPayment(Order $order, $amount)
    {
        // TODO: Implémenter l'intégration Mobile Money
        return response()->json([
            'success' => false,
            'message' => 'Paiement Mobile Money en cours de développement.'
        ]);
    }

    public function success(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = Order::findOrFail($orderId);

        // Mettre à jour le statut de la commande
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Mettre à jour le paiement
        $payment = $order->payments()->first();
        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);
        }

        // Créer l'inscription de l'étudiant
        // La méthode createAndNotify envoie automatiquement les notifications et emails
        foreach ($order->orderItems as $item) {
            $enrollment = \App\Models\Enrollment::createAndNotify([
                'user_id' => $order->user_id,
                'course_id' => $item->course_id,
                'order_id' => $order->id,
                'status' => 'active',
            ]);

            // Note: Le compteur d'étudiants est maintenant calculé dynamiquement via les enrollments
        }

        // Mettre à jour le coupon si utilisé
        if ($order->coupon) {
            $order->coupon->increment('used_count');
        }

        // Charger les relations nécessaires pour les emails et notifications
        $order->load(['user', 'orderItems.course', 'coupon', 'affiliate', 'payments']);

        // Envoyer la notification de confirmation de paiement
        $this->sendPaymentConfirmation($order);

        // Envoyer la facture par email
        $this->sendInvoiceEmail($order);

        return view('payments.success', compact('order'));
    }

    public function cancel(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = Order::findOrFail($orderId);

        // Mettre à jour le statut de la commande
        $order->update(['status' => 'cancelled']);

        // Mettre à jour le paiement
        $payment = $order->payments()->first();
        if ($payment) {
            $payment->update(['status' => 'cancelled']);
        }

        return view('payments.cancel', compact('order'));
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Traiter les événements Stripe
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event->data->object);
                break;
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function handlePaymentSucceeded($paymentIntent)
    {
        $payment = Payment::where('payment_id', $paymentIntent->id)->first();
        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            $order = $payment->order;
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Créer l'inscription de l'étudiant si pas déjà fait
            // La méthode createAndNotify envoie automatiquement les notifications et emails
            if (!$order->enrollments()->exists()) {
                foreach ($order->orderItems as $item) {
                    $enrollment = \App\Models\Enrollment::createAndNotify([
                        'user_id' => $order->user_id,
                        'course_id' => $item->course_id,
                        'order_id' => $order->id,
                        'status' => 'active',
                    ]);
                }
            }

            // Mettre à jour le coupon si utilisé
            if ($order->coupon) {
                $order->coupon->increment('used_count');
            }

            // Charger les relations nécessaires pour les emails et notifications
            $order->load(['user', 'orderItems.course', 'coupon', 'affiliate', 'payments']);

            // Envoyer la notification de confirmation de paiement
            $this->sendPaymentConfirmation($order);

            // Envoyer la facture par email
            $this->sendInvoiceEmail($order);
        }
    }

    private function handlePaymentFailed($paymentIntent)
    {
        $payment = Payment::where('payment_id', $paymentIntent->id)->first();
        if ($payment) {
            $payment->update(['status' => 'failed']);
            $order = $payment->order;
            $order->update(['status' => 'failed']);
        }
    }

    /**
     * Envoyer la notification de confirmation de paiement
     */
    private function sendPaymentConfirmation(Order $order)
    {
        try {
            // Vérifier que l'utilisateur existe
            if (!$order->user) {
                \Log::warning("Impossible d'envoyer la confirmation de paiement : utilisateur manquant pour la commande {$order->id}");
                return;
            }

            // Vérifier si la notification n'a pas déjà été envoyée (éviter doublons)
            $alreadyNotified = $order->user->notifications()
                ->where('type', PaymentReceived::class)
                ->where('data->order_id', $order->id)
                ->exists();

            if (!$alreadyNotified) {
                // Envoyer l'email directement de manière synchrone
                try {
                    Mail::to($order->user->email)->send(new \App\Mail\PaymentReceivedMail($order));
                    \Log::info("Email PaymentReceivedMail envoyé directement à {$order->user->email} pour la commande {$order->order_number}");
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
                
                \Log::info("Notification de confirmation de paiement envoyée pour la commande {$order->order_number} à l'utilisateur {$order->user->id}");
            } else {
                \Log::info("Notification de confirmation de paiement déjà envoyée pour la commande {$order->order_number}, ignorée");
            }
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas faire échouer le processus de paiement
            \Log::error("Erreur lors de l'envoi de la notification de confirmation de paiement pour la commande {$order->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Envoyer la facture par email à l'utilisateur
     */
    private function sendInvoiceEmail(Order $order)
    {
        try {
            // Charger les relations nécessaires si pas déjà chargées
            if (!$order->relationLoaded('user')) {
                $order->load(['user', 'orderItems.course', 'coupon', 'affiliate', 'payments']);
            }

            // Vérifier que l'utilisateur existe et a un email
            if (!$order->user || !$order->user->email) {
                \Log::warning("Impossible d'envoyer la facture : utilisateur ou email manquant pour la commande {$order->id}");
                return;
            }

            // Envoyer l'email de facture avec enregistrement et notification
            EmailService::sendAndRecord(
                $order->user,
                $order->user->email,
                new InvoiceMail($order),
                'invoice',
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]
            );

            \Log::info("Facture envoyée avec succès pour la commande {$order->order_number} à {$order->user->email}");
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas faire échouer le processus de paiement
            \Log::error("Erreur lors de l'envoi de la facture pour la commande {$order->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
}
