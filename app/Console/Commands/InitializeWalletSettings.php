<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class InitializeWalletSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:init-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise les paramètres du Wallet dans la base de données s\'ils n\'existent pas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Initialisation des paramètres Wallet...');
        $this->newLine();

        $settings = [
            [
                'key' => 'wallet_holding_period_days',
                'value' => 7,
                'type' => 'number',
                'description' => 'Nombre de jours pendant lesquels les fonds sont bloqués avant d\'être disponibles au retrait',
            ],
            [
                'key' => 'wallet_minimum_payout_amount',
                'value' => 5,
                'type' => 'number',
                'description' => 'Montant minimum pour effectuer un retrait',
            ],
            [
                'key' => 'wallet_auto_release_enabled',
                'value' => 1,
                'type' => 'boolean',
                'description' => 'Activer la libération automatique des fonds bloqués',
            ],
        ];

        $created = 0;
        $existing = 0;

        foreach ($settings as $setting) {
            $exists = Setting::where('key', $setting['key'])->exists();

            if ($exists) {
                $this->line("  ⏭️  {$setting['key']} existe déjà");
                $existing++;
            } else {
                Setting::create($setting);
                $this->info("  ✅ {$setting['key']} créé avec la valeur : {$setting['value']}");
                $created++;
            }
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('           RÉSUMÉ DE L\'OPÉRATION       ');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        $this->table(
            ['Métrique', 'Valeur'],
            [
                ['Settings créés', $created],
                ['Settings existants', $existing],
                ['Total', count($settings)],
            ]
        );

        if ($created > 0) {
            $this->info('✅ Initialisation terminée avec succès !');
            $this->newLine();
            $this->info('💡 Vous pouvez maintenant configurer ces paramètres depuis l\'administration :');
            $this->info('   👉 ' . route('admin.settings'));
        } else {
            $this->info('ℹ️  Tous les paramètres existaient déjà.');
        }

        return Command::SUCCESS;
    }
}
