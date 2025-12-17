<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestMonerooApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moneroo:test-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tester la connexion à l\'API Moneroo et afficher les méthodes disponibles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Test de connexion à l\'API Moneroo...');
        $this->newLine();

        // Vérifier la configuration
        $baseUrl = config('services.moneroo.base_url', 'https://api.moneroo.io/v1');
        $apiKey = config('services.moneroo.api_key');

        if (!$apiKey) {
            $this->error('❌ MONEROO_API_KEY non configurée dans le fichier .env');
            $this->newLine();
            $this->info('💡 Ajoutez cette ligne dans votre fichier .env :');
            $this->line('MONEROO_API_KEY=votre_cle_api_ici');
            return Command::FAILURE;
        }

        $this->info('✅ API Key trouvée : ' . substr($apiKey, 0, 10) . '...');
        $this->info('📍 Base URL : ' . $baseUrl);
        $this->newLine();

        // Tester plusieurs endpoints
        $endpoints = [
            '/payouts/available-methods' => 'Méthodes de payout disponibles',
            '/payouts/methods' => 'Méthodes de payout (alternatif)',
        ];

        foreach ($endpoints as $endpoint => $description) {
            $this->testEndpoint($baseUrl, $endpoint, $apiKey, $description);
        }

        return Command::SUCCESS;
    }

    private function testEndpoint($baseUrl, $endpoint, $apiKey, $description)
    {
        $url = rtrim($baseUrl, '/') . $endpoint;
        
        $this->info("🌐 Test de : $description");
        $this->line("   URL : $url");

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                $this->info("   ✅ Succès (Status: {$response->status()})");
                
                $data = $response->json();
                
                // Afficher la structure de la réponse
                $this->line('   📊 Structure de la réponse :');
                $this->displayStructure($data, '      ');
                
                // Compter les pays et providers si disponibles
                if (isset($data['data']['countries'])) {
                    $countryCount = count($data['data']['countries']);
                    $this->info("   🌍 Pays disponibles : $countryCount");
                } elseif (isset($data['countries'])) {
                    $countryCount = count($data['countries']);
                    $this->info("   🌍 Pays disponibles : $countryCount");
                }
                
                if (isset($data['data']['methods'])) {
                    $methodCount = count($data['data']['methods']);
                    $this->info("   📱 Méthodes de paiement : $methodCount");
                } elseif (isset($data['methods'])) {
                    $methodCount = count($data['methods']);
                    $this->info("   📱 Méthodes de paiement : $methodCount");
                }
                
            } else {
                $this->error("   ❌ Échec (Status: {$response->status()})");
                $this->line("   Réponse : " . $response->body());
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Exception : " . $e->getMessage());
        }

        $this->newLine();
    }

    private function displayStructure($data, $indent = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $count = count($value);
                    $this->line($indent . "- $key : array($count éléments)");
                } else {
                    $valueStr = is_bool($value) ? ($value ? 'true' : 'false') : 
                               (is_null($value) ? 'null' : $value);
                    $this->line($indent . "- $key : " . substr($valueStr, 0, 50));
                }
            }
        }
    }
}
