<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Coupon;
use App\Models\Affiliate;
use App\Models\Setting;
use App\Mail\InvoiceMail;
use App\Mail\PaymentFailedMail;
use App\Notifications\PaymentReceived;
use App\Services\EmailService;
use App\Services\PawaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Payer les formateurs externes si nécessaire
        $this->processExternalInstructorPayouts($order);

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

        // Charger les relations nécessaires pour l'email
        $order->load(['user', 'orderItems.course', 'payments']);
        
        // Envoyer l'email d'annulation de paiement
        $this->sendPaymentFailureEmail($order, 'Paiement annulé par l\'utilisateur');

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

            // Payer les formateurs externes si nécessaire
            $this->processExternalInstructorPayouts($order);
        }
    }

    private function handlePaymentFailed($paymentIntent)
    {
        $payment = Payment::where('payment_id', $paymentIntent->id)->first();
        if ($payment) {
            $failureReason = $paymentIntent->last_payment_error->message ?? 'Paiement refusé par la banque';
            $payment->update([
                'status' => 'failed',
                'failure_reason' => $failureReason,
            ]);
            $order = $payment->order;
            $order->update(['status' => 'failed']);
            
            // Charger les relations nécessaires pour l'email
            $order->load(['user', 'orderItems.course', 'payments']);
            
            // Envoyer l'email d'échec de paiement
            $this->sendPaymentFailureEmail($order, $failureReason);
        }
    }

    /**
     * Envoyer les emails de paiement (même logique que Enrollment::sendEnrollmentNotifications)
     * Cette méthode envoie directement les emails de manière synchrone
     */
    private function sendPaymentConfirmation(Order $order)
    {
        try {
            // Charger les relations nécessaires
            if (!$order->relationLoaded('user')) {
                $order->load('user');
            }
            if (!$order->relationLoaded('orderItems')) {
                $order->load('orderItems.course');
            }
            if (!$order->relationLoaded('coupon')) {
                $order->load('coupon');
            }
            if (!$order->relationLoaded('affiliate')) {
                $order->load('affiliate');
            }
            if (!$order->relationLoaded('payments')) {
                $order->load('payments');
            }

            $user = $order->user;

            if (!$user || !$user->email) {
                \Log::warning("Impossible d'envoyer les emails de paiement: utilisateur ou email manquant", [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                ]);
                return;
            }

            // Envoyer l'email et WhatsApp en parallèle
            try {
                $mailable = new \App\Mail\PaymentReceivedMail($order);
                $communicationService = app(\App\Services\CommunicationService::class);
                $communicationService->sendEmailAndWhatsApp($user, $mailable);
                \Log::info("Email et WhatsApp PaymentReceivedMail envoyés pour la commande {$order->order_number}", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $emailException) {
                \Log::error("Erreur lors de l'envoi de l'email PaymentReceivedMail", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $emailException->getMessage(),
                    'trace' => $emailException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }
            
            // Envoyer la notification (pour la base de données et l'affichage dans la navbar)
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            try {
                Notification::sendNow($user, new PaymentReceived($order));
                
                \Log::info("Notification PaymentReceived envoyée à l'utilisateur {$user->id} pour la commande {$order->id}", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $notifException) {
                \Log::error("Erreur lors de l'envoi de la notification PaymentReceived", [
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'error' => $notifException->getMessage(),
                    'trace' => $notifException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer le processus
            }
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas faire échouer le processus de paiement
            \Log::error("Erreur lors de l'envoi des emails de paiement pour la commande {$order->id}: " . $e->getMessage());
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

    /**
     * Envoyer l'email d'échec de paiement
     */
    private function sendPaymentFailureEmail(Order $order, ?string $failureReason = null)
    {
        try {
            // Charger les relations nécessaires si pas déjà chargées
            if (!$order->relationLoaded('user')) {
                $order->load(['user', 'orderItems.course', 'payments']);
            }

            // Vérifier que l'utilisateur existe et a un email
            if (!$order->user || !$order->user->email) {
                \Log::warning("Impossible d'envoyer l'email d'échec : utilisateur ou email manquant pour la commande {$order->id}");
                return;
            }

            // Envoyer l'email d'échec de manière synchrone (immédiate)
            $mailable = new PaymentFailedMail($order, $failureReason);
            $communicationService = app(\App\Services\CommunicationService::class);
            $communicationService->sendEmailAndWhatsApp($order->user, $mailable);

            \Log::info("Email d'échec de paiement envoyé pour la commande {$order->order_number} à {$order->user->email}");
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas faire échouer le processus
            \Log::error("Erreur lors de l'envoi de l'email d'échec de paiement pour la commande {$order->id}: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }

    /**
     * Traiter les paiements aux formateurs externes après un paiement réussi
     */
    private function processExternalInstructorPayouts(Order $order)
    {
        try {
            // Charger les orderItems avec les cours et leurs formateurs
            $order->load(['orderItems.course.instructor']);

            foreach ($order->orderItems as $orderItem) {
                $course = $orderItem->course;
                $instructor = $course->instructor;

                // Vérifier que le formateur existe
                if (!$instructor) {
                    Log::warning("Formateur introuvable pour le cours", [
                        'course_id' => $course->id,
                        'order_id' => $order->id,
                    ]);
                    continue;
                }

                // Vérifier que c'est un formateur externe (is_external_instructor activé ET role = instructor)
                if (!$instructor->isExternalInstructor()) {
                    Log::info("Formateur non externe ou paiements automatiques non activés - paiement ignoré", [
                        'instructor_id' => $instructor->id,
                        'instructor_name' => $instructor->name,
                        'instructor_role' => $instructor->role,
                        'is_external_instructor' => $instructor->is_external_instructor,
                        'course_id' => $course->id,
                        'course_title' => $course->title,
                        'order_id' => $order->id,
                        'message' => $instructor->role !== 'instructor' 
                            ? 'L\'utilisateur n\'est pas un formateur' 
                            : 'Le formateur n\'a pas activé l\'option "Activer les paiements automatiques" dans ses paramètres de paiement',
                    ]);
                    continue;
                }

                // Vérifier que toutes les informations pawaPay sont configurées
                if (!$instructor->pawapay_phone || !$instructor->pawapay_provider || !$instructor->pawapay_country) {
                    Log::warning("Formateur avec paiements automatiques activés mais informations pawaPay incomplètes", [
                        'instructor_id' => $instructor->id,
                        'instructor_name' => $instructor->name,
                        'course_id' => $course->id,
                        'course_title' => $course->title,
                        'order_id' => $order->id,
                        'has_phone' => !empty($instructor->pawapay_phone),
                        'has_provider' => !empty($instructor->pawapay_provider),
                        'has_country' => !empty($instructor->pawapay_country),
                        'message' => 'Le formateur a activé les paiements automatiques mais n\'a pas fourni toutes les informations de paiement nécessaires',
                    ]);
                    continue;
                }

                // Vérifier si un payout n'a pas déjà été créé pour cette commande et ce cours
                $existingPayout = \App\Models\InstructorPayout::where('order_id', $order->id)
                    ->where('course_id', $course->id)
                    ->first();

                if ($existingPayout) {
                    Log::info("Payout déjà créé pour ce cours et cette commande", [
                        'payout_id' => $existingPayout->payout_id,
                        'order_id' => $order->id,
                        'course_id' => $course->id,
                    ]);
                    continue;
                }

                // Calculer le montant à payer au formateur
                $coursePrice = $orderItem->total; // Prix payé par l'étudiant (après réduction)
                $commissionPercentage = Setting::get('external_instructor_commission_percentage', 20);
                $commissionAmount = ($coursePrice * $commissionPercentage) / 100;
                $payoutAmount = $coursePrice - $commissionAmount;

                // Utiliser la devise de la commande
                $currency = $order->currency ?? Setting::getBaseCurrency();

                // Initier le payout via pawaPay
                $pawaPayService = new PawaPayService();
                $result = $pawaPayService->initiatePayout(
                    $instructor->id,
                    $order->id,
                    $course->id,
                    $payoutAmount,
                    $currency,
                    $instructor->pawapay_phone,
                    $instructor->pawapay_provider,
                    $instructor->pawapay_country
                );

                if ($result['success']) {
                    // Mettre à jour le payout avec les informations de commission
                    $payout = $result['payout'];
                    $payout->update([
                        'commission_percentage' => $commissionPercentage,
                        'commission_amount' => $commissionAmount,
                    ]);

                    Log::info("Payout initié avec succès pour le formateur externe", [
                        'payout_id' => $result['payout_id'],
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'order_id' => $order->id,
                        'amount' => $payoutAmount,
                        'commission' => $commissionAmount,
                    ]);
                } else {
                    Log::error("Échec de l'initiation du payout pour le formateur externe", [
                        'instructor_id' => $instructor->id,
                        'course_id' => $course->id,
                        'order_id' => $order->id,
                        'error' => $result['error'] ?? 'Erreur inconnue',
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Logger l'erreur mais ne pas faire échouer le processus de paiement
            Log::error("Erreur lors du traitement des payouts aux formateurs externes", [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
