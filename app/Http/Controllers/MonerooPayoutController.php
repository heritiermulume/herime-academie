<?php

namespace App\Http\Controllers;

use App\Services\MonerooPayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controller pour gérer les payouts Moneroo
 * 
 * Documentation: https://docs.moneroo.io/fr/payouts
 */
class MonerooPayoutController extends Controller
{
    /**
     * Callback pour les payouts Moneroo
     * Cette route est appelée par Moneroo pour notifier du statut d'un payout
     * 
     * Documentation: https://docs.moneroo.io/fr/introduction/webhooks
     */
    public function callback(Request $request)
    {
        try {
            $data = $request->all();

            Log::info("Callback Moneroo reçu pour payout", [
                'data' => $data,
            ]);

            // Valider que nous avons un payout_id
            $payoutData = $data['data'] ?? $data;
            if (!isset($payoutData['id'])) {
                Log::error("Callback Moneroo sans payout_id", ['data' => $data]);
                return response()->json(['error' => 'payout_id manquant'], 400);
            }

            // Traiter le callback via le service
            $monerooPayoutService = new MonerooPayoutService();
            $success = $monerooPayoutService->handleCallback($data);

            if ($success) {
                return response()->json(['status' => 'success'], 200);
            } else {
                return response()->json(['error' => 'Erreur lors du traitement du callback'], 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception lors du traitement du callback Moneroo", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all(),
            ]);

            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }

    /**
     * Vérifier le statut d'un payout
     * 
     * Documentation: https://docs.moneroo.io/fr/payouts/verifier-un-transfert
     */
    public function checkStatus(Request $request, string $payoutId)
    {
        try {
            $monerooPayoutService = new MonerooPayoutService();
            $result = $monerooPayoutService->checkPayoutStatus($payoutId);

            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 500);
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de la vérification du statut du payout Moneroo", [
                'payout_id' => $payoutId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }
}

