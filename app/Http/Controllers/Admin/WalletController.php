<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProviderPayout;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletPayout;
use App\Models\WalletPayoutAccount;
use App\Models\Setting;
use App\Services\AdminWalletPayoutNotifier;
use App\Services\MonerooPayoutService;
use App\Services\MonerooPayoutMethodsService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function __construct(
        protected MonerooPayoutService $monerooPayoutService,
        protected MonerooPayoutMethodsService $payoutMethodsService,
        protected AdminWalletPayoutNotifier $walletPayoutNotifier
    ) {}

    /**
     * Tableau de bord Wallet (onglet Accueil).
     * Les portefeuilles sont regroupés par devise configurée sur le site (devise de base + devises des wallets existants).
     */
    public function index()
    {
        $baseCurrency = Setting::get('base_currency', 'USD');
        $currenciesInWallets = Wallet::where('is_active', true)->distinct()->pluck('currency')->filter()->values()->toArray();
        $configuredCurrencies = array_values(array_unique(array_merge([$baseCurrency], $currenciesInWallets)));
        sort($configuredCurrencies);

        $walletsByCurrency = [];
        foreach ($configuredCurrencies as $currency) {
            $walletsInCurrency = Wallet::where('is_active', true)->where('currency', $currency)->get();
            $walletsByCurrency[] = [
                'currency' => $currency,
                'balance' => $walletsInCurrency->sum('balance'),
                'available_balance' => $walletsInCurrency->sum('available_balance'),
                'held_balance' => $walletsInCurrency->sum('held_balance'),
                'total_earned' => $walletsInCurrency->sum('total_earned'),
                'wallets_count' => $walletsInCurrency->count(),
            ];
        }

        $recentGains = WalletTransaction::with('wallet.user:id,name')
            ->whereIn('type', ['credit', 'commission', 'bonus', 'refund'])
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.wallet.index', compact('walletsByCurrency', 'recentGains'));
    }

    /**
     * Onglet Solde : solde total (= revenu dashboard - retraits réussis), entrées, sorties
     */
    public function balance(Request $request)
    {
        $wallets = Wallet::with('user:id,name,email')->where('is_active', true)->get();
        $totalAvailable = $wallets->sum('available_balance');
        $totalHeld = $wallets->sum('held_balance');

        // Même calcul que dashboard/analytics : revenus internes + commissions
        $internalRevenue = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->whereDoesntHave('orderItems', function ($query) {
                $query->whereHas('content', function ($q) {
                    $q->whereHas('provider', function ($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->get()
            ->sum(fn ($o) => $o->total_amount ?? $o->total ?? 0);
        $commissionsRevenue = ProviderPayout::withTrashed()->where('status', 'completed')
            ->sum('commission_amount');
        $totalRevenue = $internalRevenue + $commissionsRevenue;

        // Retraits wallet réussis (payouts Moneroo + retraits manuels) à soustraire du revenu
        $totalCompletedPayouts = (float) WalletPayout::where('status', 'completed')->sum('amount');
        $totalBalance = max(0, $totalRevenue - $totalCompletedPayouts);

        $inflows = WalletTransaction::with('wallet.user:id,name')
            ->whereIn('type', ['credit', 'commission', 'bonus', 'refund'])
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'inflows_page');

        $outflows = WalletTransaction::with('wallet.user:id,name')
            ->whereIn('type', ['debit', 'payout', 'withdrawal'])
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'outflows_page');

        return view('admin.wallet.balance', compact(
            'wallets', 'totalBalance', 'totalAvailable', 'totalHeld',
            'inflows', 'outflows'
        ));
    }

    /**
     * Exporter le solde wallet (CSV : titre, résumé, entrées, sorties, totaux)
     */
    public function exportBalance(Request $request)
    {
        $baseCurrency = Setting::get('base_currency', 'USD');
        $wallets = Wallet::with('user:id,name,email')->where('is_active', true)->get();
        $totalAvailable = $wallets->sum('available_balance');
        $totalHeld = $wallets->sum('held_balance');

        $internalRevenue = Order::withTrashed()->whereIn('status', ['paid', 'completed'])
            ->whereDoesntHave('orderItems', function ($query) {
                $query->whereHas('content', function ($q) {
                    $q->whereHas('provider', function ($providerQuery) {
                        $providerQuery->where('role', 'provider');
                    });
                });
            })
            ->get()
            ->sum(fn ($o) => $o->total_amount ?? $o->total ?? 0);
        $commissionsRevenue = ProviderPayout::withTrashed()->where('status', 'completed')
            ->sum('commission_amount');
        $totalRevenue = $internalRevenue + $commissionsRevenue;
        $totalCompletedPayouts = (float) WalletPayout::where('status', 'completed')->sum('amount');
        $totalBalance = max(0, $totalRevenue - $totalCompletedPayouts);

        $inflows = WalletTransaction::with('wallet.user:id,name')
            ->whereIn('type', ['credit', 'commission', 'bonus', 'refund'])
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->limit(5000)
            ->get();

        $outflows = WalletTransaction::with('wallet.user:id,name')
            ->whereIn('type', ['debit', 'payout', 'withdrawal'])
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->limit(5000)
            ->get();

        $totalInflowsAmount = $inflows->sum('amount');
        $totalOutflowsAmount = $outflows->sum('amount');

        $filename = 'wallet-solde_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($baseCurrency, $totalBalance, $totalAvailable, $totalHeld, $inflows, $outflows, $totalInflowsAmount, $totalOutflowsAmount) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Export Wallet - Solde - Herime Académie']);
            fputcsv($file, ['Date d\'export', now()->format('d/m/Y à H:i')]);
            fputcsv($file, []);
            fputcsv($file, ['Résumé']);
            fputcsv($file, ['Solde total', number_format($totalBalance, 2) . ' ' . $baseCurrency]);
            fputcsv($file, ['Disponible au retrait', number_format($totalAvailable, 2) . ' ' . $baseCurrency]);
            fputcsv($file, ['En période de blocage', number_format($totalHeld, 2) . ' ' . $baseCurrency]);
            fputcsv($file, []);

            fputcsv($file, ['Entrées (paiements reçus)']);
            fputcsv($file, ['Date', 'Portefeuille', 'Type', 'Montant', 'Devise', 'Description']);
            foreach ($inflows as $tx) {
                fputcsv($file, [
                    $tx->created_at->format('d/m/Y H:i'),
                    $tx->wallet && $tx->wallet->user ? $tx->wallet->user->name : '—',
                    $tx->type,
                    number_format((float) $tx->amount, 2),
                    $tx->currency ?? $baseCurrency,
                    Str::limit($tx->description ?? '', 100),
                ]);
            }
            fputcsv($file, ['Total entrées', '', '', number_format($totalInflowsAmount, 2), $baseCurrency, '']);
            fputcsv($file, []);

            fputcsv($file, ['Sorties (payouts / retraits)']);
            fputcsv($file, ['Date', 'Portefeuille', 'Type', 'Montant', 'Devise', 'Description']);
            foreach ($outflows as $tx) {
                fputcsv($file, [
                    $tx->created_at->format('d/m/Y H:i'),
                    $tx->wallet && $tx->wallet->user ? $tx->wallet->user->name : '—',
                    $tx->type,
                    number_format((float) $tx->amount, 2),
                    $tx->currency ?? $baseCurrency,
                    Str::limit($tx->description ?? '', 100),
                ]);
            }
            fputcsv($file, ['Total sorties', '', '', number_format($totalOutflowsAmount, 2), $baseCurrency, '']);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Onglet Comptes : comptes de paiement (bénéficiaires)
     */
    public function accounts()
    {
        $accounts = WalletPayoutAccount::orderByDesc('is_default')->orderBy('name')->get();
        $monerooData = $this->payoutMethodsService->getPayoutMethods();

        return view('admin.wallet.accounts', compact('accounts', 'monerooData'));
    }

    /**
     * Enregistrer un nouveau compte de paiement
     */
    public function storeAccount(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|size:2|alpha',
            'method' => 'required|string|max:64',
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'currency' => 'required|string|size:3|alpha',
            'recipient_first_name' => 'nullable|string|max:100',
            'recipient_last_name' => 'nullable|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        $account = WalletPayoutAccount::create([
            'name' => $validated['name'],
            'country_code' => strtoupper($validated['country']),
            'method' => $validated['method'],
            'phone' => $validated['phone'],
            'currency' => strtoupper($validated['currency']),
            'recipient_first_name' => $validated['recipient_first_name'] ?? null,
            'recipient_last_name' => $validated['recipient_last_name'] ?? null,
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'is_active' => true,
        ]);

        if ($account->is_default) {
            $account->setAsDefault();
        }

        return redirect()->route('admin.wallet.accounts')
            ->with('success', 'Compte de paiement ajouté avec succès.');
    }

    /**
     * Supprimer un compte de paiement
     */
    public function destroyAccount(WalletPayoutAccount $payoutAccount)
    {
        $payoutAccount->delete();
        return redirect()->route('admin.wallet.accounts')
            ->with('success', 'Compte de paiement supprimé.');
    }

    /**
     * Onglet Paiements : initier un payout, liste des paiements.
     * Portefeuille source = portefeuilles principaux du site (un par devise), pas les portefeuilles des ambassadeurs.
     */
    public function payments(Request $request)
    {
        $baseCurrency = Setting::get('base_currency', 'USD');
        $currenciesInWallets = Wallet::where('is_active', true)->distinct()->pluck('currency')->filter()->values()->toArray();
        $configuredCurrencies = array_values(array_unique(array_merge([$baseCurrency], $currenciesInWallets)));
        sort($configuredCurrencies);

        $walletsByCurrency = [];
        foreach ($configuredCurrencies as $currency) {
            $walletsInCurrency = Wallet::where('is_active', true)->where('currency', $currency)->get();
            $walletsByCurrency[] = [
                'currency' => $currency,
                'balance' => $walletsInCurrency->sum('balance'),
                'available_balance' => $walletsInCurrency->sum('available_balance'),
                'held_balance' => $walletsInCurrency->sum('held_balance'),
                'wallets_count' => $walletsInCurrency->count(),
            ];
        }

        $payouts = WalletPayout::with('wallet.user:id,name,email')
            ->orderByDesc('created_at')
            ->paginate(20);
        $payoutAccounts = WalletPayoutAccount::active()->orderByDesc('is_default')->get();
        $monerooData = $this->payoutMethodsService->getPayoutMethods();

        return view('admin.wallet.payments', compact(
            'walletsByCurrency', 'payouts', 'payoutAccounts', 'monerooData'
        ));
    }

    /**
     * Initier un payout (admin : depuis un portefeuille principal du site = devise, on débite un wallet de cette devise)
     */
    public function storePayout(Request $request)
    {
        $minPayout = (float) Setting::get('wallet_minimum_payout_amount', 5);

        $validated = $request->validate([
            'source_currency' => 'required|string|size:3|alpha',
            'amount' => "required|numeric|min:{$minPayout}",
            'payout_account_id' => 'nullable|exists:wallet_payout_accounts,id',
            'method' => 'required_without:payout_account_id|string|max:64',
            'phone' => 'required_without:payout_account_id|string|regex:/^\+?[0-9]{10,15}$/',
            'country' => 'required_without:payout_account_id|string|size:2|alpha',
            'currency' => 'required|string|size:3|alpha',
            'recipient_first_name' => 'nullable|string|max:100',
            'recipient_last_name' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];
        $sourceCurrency = strtoupper($validated['source_currency']);

        $wallet = Wallet::with('user')
            ->where('is_active', true)
            ->where('currency', $sourceCurrency)
            ->where('available_balance', '>=', $amount)
            ->orderByDesc('available_balance')
            ->first();

        if (!$wallet) {
            return redirect()->back()
                ->with('error', "Aucun portefeuille en {$sourceCurrency} avec un solde disponible suffisant (≥ " . number_format($amount, 2) . " {$sourceCurrency}).")
                ->withInput();
        }

        if (!$wallet->hasBalance($amount)) {
            return redirect()->back()
                ->with('error', 'Solde disponible insuffisant pour ce portefeuille.')
                ->withInput();
        }

        $phone = $validated['phone'] ?? null;
        $method = $validated['method'] ?? null;
        $country = $validated['country'] ?? null;
        $currency = strtoupper($validated['currency']);

        if (!empty($validated['payout_account_id'])) {
            $account = WalletPayoutAccount::findOrFail($validated['payout_account_id']);
            $phone = $account->phone;
            $method = $account->method;
            $country = $account->country_code;
            $currency = $account->currency;
        }

        $result = $this->monerooPayoutService->initiateWalletPayout(
            $wallet,
            (float) $validated['amount'],
            $currency,
            $phone,
            $method,
            $country,
            $validated['description'] ?? null
        );

        if ($result['success']) {
            return redirect()->route('admin.wallet.payments')
                ->with('success', 'Payout initié avec succès.');
        }

        if (!empty($result['payout'])) {
            try {
                $this->walletPayoutNotifier->notifyPayoutFailed($result['payout'], $result['error'] ?? null);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Admin wallet payout: échec notification (payout échoué)', [
                    'wallet_payout_id' => $result['payout']->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->back()
            ->with('error', $result['error'] ?? 'Erreur lors de l\'initiation du payout.')
            ->withInput();
    }

    /**
     * Enregistrer manuellement une transaction de retrait (sans passer par Moneroo).
     * Portefeuille = portefeuille principal du site (devise) ; on débite un wallet de cette devise.
     */
    public function storeManualPayout(Request $request)
    {
        $validated = $request->validate([
            'source_currency' => 'required|string|size:3|alpha',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $amount = (float) $validated['amount'];
        $sourceCurrency = strtoupper($validated['source_currency']);
        $description = $validated['description'] ?? 'Retrait manuel (admin)';

        $wallet = Wallet::with('user')
            ->where('is_active', true)
            ->where('currency', $sourceCurrency)
            ->where('available_balance', '>=', $amount)
            ->orderByDesc('available_balance')
            ->first();

        if (!$wallet) {
            return redirect()->back()
                ->with('error', "Aucun portefeuille en {$sourceCurrency} avec un solde disponible suffisant (≥ " . number_format($amount, 2) . " {$sourceCurrency}).")
                ->withInput();
        }

        if (!$wallet->hasBalance($amount)) {
            return redirect()->back()
                ->with('error', 'Solde disponible insuffisant pour ce portefeuille.')
                ->withInput();
        }

        $currency = $sourceCurrency;

        $user = $wallet->user;
        $names = $user ? [
            'first_name' => $user->name ? explode(' ', $user->name)[0] ?? $user->name : '—',
            'last_name' => $user->name && count(explode(' ', $user->name)) > 1 ? implode(' ', array_slice(explode(' ', $user->name), 1)) : ($user->name ?? '—'),
        ] : ['first_name' => '—', 'last_name' => '—'];

        $walletPayout = WalletPayout::create([
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'completed',
            'method' => 'manual',
            'description' => $description,
            'customer_email' => $user->email ?? null,
            'customer_first_name' => $names['first_name'],
            'customer_last_name' => $names['last_name'],
            'initiated_at' => now(),
            'completed_at' => now(),
        ]);

        try {
            $transaction = $wallet->debit(
                $amount,
                'payout',
                $description,
                $walletPayout,
                ['manual' => true]
            );
            $walletPayout->wallet_transaction_id = $transaction->id;
            $walletPayout->save();
        } catch (\Exception $e) {
            $walletPayout->delete();
            return redirect()->back()
                ->with('error', 'Erreur lors de l\'enregistrement du retrait : ' . $e->getMessage())
                ->withInput();
        }

        try {
            $this->walletPayoutNotifier->notifyPayoutCompleted($walletPayout);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Admin wallet payout: échec notification (payout réussi)', [
                'wallet_payout_id' => $walletPayout->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('admin.wallet.payments')
            ->with('success', 'Retrait manuel enregistré avec succès.');
    }

    /**
     * Onglet Configuration : paiements automatiques, règles d'automatisation
     */
    public function config()
    {
        $walletSettings = [
            'holding_period_days' => (int) Setting::get('wallet_holding_period_days', 7),
            'minimum_payout_amount' => (float) Setting::get('wallet_minimum_payout_amount', 5),
            'auto_release_enabled' => (bool) Setting::get('wallet_auto_release_enabled', true),
            'auto_payout_enabled' => (bool) Setting::get('wallet_auto_payout_enabled', false),
            'auto_payout_min_balance' => (float) Setting::get('wallet_auto_payout_min_balance', 0),
            'auto_payout_frequency' => Setting::get('wallet_auto_payout_frequency', 'weekly'),
        ];

        return view('admin.wallet.config', compact('walletSettings'));
    }

    /**
     * Enregistrer la configuration wallet (auto-payout, règles)
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'wallet_auto_payout_enabled' => 'nullable|boolean',
            'wallet_auto_payout_min_balance' => 'nullable|numeric|min:0',
            'wallet_auto_payout_frequency' => 'nullable|string|in:daily,weekly,monthly',
        ]);

        Setting::set('wallet_auto_payout_enabled', $request->boolean('wallet_auto_payout_enabled') ? 1 : 0, 'boolean');
        Setting::set('wallet_auto_payout_min_balance', $validated['wallet_auto_payout_min_balance'] ?? 0, 'number');
        Setting::set('wallet_auto_payout_frequency', $validated['wallet_auto_payout_frequency'] ?? 'weekly');

        return redirect()->route('admin.wallet.config')
            ->with('success', 'Configuration enregistrée.');
    }

    /**
     * API JSON : méthodes Moneroo (pays / opérateurs) pour modales
     */
    public function monerooMethods()
    {
        return response()->json($this->payoutMethodsService->getPayoutMethods());
    }
}
