<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MaxiCashController extends Controller
{
    /**
     * Process MaxiCash payment
     */
    public function process(Request $request)
    {
        try {
            $phone = $request->input('phone');
            $email = $request->input('email', '');
            $total = $request->input('total');
            $cartItems = json_decode($request->input('cart_items'), true);
            $reference = $request->input('reference');
            
            // Validate data
            if (!$phone || !$total || !$cartItems) {
                return redirect()->route('cart.checkout')
                    ->with('error', 'Informations de paiement invalides.');
            }
            
            // Get MaxiCash configuration
            $config = config('services.maxicash');
            
            // Amount in cents (MaxiCash requires amounts in cents)
            $amount = (int)($total * 100);
            
            // Currency (maxiDollar is the default for international)
            $currency = 'MaxiDollar';
            
            // Get success and failure URLs
            $successUrl = route('maxicash.success', ['reference' => $reference]);
            $cancelUrl = route('maxicash.cancel', ['reference' => $reference]);
            $failureUrl = route('maxicash.failure', ['reference' => $reference]);
            $notifyUrl = route('maxicash.notify', ['reference' => $reference]);
            
            // Prepare MaxiCash Gateway parameters
            $params = [
                'PayType' => 'MaxiCash',
                'Amount' => $amount,
                'Currency' => $currency,
                'Telephone' => $phone,
                'Email' => $email,
                'MerchantID' => $config['merchant_id'],
                'MerchantPassword' => $config['merchant_password'],
                'Language' => 'en',
                'Reference' => $reference,
                'accepturl' => $successUrl,
                'cancelurl' => $cancelUrl,
                'declineurl' => $failureUrl,
                'notifyurl' => $notifyUrl,
            ];
            
            // Determine if using sandbox or live
            $gatewayUrl = $config['sandbox'] 
                ? 'https://api-testbed.maxicashapp.com/PayEntryPost'
                : 'https://api.maxicashapp.com/PayEntryPost';
            
            // Store payment data in session for later use
            session([
                'maxicash_payment' => [
                    'reference' => $reference,
                    'amount' => $total,
                    'cart_items' => $cartItems,
                    'phone' => $phone,
                    'email' => $email,
                ]
            ]);
            
            // Return view with hidden form that will auto-submit to MaxiCash
            return view('payments.maxicash.form', [
                'params' => $params,
                'gatewayUrl' => $gatewayUrl
            ]);
            
        } catch (\Exception $e) {
            Log::error('MaxiCash Payment Error: ' . $e->getMessage());
            return redirect()->route('cart.checkout')
                ->with('error', 'Erreur lors de l\'initiation du paiement MaxiCash.');
        }
    }
    
    /**
     * Handle successful payment callback
     */
    public function success(Request $request)
    {
        try {
            $reference = $request->input('reference');
            $paymentData = session('maxicash_payment');
            
            if (!$paymentData || $paymentData['reference'] !== $reference) {
                return redirect()->route('cart.checkout')
                    ->with('error', 'Données de paiement invalides.');
            }
            
            // Create order
            $order = $this->createOrder($paymentData);
            
            // Clear session
            session()->forget('maxicash_payment');
            session()->forget('cart');
            
            // Clear user's cart if authenticated
            if (Auth::check()) {
                Auth::user()->cartItems()->delete();
            }
            
            return view('payments.maxicash.success', [
                'order' => $order,
                'paymentData' => $paymentData
            ]);
            
        } catch (\Exception $e) {
            Log::error('MaxiCash Success Error: ' . $e->getMessage());
            return redirect()->route('cart.checkout')
                ->with('error', 'Erreur lors de la confirmation du paiement.');
        }
    }
    
    /**
     * Handle payment cancellation
     */
    public function cancel(Request $request)
    {
        $reference = $request->input('reference');
        
        return redirect()->route('cart.checkout')
            ->with('error', 'Paiement annulé. Vous pouvez réessayer.');
    }
    
    /**
     * Handle payment failure
     */
    public function failure(Request $request)
    {
        $reference = $request->input('reference');
        
        // Clear session
        session()->forget('maxicash_payment');
        
        return redirect()->route('cart.checkout')
            ->with('error', 'Échec du paiement. Veuillez réessayer ou choisir un autre mode de paiement.');
    }
    
    /**
     * Handle MaxiCash notification (webhook)
     */
    public function notify(Request $request)
    {
        try {
            $reference = $request->input('reference');
            
            // Process the notification from MaxiCash
            // Update order status based on the notification
            Log::info('MaxiCash Notification received', $request->all());
            
            return response()->json(['status' => 'received']);
            
        } catch (\Exception $e) {
            Log::error('MaxiCash Notification Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
    
    /**
     * Create order from payment data
     */
    private function createOrder($paymentData)
    {
        // Import the necessary models
        $orderModel = app('App\Models\Order');
        $orderItemModel = app('App\Models\OrderItem');
        
        // Calculate total
        $total = $paymentData['amount'];
        $cartItems = $paymentData['cart_items'];
        
        // Create order
        $order = $orderModel::create([
            'user_id' => Auth::id(),
            'reference' => $paymentData['reference'],
            'status' => 'paid',
            'payment_method' => 'maxicash',
            'total' => $total,
            'currency' => 'USD',
        ]);
        
        // Create order items
        foreach ($cartItems as $item) {
            $course = $item['course'] ?? null;
            if (!$course) continue;
            
            $orderItemModel::create([
                'order_id' => $order->id,
                'course_id' => $course['id'] ?? $course->id,
                'price' => $item['price'] ?? $course['current_price'] ?? 0,
                'quantity' => $item['quantity'] ?? 1,
            ]);
            
            // Enroll user in the course
            if (Auth::check() && isset($course['id'])) {
                $this->enrollUserInCourse($course['id']);
            }
        }

        // Envoyer la facture par email
        try {
            $order->load(['user', 'orderItems.course', 'coupon', 'affiliate', 'payments']);
            if ($order->user && $order->user->email) {
                \Illuminate\Support\Facades\Mail::to($order->user->email)
                    ->send(new \App\Mail\InvoiceMail($order));
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi de la facture pour la commande {$order->id}: " . $e->getMessage());
        }
        
        return $order;
    }
    
    /**
     * Enroll user in a course
     */
    private function enrollUserInCourse($courseId)
    {
        $enrollmentModel = app('App\Models\Enrollment');
        
        // Check if user is already enrolled
        $existingEnrollment = $enrollmentModel::where('user_id', Auth::id())
            ->where('course_id', $courseId)
            ->first();
        
        if (!$existingEnrollment) {
            $enrollmentModel::create([
                'user_id' => Auth::id(),
                'course_id' => $courseId,
                'status' => 'active',
            ]);

            // Envoyer l'email de confirmation d'inscription
            try {
                $course = app('App\Models\Course')::find($courseId);
                $user = Auth::user();
                if ($course && $user) {
                    $user->notify(new \App\Notifications\CourseEnrolled($course));
                }
            } catch (\Exception $e) {
                \Log::error("Erreur lors de l'envoi de l'email d'inscription: " . $e->getMessage());
            }
        }
    }
}

