<?php

namespace App\Http\Controllers;

use App\Services\PawaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PawaPayPayoutController extends Controller
{
    /**
     * Callback pour les payouts pawaPay
     * Cette route est appelée par pawaPay pour notifier du statut d'un payout
     */
    public function callback(Request $request)
    {
        try {
            $data = $request->all();

            Log::info("Callback pawaPay reçu pour payout", [
                'data' => $data,
            ]);

            // Valider que nous avons un payoutId
            if (!isset($data['payoutId'])) {
                Log::error("Callback pawaPay sans payoutId", ['data' => $data]);
                return response()->json(['error' => 'payoutId manquant'], 400);
            }

            // Traiter le callback via le service
            $pawaPayService = new PawaPayService();
            $success = $pawaPayService->handleCallback($data);

            if ($success) {
                return response()->json(['status' => 'success'], 200);
            } else {
                return response()->json(['error' => 'Erreur lors du traitement du callback'], 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception lors du traitement du callback pawaPay", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }

    /**
     * Vérifier le statut d'un payout
     */
    public function checkStatus(Request $request, string $payoutId)
    {
        try {
            $pawaPayService = new PawaPayService();
            $result = $pawaPayService->checkPayoutStatus($payoutId);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de la vérification du statut du payout", [
                'payout_id' => $payoutId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }
}
