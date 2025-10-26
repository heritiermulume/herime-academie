<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\MokoService;
use App\Models\MokoTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Course;
use App\Models\CartItem;
use App\Models\User;

class MokoController extends Controller
{
    protected $mokoService;

    public function __construct(MokoService $mokoService)
    {
        $this->mokoService = $mokoService;
    }

    /**
     * Afficher la page de paiement MOKO
     */
    public function showPaymentForm(Request $request)
    {
        // Récupérer le panier selon le statut de connexion
        if (auth()->check()) {
            // Utilisateur connecté : utiliser la base de données
            $cartItems = auth()->user()->cartItems()->with('course')->get();
            $courses = $cartItems->pluck('course')->filter();
            
            // Calculer le total comme dans CartController
            $total = 0;
            foreach ($cartItems as $item) {
                $course = $item->course;
                if ($course) {
                    $total += $course->current_price ?? $course->sale_price ?? $course->price;
                }
            }
        } else {
            // Utilisateur non connecté : utiliser la session
            $cartItemIds = session('cart', []);
            $courses = collect();
            $total = 0;
            
            foreach ($cartItemIds as $courseId) {
                $course = Course::find($courseId);
                if ($course) {
                    $courses->push($course);
                    $total += $course->current_price ?? $course->sale_price ?? $course->price;
                }
            }
        }
        
        if ($courses->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Votre panier est vide.');
        }

        $paymentMethods = $this->mokoService->getAvailablePaymentMethods();
        
        return view('moko.payment', compact('courses', 'total', 'paymentMethods'));
    }

    /**
     * Initier un paiement MOKO
     */
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'method' => 'required|in:airtel,orange,mpesa,africell',
            'customer_number' => 'required|string|min:9|max:15',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Récupérer le panier selon le statut de connexion
            if (auth()->check()) {
                // Utilisateur connecté : utiliser la base de données
                $cartItems = auth()->user()->cartItems()->with('course')->get();
                $courses = $cartItems->pluck('course')->filter();
                
                // Calculer le total comme dans CartController
                $total = 0;
                foreach ($cartItems as $item) {
                    $course = $item->course;
                    if ($course) {
                        $total += $course->current_price ?? $course->sale_price ?? $course->price;
                    }
                }
                
                \Log::info('MOKO Debug - User connected', [
                    'user_id' => auth()->id(),
                    'cart_items_count' => $cartItems->count(),
                    'courses_count' => $courses->count(),
                    'total' => $total,
                    'cart_items' => $cartItems->map(function($item) {
                        return [
                            'course_id' => $item->course_id,
                            'course_price' => $item->course->price ?? 0,
                            'course_sale_price' => $item->course->sale_price ?? 0,
                            'course_current_price' => $item->course->current_price ?? 0
                        ];
                    })
                ]);
            } else {
                // Utilisateur non connecté : utiliser la session
                $cartItemIds = session('cart', []);
                $courses = collect();
                $total = 0;
                
                foreach ($cartItemIds as $courseId) {
                    $course = Course::find($courseId);
                    if ($course) {
                        $courses->push($course);
                        $total += $course->current_price ?? $course->sale_price ?? $course->price;
                    }
                }
                
                \Log::info('MOKO Debug - User not connected', [
                    'cart_item_ids' => $cartItemIds,
                    'courses_count' => $courses->count(),
                    'total' => $total
                ]);
            }
            
            if ($courses->isEmpty()) {
                \Log::warning('MOKO Debug - Empty cart', [
                    'user_connected' => auth()->check(),
                    'user_id' => auth()->id(),
                    'session_id' => session()->getId()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Votre panier est vide.',
                    'debug' => [
                        'user_connected' => auth()->check(),
                        'user_id' => auth()->id(),
                        'session_id' => session()->getId()
                    ]
                ], 400);
            }

            // Générer une référence unique
            $reference = 'MOKO_' . time() . '_' . Str::random(8);

            // Préparer les données de transaction
            $transactionData = [
                'amount' => $total,
                'currency' => config('moko.default_currency', 'CDF'),
                'customer_number' => $request->customer_number,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'reference' => $reference,
                'method' => $request->method,
                'callback_url' => route('moko.callback'),
            ];

            // Valider les données
            $this->mokoService->validateTransactionData($transactionData);

            // Initier la transaction avec MOKO
            $response = $this->mokoService->initiateDebit($transactionData);

            if (!$response['success']) {
                throw new \Exception($response['error']);
            }

            $mokoData = $response['data'];

            // Créer la commande
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => auth()->id(),
                'subtotal' => $total,
                'total' => $total,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => 'moko_' . $request->method,
                'payment_reference' => $reference,
            ]);

            // Ajouter les articles à la commande
            foreach ($courses as $course) {
                $coursePrice = $course->current_price ?? $course->sale_price ?? $course->price;
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'price' => $course->price,
                    'sale_price' => $course->sale_price,
                    'total' => $coursePrice,
                ]);
            }

            // Enregistrer la transaction MOKO
            $mokoTransaction = MokoTransaction::create([
                'transaction_id' => $mokoData['Transaction_id'] ?? null,
                'reference' => $reference,
                'status' => 'pending',
                'trans_status' => $mokoData['Status'] ?? null,
                'amount' => $total,
                'currency' => $transactionData['currency'],
                'method' => $request->method,
                'action' => 'debit',
                'customer_number' => $request->customer_number,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'user_id' => auth()->id(),
                'order_id' => $order->id,
                'moko_response' => $mokoData,
                'callback_url' => $transactionData['callback_url'],
                'moko_created_at' => isset($mokoData['Created_At']) ? 
                    \Carbon\Carbon::parse($mokoData['Created_At']) : null,
                'moko_updated_at' => isset($mokoData['Updated_At']) ? 
                    \Carbon\Carbon::parse($mokoData['Updated_At']) : null,
            ]);

            DB::commit();

            // Vider le panier
            $request->session()->forget('cart');

            return response()->json([
                'success' => true,
                'message' => 'Transaction initiée avec succès. Vérifiez votre téléphone pour confirmer le paiement.',
                'transaction_id' => $mokoTransaction->transaction_id,
                'reference' => $reference,
                'status_url' => route('moko.status', $reference),
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('MOKO Payment Initiation Failed', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du paiement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function checkStatus(Request $request, $reference)
    {
        try {
            $transaction = MokoTransaction::where('reference', $reference)->first();
            
            if (!$transaction) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Transaction non trouvée.'
                    ], 404);
                }
                return view('moko.status', ['reference' => $reference])
                    ->with('error', 'Transaction non trouvée.');
            }

            // Vérifier le statut avec MOKO
            $response = $this->mokoService->verifyTransaction($reference);
            
            if ($response['success']) {
                $mokoData = $response['data'];
                
                // Mettre à jour le statut local
                $transaction->update([
                    'trans_status' => $mokoData['Trans_Status'] ?? null,
                    'comment' => $mokoData['Trans_Status_Description'] ?? null,
                    'moko_updated_at' => isset($mokoData['Updated_at']) ? 
                        \Carbon\Carbon::parse($mokoData['Updated_at']) : null,
                ]);

                // Mettre à jour le statut de la commande
                if ($transaction->order) {
                    $orderStatus = $this->mapMokoStatusToOrderStatus($mokoData['Trans_Status'] ?? '');
                    $transaction->order->update(['status' => $orderStatus]);
                }
            }

            // Retourner JSON pour les requêtes AJAX, HTML pour les requêtes normales
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => $transaction->status,
                    'trans_status' => $transaction->trans_status,
                    'comment' => $transaction->comment,
                    'is_successful' => $transaction->isSuccessful(),
                ]);
            }

            return view('moko.status', [
                'reference' => $reference,
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            Log::error('MOKO Status Check Failed', [
                'message' => $e->getMessage(),
                'reference' => $reference,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la vérification du statut: ' . $e->getMessage()
                ], 500);
            }

            return view('moko.status', ['reference' => $reference])
                ->with('error', 'Erreur lors de la vérification du statut.');
        }
    }

    /**
     * Callback pour les notifications MOKO
     */
    public function handleCallback(Request $request)
    {
        try {
            Log::info('MOKO Callback Received', $request->all());

            $reference = $request->input('Reference');
            $transactionId = $request->input('Transaction_id');
            $status = $request->input('Status');
            $transStatus = $request->input('Trans_Status');

            if (!$reference) {
                return response()->json(['error' => 'Reference manquante'], 400);
            }

            $transaction = MokoTransaction::where('reference', $reference)->first();
            
            if (!$transaction) {
                Log::warning('MOKO Callback: Transaction non trouvée', ['reference' => $reference]);
                return response()->json(['error' => 'Transaction non trouvée'], 404);
            }

            // Enregistrer les données du callback
            $transaction->saveCallbackData($request->all());

            // Mettre à jour le statut
            $localStatus = $this->mapMokoStatusToLocalStatus($transStatus);
            $transaction->updateStatus($localStatus, $transStatus, $request->input('Comment'));

            // Mettre à jour la commande
            if ($transaction->order) {
                $orderStatus = $this->mapMokoStatusToOrderStatus($transStatus);
                $transaction->order->update(['status' => $orderStatus]);

                // Si le paiement est réussi, inscrire l'utilisateur aux cours
                if ($orderStatus === 'completed') {
                    $this->enrollUserInCourses($transaction->order);
                }
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('MOKO Callback Error', [
                'message' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['error' => 'Erreur interne'], 500);
        }
    }

    /**
     * Page de succès
     */
    public function success(Request $request)
    {
        $reference = $request->get('reference');
        $transaction = null;

        if ($reference) {
            $transaction = MokoTransaction::where('reference', $reference)->first();
        }

        return view('moko.success', compact('transaction'));
    }

    /**
     * Page d'échec
     */
    public function failure(Request $request)
    {
        $reference = $request->get('reference');
        $transaction = null;

        if ($reference) {
            $transaction = MokoTransaction::where('reference', $reference)->first();
        }

        return view('moko.failure', compact('transaction'));
    }

    /**
     * Mapper le statut MOKO vers le statut local
     */
    private function mapMokoStatusToLocalStatus($mokoStatus)
    {
        switch (strtolower($mokoStatus)) {
            case 'successful':
                return 'success';
            case 'failed':
                return 'failed';
            case 'pending':
            default:
                return 'pending';
        }
    }

    /**
     * Mapper le statut MOKO vers le statut de commande
     */
    private function mapMokoStatusToOrderStatus($mokoStatus)
    {
        switch (strtolower($mokoStatus)) {
            case 'successful':
                return 'completed';
            case 'failed':
                return 'cancelled';
            case 'pending':
            default:
                return 'pending';
        }
    }

    /**
     * Inscrire l'utilisateur aux cours après paiement réussi
     */
    private function enrollUserInCourses(Order $order)
    {
        foreach ($order->items as $item) {
            if ($item->course) {
                // Créer l'inscription
                \App\Models\Enrollment::create([
                    'user_id' => $order->user_id,
                    'course_id' => $item->course_id,
                    'enrolled_at' => now(),
                ]);
            }
        }
    }
}
