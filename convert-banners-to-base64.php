<?php
/**
 * Script de conversion des bannières existantes vers le stockage base64
 * 
 * Usage: php artisan tinker < convert-banners-to-base64.php
 */

use App\Models\Banner;

echo "🔄 Démarrage de la conversion des bannières...\n\n";

$banners = Banner::all();
$converted = 0;
$skipped = 0;
$errors = 0;

foreach ($banners as $banner) {
    echo "Traitement de la bannière #{$banner->id}: {$banner->title}\n";
    
    // Convertir l'image principale
    if ($banner->image && !str_starts_with($banner->image, 'data:')) {
        $imagePath = public_path($banner->image);
        
        if (file_exists($imagePath)) {
            try {
                $imageData = file_get_contents($imagePath);
                $mimeType = mime_content_type($imagePath);
                $banner->image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                $converted++;
                echo "  ✅ Image principale convertie: {$imagePath}\n";
            } catch (\Exception $e) {
                echo "  ❌ Erreur lors de la conversion de l'image: {$e->getMessage()}\n";
                $errors++;
            }
        } else {
            echo "  ⚠️  Fichier non trouvé: {$imagePath}\n";
            $skipped++;
        }
    } elseif (str_starts_with($banner->image, 'data:')) {
        echo "  ℹ️  Image déjà en base64\n";
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
                echo "  ✅ Image mobile convertie: {$imagePath}\n";
            } catch (\Exception $e) {
                echo "  ❌ Erreur lors de la conversion de l'image mobile: {$e->getMessage()}\n";
                $errors++;
            }
        } else {
            echo "  ⚠️  Fichier mobile non trouvé: {$imagePath}\n";
        }
    } elseif ($banner->mobile_image && str_starts_with($banner->mobile_image, 'data:')) {
        echo "  ℹ️  Image mobile déjà en base64\n";
    }
    
    // Sauvegarder les modifications
    try {
        $banner->save();
        echo "  💾 Bannière sauvegardée\n";
    } catch (\Exception $e) {
        echo "  ❌ Erreur lors de la sauvegarde: {$e->getMessage()}\n";
        $errors++;
    }
    
    echo "\n";
}

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "🎉 Conversion terminée!\n\n";
echo "📊 Résumé:\n";
echo "  • Total de bannières: {$banners->count()}\n";
echo "  • Images converties: {$converted}\n";
echo "  • Déjà en base64: {$skipped}\n";
echo "  • Erreurs: {$errors}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

