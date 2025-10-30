<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PawaPayController extends Controller
{
    private function baseUrl(): string
    {
        return rtrim(config('services.pawapay.base_url'), '/');
    }

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . config('services.pawapay.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    public function activeConf(Request $request)
    {
        $operationType = 'DEPOSIT';

        $query = ['operationType' => $operationType];
        // Si un pays est fourni, on filtre; sinon on récupère toute la configuration active
        if ($request->filled('country')) {
            $query['country'] = $request->query('country');
        }

        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl() . '/active-conf', $query);

        return response()->json($response->json(), $response->status());
    }

    public function initiate(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être connecté pour procéder au paiement.'
            ], 401);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string',
            'phoneNumber' => 'required|string',
            'provider' => 'required|string',
            'country' => 'nullable|string',
        ]);

        $user = auth()->user();

        // Récupérer les articles du panier
        $cartItems = $user->cartItems()->with('course')->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre panier est vide.'
            ], 400);
        }

        // Récupérer la devise de base du site
        $baseCurrency = \App\Models\Setting::getBaseCurrency();
        
        // Calculer le total réel depuis le panier (dans la devise de base du site)
        $subtotal = $cartItems->sum(function($item) {
            return $item->course->current_price ?? 0;
        });
        
        // IMPORTANT: Utiliser le montant converti et la devise envoyés par le frontend
        $paymentAmount = (float) $data['amount']; // Montant converti dans la devise sélectionnée
        $paymentCurrency = $data['currency'] ?? config('services.pawapay.default_currency'); // Devise sélectionnée

        // Créer l'Order (montants dans la devise de base du site)
        $order = Order::create([
            'order_number' => 'PP-' . strtoupper(Str::random(8)) . '-' . time(),
            'user_id' => $user->id,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal, // Total dans la devise de base du site
            'currency' => $baseCurrency, // Devise de la commande (devise de base du site)
            'status' => 'pending',
            'payment_method' => 'pawapay',
            'payment_provider' => $data['provider'] ?? null,
            'billing_address' => [
                'phone' => $data['phoneNumber'],
                'country' => $data['country'] ?? config('services.pawapay.default_country'),
                'payment_currency' => $paymentCurrency, // Devise utilisée pour le paiement
                'payment_amount' => $paymentAmount, // Montant dans la devise de paiement
            ],
        ]);

        // Créer les OrderItems
        foreach ($cartItems as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'course_id' => $cartItem->course_id,
                'price' => $cartItem->course->price ?? 0,
                'sale_price' => $cartItem->course->sale_price ?? null,
                'total' => $cartItem->course->current_price ?? 0,
            ]);
        }

        $depositId = (string) Str::uuid();
        // CRITIQUE: Utiliser le montant converti et la devise sélectionnée pour pawaPay
        $payload = [
            'depositId' => $depositId,
            'amount' => (string) $paymentAmount, // Montant converti dans la devise sélectionnée
            'currency' => $paymentCurrency, // Devise sélectionnée par l'utilisateur
            'payer' => [
                'type' => 'MMO',
                'accountDetails' => [
                    'phoneNumber' => $data['phoneNumber'],
                    'provider' => $data['provider'],
                ],
            ],
            'successfulUrl' => config('services.pawapay.successful_url') . '?depositId=' . $depositId,
            'failedUrl' => config('services.pawapay.failed_url') . '?depositId=' . $depositId,
        ];

        $response = Http::withHeaders($this->authHeaders())
            ->post($this->baseUrl() . '/deposits', $payload);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'initialisation du paiement.',
                'error' => $response->json()
            ], $response->status());
        }

        $responseData = $response->json();

        // Créer un enregistrement Payment (avec le montant et la devise utilisés pour le paiement)
        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'pawapay',
            'provider' => $data['provider'] ?? null,
            'payment_id' => $depositId,
            'amount' => $paymentAmount, // Montant converti dans la devise de paiement
            'currency' => $paymentCurrency, // Devise utilisée pour le paiement
            'status' => 'pending',
            'payment_data' => [
                'request' => $payload,
                'response' => $responseData,
            ],
        ]);

        return response()->json([
            'success' => true,
            'depositId' => $depositId,
            'order_id' => $order->id,
            ...$responseData
        ]);
    }

    public function status(string $depositId)
    {
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl() . "/deposits/{$depositId}");

        return response()->json($response->json(), $response->status());
    }

    public function webhook(Request $request)
    {
        $payload = $request->all();
        $depositId = $payload['depositId'] ?? null;
        $status = $payload['status'] ?? null;

        if (!$depositId) {
            return response()->json(['received' => false, 'message' => 'depositId missing'], 400);
        }

        $payment = Payment::where('payment_method', 'pawapay')
            ->where('payment_id', $depositId)
            ->with('order')
            ->first();

        if (!$payment) {
            \Log::warning('pawaPay webhook: Payment not found', ['depositId' => $depositId]);
            return response()->json(['received' => false, 'message' => 'Payment not found'], 404);
        }

        $mapped = match ($status) {
            'COMPLETED' => 'completed',
            'FAILED' => 'failed',
            'IN_RECONCILIATION' => 'pending',
            default => 'pending',
        };

        // Mettre à jour le Payment
        $payment->update([
            'status' => $mapped,
            'payment_data' => array_merge($payment->payment_data ?? [], [
                'callback' => $payload,
            ]),
            'processed_at' => $mapped === 'completed' ? now() : null,
        ]);

        // Si le paiement est complété, finaliser la commande
        if ($status === 'COMPLETED' && $payment->order) {
            $this->finalizeOrderAfterPayment($payment->order);
        } elseif ($status === 'FAILED' && $payment->order) {
            // Enregistrer la raison d'échec si disponible
            $failureReason = $payload['statusReason'] ?? $payload['message'] ?? ($payload['reason'] ?? null);
            $payment->update(['failure_reason' => $failureReason]);
            $payment->order->update(['status' => 'cancelled']);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Finaliser la commande après paiement réussi
     */
    private function finalizeOrderAfterPayment(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Vérifier si déjà finalisée
            if ($order->status === 'paid' || $order->status === 'completed') {
                return;
            }

            // Mettre à jour l'Order
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Créer les Enrollments pour chaque cours
            foreach ($order->orderItems as $orderItem) {
                // Vérifier si l'utilisateur n'est pas déjà inscrit
                $existingEnrollment = Enrollment::where('user_id', $order->user_id)
                    ->where('course_id', $orderItem->course_id)
                    ->first();

                if (!$existingEnrollment) {
                    Enrollment::create([
                        'user_id' => $order->user_id,
                        'course_id' => $orderItem->course_id,
                        'order_id' => $order->id,
                        'status' => 'active',
                    ]);
                }
            }

            // Vider le panier de l'utilisateur
            CartItem::where('user_id', $order->user_id)->delete();
        });
    }

    public function successfulRedirect(Request $request)
    {
        $depositId = $request->query('depositId');
        
        if ($depositId) {
            $payment = Payment::where('payment_method', 'pawapay')
                ->where('payment_id', $depositId)
                ->with('order')
                ->first();

            if ($payment && $payment->order) {
                // Vérifier le statut auprès de pawaPay
                $statusResponse = Http::withHeaders($this->authHeaders())
                    ->get($this->baseUrl() . "/deposits/{$depositId}");

                if ($statusResponse->successful()) {
                    $statusData = $statusResponse->json();
                    $status = $statusData['status'] ?? null;

                    // Si le paiement est complété mais pas encore finalisé localement
                    if ($status === 'COMPLETED' && $payment->status !== 'completed') {
                        $this->finalizeOrderAfterPayment($payment->order);
                    }

                    $order = $payment->order->fresh();
                    return view('payments.pawapay.success', compact('order'));
                }
            }
        }

        return view('payments.pawapay.success');
    }

    public function failedRedirect(Request $request)
    {
        return view('payments.pawapay.failed');
    }
}


