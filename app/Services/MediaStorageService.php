<?php

namespace App\Services;

use App\Models\MediaFile;
use App\Models\MediaVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaStorageService
{
    /**
     * Upload un fichier et créer l'enregistrement MediaFile
     */
    public function upload(
        UploadedFile $file,
        string $mediaType,
        ?int $userId = null,
        ?string $entityType = null,
        ?int $entityId = null
    ): MediaFile {
        // Générer un ID unique
        $fileId = MediaFile::generateFileId();
        
        $bucket = $this->determineBucket($mediaType, $userId);
        $extension = $file->getClientOriginalExtension();
        $storagePath = "{$bucket}/{$fileId}/original.{$extension}";

        $disk = Storage::disk('local');
        $disk->makeDirectory("{$bucket}/{$fileId}");

        $stored = $file->storeAs("{$bucket}/{$fileId}", "original.{$extension}", ['disk' => 'local']);
        if (!$stored) {
            throw new \RuntimeException('Erreur lors de la sauvegarde du fichier média.');
        }
        
        // Calculer les checksums
        $absolutePath = $disk->path($storagePath);
        $md5 = md5_file($absolutePath);
        $sha256 = hash_file('sha256', $absolutePath);
        
        // Extraire les métadonnées selon le type
        $metadata = $this->extractMetadata($file, $mediaType);
        
        // Créer l'enregistrement
        $mediaFile = MediaFile::create([
            'file_id' => $fileId,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'media_type' => $mediaType,
            'size' => $file->getSize(),
            'storage_bucket' => $bucket,
            'storage_path' => $storagePath,
            'storage_driver' => 'local',
            'checksum_md5' => $md5,
            'checksum_sha256' => $sha256,
            'metadata' => $metadata,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => 'processing',
        ]);
        
        // Traiter le fichier de manière asynchrone selon le type
        $this->processMedia($mediaFile, $file);
        
        return $mediaFile;
    }

    /**
     * Déterminer le bucket de stockage
     */
    protected function determineBucket(string $mediaType, ?int $userId): string
    {
        $userFolder = $userId ? "user_{$userId}" : 'guest';
        return "media/{$mediaType}s/{$userFolder}";
    }

    /**
     * Extraire les métadonnées du fichier
     */
    protected function extractMetadata(UploadedFile $file, string $mediaType): array
    {
        $metadata = [];
        
        if ($mediaType === 'image') {
            try {
                $image = Image::make($file->getRealPath());
                $metadata['image'] = [
                    'width' => $image->width(),
                    'height' => $image->height(),
                    'ratio' => round($image->width() / $image->height(), 2),
                    'exif' => @exif_read_data($file->getRealPath()) ?: [],
                ];
            } catch (\Exception $e) {
                // Si l'extraction échoue, continuer sans métadonnées
            }
        }
        
        if ($mediaType === 'video') {
            // Pour FFmpeg, sera traité plus tard de manière asynchrone
            $metadata['video'] = [
                'processing_required' => true,
            ];
        }
        
        return $metadata;
    }

    /**
     * Traiter le fichier média
     */
    protected function processMedia(MediaFile $mediaFile, UploadedFile $file): void
    {
        try {
            if ($mediaFile->isImage()) {
                $this->processImage($mediaFile, $file);
            } elseif ($mediaFile->isVideo()) {
                // Pour les vidéos, on lance un job asynchrone
                // ProcessVideoJob::dispatch($mediaFile);
                
                // Pour l'instant, on marque comme prêt sans traitement
                $mediaFile->markAsReady();
            } else {
                $mediaFile->markAsReady();
            }
        } catch (\Exception $e) {
            $mediaFile->markAsFailed($e->getMessage());
        }
    }

    /**
     * Traiter une image (thumbnails, résolutions multiples)
     */
    protected function processImage(MediaFile $mediaFile, UploadedFile $file): void
    {
        $disk = Storage::disk('local');
        $basePath = dirname($mediaFile->storage_path);
        
        // Définir les tailles de variantes
        $variants = [
            'thumbnail' => ['width' => 300, 'height' => 300, 'fit' => 'crop'],
            'small' => ['width' => 640, 'height' => null, 'fit' => 'resize'],
            'medium' => ['width' => 1280, 'height' => null, 'fit' => 'resize'],
            'large' => ['width' => 1920, 'height' => null, 'fit' => 'resize'],
        ];
        
        $image = Image::make($file->getRealPath());
        $originalWidth = $image->width();
        $originalHeight = $image->height();
        
        foreach ($variants as $variantType => $config) {
            // Ne pas créer de variante plus grande que l'original
            if ($config['width'] && $config['width'] >= $originalWidth) {
                continue;
            }
            
            $variantImage = Image::make($file->getRealPath());
            
            if ($config['fit'] === 'crop') {
                $variantImage->fit($config['width'], $config['height']);
            } else {
                $variantImage->resize($config['width'], $config['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Sauvegarder la variante
            $variantPath = "{$basePath}/{$variantType}.jpg";
            $disk->makeDirectory($basePath);

            $variantFullPath = $disk->path($variantPath);
            $variantImage->save($variantFullPath, 85);
            
            // Créer l'enregistrement de variante
            MediaVariant::create([
                'media_file_id' => $mediaFile->id,
                'variant_type' => $variantType,
                'format' => 'jpg',
                'storage_path' => $variantPath,
                'size' => filesize($variantFullPath),
                'width' => $variantImage->width(),
                'height' => $variantImage->height(),
                'status' => 'ready',
            ]);
        }
        
        $mediaFile->markAsReady();
    }

    /**
     * Supprimer un fichier média et toutes ses variantes
     */
    public function delete(MediaFile $mediaFile): bool
    {
        $disk = Storage::disk('local');
        
        try {
            // Supprimer le fichier original
            $originalPath = $mediaFile->storage_path;
            if ($disk->exists($originalPath)) {
                $disk->delete($originalPath);
            }
            
            // Supprimer toutes les variantes
            foreach ($mediaFile->variants as $variant) {
                $variantPath = $variant->storage_path;
                if ($disk->exists($variantPath)) {
                    $disk->delete($variantPath);
                }
            }
            
            // Supprimer le dossier du fichier
            $folder = dirname($originalPath);
            $disk->deleteDirectory($folder);
            
            // Supprimer l'enregistrement (soft delete)
            $mediaFile->delete();
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du fichier média: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir l'URL optimale pour affichage
     */
    public function getOptimalUrl(MediaFile $mediaFile, string $size = 'medium'): string
    {
        if ($mediaFile->isImage()) {
            $variant = $mediaFile->variants()->where('variant_type', $size)->first();
            if ($variant) {
                return $variant->url;
            }
        }
        
        return $mediaFile->url;
    }

    /**
     * Associer un fichier média à une entité
     */
    public function attachToEntity(MediaFile $mediaFile, string $entityType, int $entityId): void
    {
        $mediaFile->update([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }

    /**
     * Obtenir tous les fichiers d'une entité
     */
    public function getEntityMedia(string $entityType, int $entityId, ?string $mediaType = null)
    {
        $query = MediaFile::forEntity($entityType, $entityId)->ready();
        
        if ($mediaType) {
            $query->where('media_type', $mediaType);
        }
        
        return $query->get();
    }
}

