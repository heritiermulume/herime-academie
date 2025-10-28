<?php

namespace App\Console\Commands;

use App\Models\Banner;
use Illuminate\Console\Command;

class FixBannerUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banners:fix-urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige automatiquement les URLs de bannières mal formatées (ajoute https:// aux URLs externes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Vérification des URLs de bannières...');
        $this->newLine();
        
        $banners = Banner::all();
        $fixed = 0;
        
        foreach ($banners as $banner) {
            $modified = false;
            
            // Vérifier et corriger button1_url
            if ($banner->button1_url) {
                $fixedUrl = $this->fixUrl($banner->button1_url);
                if ($fixedUrl !== $banner->button1_url) {
                    $this->warn("Bannière #{$banner->id} - Bouton 1");
                    $this->line("  Avant: {$banner->button1_url}");
                    $this->line("  Après: {$fixedUrl}");
                    $banner->button1_url = $fixedUrl;
                    $modified = true;
                }
            }
            
            // Vérifier et corriger button2_url
            if ($banner->button2_url) {
                $fixedUrl = $this->fixUrl($banner->button2_url);
                if ($fixedUrl !== $banner->button2_url) {
                    $this->warn("Bannière #{$banner->id} - Bouton 2");
                    $this->line("  Avant: {$banner->button2_url}");
                    $this->line("  Après: {$fixedUrl}");
                    $banner->button2_url = $fixedUrl;
                    $modified = true;
                }
            }
            
            if ($modified) {
                $banner->save();
                $fixed++;
                $this->newLine();
            }
        }
        
        if ($fixed > 0) {
            $this->info("✅ {$fixed} bannière(s) corrigée(s) avec succès !");
        } else {
            $this->info("✅ Aucune correction nécessaire. Toutes les URLs sont correctes !");
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Corrige une URL si nécessaire
     */
    private function fixUrl(string $url): string
    {
        $url = trim($url);
        
        // Si l'URL est vide, on ne fait rien
        if (empty($url)) {
            return $url;
        }
        
        // Si c'est déjà une URL complète ou un chemin interne/ancre, on ne touche pas
        if (
            str_starts_with($url, 'http://') || 
            str_starts_with($url, 'https://') ||
            str_starts_with($url, '/') ||
            str_starts_with($url, '#')
        ) {
            return $url;
        }
        
        // Si l'URL contient un point (probablement un domaine), on ajoute https://
        if (str_contains($url, '.')) {
            return 'https://' . $url;
        }
        
        // Sinon, c'est probablement un chemin interne sans /, on ajoute /
        return '/' . $url;
    }
}
