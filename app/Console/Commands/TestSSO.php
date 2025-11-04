<?php

namespace App\Console\Commands;

use App\Services\SSOService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestSSO extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sso:test {--token= : Token JWT à tester}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester la configuration et la connexion SSO avec compte.herime.com';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Test de la configuration SSO');
        $this->newLine();

        // 1. Vérifier la configuration
        $this->info('1️⃣ Vérification de la configuration...');
        $this->checkConfiguration();
        $this->newLine();

        // 2. Tester la connexion à l'API
        $this->info('2️⃣ Test de la connexion à l\'API SSO...');
        $this->testApiConnection();
        $this->newLine();

        // 3. Tester la validation de token (si fourni)
        $token = $this->option('token');
        if ($token) {
            $this->info('3️⃣ Test de validation de token...');
            $this->testTokenValidation($token);
            $this->newLine();
        } else {
            $this->warn('3️⃣ Test de validation de token ignoré (utilisez --token=xxx pour tester)');
            $this->newLine();
        }

        // 4. Vérifier les URLs
        $this->info('4️⃣ Vérification des URLs...');
        $this->checkUrls();
        $this->newLine();

        $this->info('✅ Tests terminés !');
        return 0;
    }

    /**
     * Vérifier la configuration
     */
    protected function checkConfiguration()
    {
        $ssoEnabled = config('services.sso.enabled');
        $ssoBaseUrl = config('services.sso.base_url');
        $ssoSecret = config('services.sso.secret');
        $ssoTimeout = config('services.sso.timeout', 10);

        $this->line('   SSO_ENABLED: ' . ($ssoEnabled ? '✅ Activé' : '❌ Désactivé'));
        $this->line('   SSO_BASE_URL: ' . ($ssoBaseUrl ?: '❌ Non configuré'));
        $this->line('   SSO_SECRET: ' . ($ssoSecret ? '✅ Configuré (' . strlen($ssoSecret) . ' caractères)' : '❌ Non configuré'));
        $this->line('   SSO_TIMEOUT: ' . $ssoTimeout . ' secondes');

        if (!$ssoBaseUrl || !$ssoSecret) {
            $this->error('   ⚠️  Configuration incomplète ! Vérifiez votre fichier .env');
            return false;
        }

        if (strlen($ssoSecret) !== 64) {
            $this->warn('   ⚠️  La clé secrète devrait faire 64 caractères hexadécimaux');
        }

        return true;
    }

    /**
     * Tester la connexion à l'API
     */
    protected function testApiConnection()
    {
        $ssoBaseUrl = config('services.sso.base_url');
        $ssoSecret = config('services.sso.secret');

        if (!$ssoBaseUrl || !$ssoSecret) {
            $this->error('   ❌ Configuration manquante pour tester la connexion');
            return false;
        }

        try {
            $this->line('   Test de connexion à: ' . $ssoBaseUrl);
            
            // Test simple de connexion (sans valider de token réel)
            $response = Http::timeout(5)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $ssoSecret,
                ])
                ->post($ssoBaseUrl . '/api/validate-token', [
                    'token' => 'test_connection_token',
                ]);

            if ($response->status() === 200 || $response->status() === 400 || $response->status() === 401) {
                $this->info('   ✅ Connexion à l\'API réussie (Status: ' . $response->status() . ')');
                $this->line('   📝 L\'endpoint répond correctement');
                return true;
            } elseif ($response->status() === 404) {
                $this->error('   ❌ Endpoint non trouvé (404)');
                $this->line('   Vérifiez que l\'endpoint /api/validate-token existe sur ' . $ssoBaseUrl);
                return false;
            } else {
                $this->warn('   ⚠️  Réponse inattendue (Status: ' . $response->status() . ')');
                return false;
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur de connexion: ' . $e->getMessage());
            $this->line('   Vérifiez que ' . $ssoBaseUrl . ' est accessible');
            return false;
        }
    }

    /**
     * Tester la validation d'un token
     */
    protected function testTokenValidation(string $token)
    {
        $ssoService = app(SSOService::class);

        try {
            $this->line('   Validation du token...');
            $userData = $ssoService->validateToken($token);

            if ($userData) {
                $this->info('   ✅ Token valide !');
                $this->line('   📋 Données utilisateur:');
                $this->line('      - ID: ' . ($userData['id'] ?? 'N/A'));
                $this->line('      - Email: ' . ($userData['email'] ?? 'N/A'));
                $this->line('      - Nom: ' . ($userData['name'] ?? 'N/A'));
                $this->line('      - Rôle: ' . ($userData['role'] ?? 'N/A'));
                return true;
            } else {
                $this->error('   ❌ Token invalide ou expiré');
                return false;
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur lors de la validation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier les URLs
     */
    protected function checkUrls()
    {
        $ssoService = app(SSOService::class);

        $loginUrl = $ssoService->getLoginUrl('https://academie.herime.com/sso/callback?redirect=/dashboard');
        $logoutUrl = $ssoService->getLogoutUrl('https://academie.herime.com');

        $this->line('   URL de connexion:');
        $this->line('   ' . $loginUrl);
        $this->newLine();
        $this->line('   URL de déconnexion:');
        $this->line('   ' . $logoutUrl);
    }
}

