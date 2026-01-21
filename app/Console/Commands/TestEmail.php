<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\CustomAnnouncementMail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester l\'envoi d\'un email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? config('mail.from.address');
        
        $this->info("Test d'envoi d'email à: {$email}");
        $this->newLine();
        
        // Afficher la configuration
        $this->info("Configuration du mailer:");
        $mailer = config('mail.default');
        $mailerConfig = config("mail.mailers.{$mailer}");
        
        $this->table(
            ['Paramètre', 'Valeur'],
            [
                ['Mailer par défaut', $mailer],
                ['Transport', $mailerConfig['transport'] ?? 'N/A'],
                ['Host', $mailerConfig['host'] ?? 'N/A'],
                ['Port', $mailerConfig['port'] ?? 'N/A'],
                ['Encryption', $mailerConfig['encryption'] ?? 'N/A'],
                ['Username', $mailerConfig['username'] ?? 'N/A'],
                ['From Address', config('mail.from.address')],
                ['From Name', config('mail.from.name')],
            ]
        );
        
        $this->newLine();
        
        // Avertissement si le mailer est en mode log ou array
        if (in_array($mailerConfig['transport'] ?? '', ['log', 'array'])) {
            $this->warn("⚠️  ATTENTION: Le mailer est configuré en mode '{$mailerConfig['transport']}'");
            $this->warn("   Les emails seront enregistrés dans les logs mais ne seront PAS envoyés réellement!");
            $this->newLine();
        }
        
        // Tester l'envoi
        $this->info("Tentative d'envoi...");
        
        try {
            $mailable = new CustomAnnouncementMail(
                'Test d\'envoi d\'email - Herime Académie',
                '<p>Ceci est un email de test pour vérifier la configuration SMTP.</p><p>Si vous recevez cet email, la configuration est correcte.</p>',
                []
            );
            
            Mail::to($email)->send($mailable);
            
            $this->info("✅ Email envoyé avec succès!");
            $this->info("   Vérifiez votre boîte de réception (et les spams) à l'adresse: {$email}");
            
            if (in_array($mailerConfig['transport'] ?? '', ['log', 'array'])) {
                $this->newLine();
                $this->warn("   Note: En mode '{$mailerConfig['transport']}', l'email est enregistré dans storage/logs/laravel.log");
                $this->info("   Consultez les logs pour voir le contenu de l'email.");
            }
            
            return Command::SUCCESS;
        } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
            $this->error("❌ Erreur SMTP:");
            $this->error("   " . $e->getMessage());
            $this->newLine();
            $this->warn("Vérifiez:");
            $this->line("   - Les paramètres SMTP dans .env");
            $this->line("   - La connexion au serveur SMTP");
            $this->line("   - Les identifiants (username/password)");
            $this->line("   - Le port et le chiffrement (TLS/SSL)");
            
            Log::error("Erreur SMTP lors du test d'email", [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'envoi:");
            $this->error("   " . $e->getMessage());
            $this->newLine();
            $this->line("Classe d'erreur: " . get_class($e));
            
            Log::error("Erreur lors du test d'email", [
                'email' => $email,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
}
