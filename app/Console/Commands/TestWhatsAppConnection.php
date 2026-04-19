<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class TestWhatsAppConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test 
                            {--phone= : Numéro de téléphone pour tester l\'envoi (format: 229XXXXXXXX)}
                            {--message= : Message de test (défaut: "Test depuis Laravel")}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester la connexion à Evolution API et envoyer un message de test';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $whatsappService = app(WhatsAppService::class);
        
        $this->info('🔍 Vérification de la connexion à Evolution API...');
        $this->newLine();
        
        // Vérifier la connexion
        $connectionStatus = $whatsappService->checkConnection();
        
        if ($connectionStatus['connected']) {
            $this->info('✅ Connexion active!');
            $this->line('   État: ' . ($connectionStatus['state'] ?? 'connected'));
        } else {
            $this->error('❌ Connexion non active!');
            $this->line('   État: ' . ($connectionStatus['state'] ?? 'unknown'));
            if (!empty($connectionStatus['diagnostic'])) {
                $this->newLine();
                $this->warn('   Diagnostic (HTTP / réseau):');
                $this->line('   ' . $connectionStatus['diagnostic']);
            }
            $this->newLine();
            $this->warn('   Config effective (Laravel):');
            $this->line('   WHATSAPP_BASE_URL → ' . (config('services.whatsapp.base_url') ?: '(vide)'));
            $this->line('   WHATSAPP_INSTANCE_NAME → ' . (config('services.whatsapp.instance_name') ?: '(vide)'));
            $key = (string) config('services.whatsapp.api_key');
            $this->line('   WHATSAPP_API_KEY → ' . ($key === '' ? '(vide — à remplir)' : 'défini (' . strlen($key) . ' caractères)'));
            $this->newLine();
            $this->line('   Test manuel depuis ce serveur (remplacez LA_CLE par votre clé Evolution):');
            $this->line('   curl -sS -w "\\nHTTP:%{http_code}\\n" -H "apikey: LA_CLE" "http://127.0.0.1:8080/instance/fetchInstances"');
            $this->newLine();
            $this->warn('⚠️  Assurez-vous que:');
            $this->line('   1. Evolution API est démarré');
            $this->line('   2. L\'instance est créée et connectée à WhatsApp');
            $this->line('   3. Les variables d\'environnement sont correctement configurées');
            $this->newLine();
            $this->line('   Consultez WHATSAPP_SETUP.md pour plus d\'informations');
            return Command::FAILURE;
        }
        
        $this->newLine();
        
        // Tester l'envoi si un numéro est fourni
        $phone = $this->option('phone');
        if ($phone) {
            $message = $this->option('message') ?? 'Test depuis Laravel';
            
            $this->info("📤 Envoi d'un message de test à {$phone}...");
            $this->line("   Message: {$message}");
            $this->newLine();
            
            $result = $whatsappService->sendMessage($phone, $message);
            
            if ($result['success']) {
                $this->info('✅ Message envoyé avec succès!');
                $this->line('   ID du message: ' . ($result['message_id'] ?? 'N/A'));
            } else {
                $this->error('❌ Échec de l\'envoi du message');
                $this->line('   Erreur: ' . ($result['error'] ?? 'Erreur inconnue'));
                return Command::FAILURE;
            }
        } else {
            $this->comment('💡 Pour tester l\'envoi d\'un message, utilisez:');
            $this->line('   php artisan whatsapp:test --phone=229XXXXXXXX --message="Votre message"');
        }
        
        $this->newLine();
        return Command::SUCCESS;
    }
}
