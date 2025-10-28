<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ConvertBannersToBase64 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banners:convert-to-base64';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convertir les bannières existantes avec chemins de fichiers vers le stockage base64';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Démarrage de la conversion des bannières...');
        $this->newLine();

        $banners = \App\Models\Banner::all();
        
        if ($banners->count() === 0) {
            $this->warn('Aucune bannière trouvée dans la base de données.');
            return 0;
        }

        $converted = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($banners->count());
        $bar->start();

        foreach ($banners as $banner) {
            $this->newLine();
            $this->info("Traitement de la bannière #{$banner->id}: {$banner->title}");
            
            // Convertir l'image principale
            if ($banner->image && !str_starts_with($banner->image, 'data:')) {
                $imagePath = public_path($banner->image);
                
                if (file_exists($imagePath)) {
                    try {
                        $imageData = file_get_contents($imagePath);
                        $mimeType = mime_content_type($imagePath);
                        $banner->image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        $converted++;
                        $this->line("  ✅ Image principale convertie: {$imagePath}");
                    } catch (\Exception $e) {
                        $this->error("  ❌ Erreur lors de la conversion: {$e->getMessage()}");
                        $errors++;
                    }
                } else {
                    $this->warn("  ⚠️  Fichier non trouvé: {$imagePath}");
                    $skipped++;
                }
            } elseif (str_starts_with($banner->image, 'data:')) {
                $this->line("  ℹ️  Image déjà en base64");
                $skipped++;
            }
            
            // Convertir l'image mobile
            if ($banner->mobile_image && !str_starts_with($banner->mobile_image, 'data:')) {
                $imagePath = public_path($banner->mobile_image);
                
                if (file_exists($imagePath)) {
                    try {
                        $imageData = file_get_contents($imagePath);
                        $mimeType = mime_content_type($imagePath);
                        $banner->mobile_image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        $converted++;
                        $this->line("  ✅ Image mobile convertie: {$imagePath}");
                    } catch (\Exception $e) {
                        $this->error("  ❌ Erreur lors de la conversion mobile: {$e->getMessage()}");
                        $errors++;
                    }
                }
            }
            
            // Sauvegarder
            try {
                $banner->save();
                $this->line("  💾 Bannière sauvegardée");
            } catch (\Exception $e) {
                $this->error("  ❌ Erreur lors de la sauvegarde: {$e->getMessage()}");
                $errors++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('🎉 Conversion terminée!');
        $this->newLine();
        $this->info('📊 Résumé:');
        $this->line("  • Total de bannières: {$banners->count()}");
        $this->line("  • Images converties: {$converted}");
        $this->line("  • Déjà en base64: {$skipped}");
        $this->line("  • Erreurs: {$errors}");
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return 0;
    }
}
