<?php

namespace App\Console\Commands;

use App\Models\WalletHold;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseWalletHolds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:release-holds 
                            {--dry-run : Simuler sans appliquer les changements}
                            {--force : Forcer la libération même si la date n\'est pas atteinte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Libère automatiquement les fonds bloqués dont la période de blocage est terminée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForce = $this->option('force');

        $this->info('🔓 Démarrage de la libération des fonds bloqués...');
        $this->newLine();

        // Récupérer tous les holds libérables
        $query = WalletHold::where('status', 'held');
        
        if (!$isForce) {
            $query->where('held_until', '<=', now());
        }

        $holds = $query->with('wallet')->get();

        if ($holds->isEmpty()) {
            $this->info('✅ Aucun fonds à libérer pour le moment.');
            return Command::SUCCESS;
        }

        $this->info("📊 {$holds->count()} hold(s) à traiter");
        $this->newLine();

        $successCount = 0;
        $failureCount = 0;
        $totalAmount = 0;

        $progressBar = $this->output->createProgressBar($holds->count());
        $progressBar->start();

        foreach ($holds as $hold) {
            if ($isDryRun) {
                $this->line("  [DRY RUN] Libération de {$hold->amount} {$hold->currency} pour le wallet #{$hold->wallet_id}");
                $successCount++;
                $totalAmount += $hold->amount;
            } else {
                try {
                    if ($hold->release()) {
                        $successCount++;
                        $totalAmount += $hold->amount;
                        
                        Log::info('Hold libéré automatiquement', [
                            'hold_id' => $hold->id,
                            'wallet_id' => $hold->wallet_id,
                            'amount' => $hold->amount,
                            'currency' => $hold->currency,
                        ]);
                    } else {
                        $failureCount++;
                        
                        Log::warning('Échec de la libération automatique du hold', [
                            'hold_id' => $hold->id,
                            'wallet_id' => $hold->wallet_id,
                        ]);
                    }
                } catch (\Exception $e) {
                    $failureCount++;
                    
                    Log::error('Erreur lors de la libération automatique du hold', [
                        'hold_id' => $hold->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Résumé
        $this->info('═══════════════════════════════════════');
        $this->info('           RÉSUMÉ DE L\'OPÉRATION       ');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('⚠️  MODE SIMULATION (Dry Run)');
            $this->newLine();
        }

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Holds traités', $holds->count()],
                ['✅ Succès', $successCount],
                ['❌ Échecs', $failureCount],
                ['💰 Montant total libéré', number_format($totalAmount, 2)],
            ]
        );

        if ($successCount > 0) {
            $this->info('✅ Libération terminée avec succès !');
        }

        if ($failureCount > 0) {
            $this->error("⚠️  {$failureCount} échec(s) détecté(s). Consultez les logs pour plus de détails.");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
