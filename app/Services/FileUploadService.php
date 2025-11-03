<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    /**
     * Upload un fichier de manière optimisée
     * 
     * @param UploadedFile $file Le fichier à uploader
     * @param string $folder Le dossier de destination (ex: 'courses/thumbnails')
     * @param string|null $oldPath Le chemin de l'ancien fichier à supprimer (optionnel)
     * @return array ['path' => string, 'url' => string]
     */
    public function upload(UploadedFile $file, string $folder, ?string $oldPath = null): array
    {
        $disk = Storage::disk('local'); // Utiliser le disque privé
        
        // Supprimer l'ancien fichier si fourni
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }
        
        // Générer un nom de fichier unique
        $filename = $this->generateUniqueFilename($file);
        
        // Construire le chemin complet
        $path = rtrim($folder, '/') . '/' . $filename;
        
        // S'assurer que le dossier existe
        $this->ensureDirectoryExists($disk, $folder);
        
        // Vérifier que le fichier temporaire existe et est lisible
        $tempPath = $file->getRealPath();
        if (!$tempPath || !file_exists($tempPath)) {
            throw new \Exception('Le fichier temporaire n\'existe pas ou n\'est pas accessible.');
        }
        
        // Vérifier que le fichier n'est pas vide
        if ($file->getSize() === 0) {
            throw new \Exception('Le fichier est vide.');
        }
        
        // Lire le contenu du fichier
        $content = file_get_contents($tempPath);
        if ($content === false) {
            throw new \Exception('Impossible de lire le fichier uploadé.');
        }
        
        // Stocker le fichier en utilisant put() avec le contenu
        $stored = $disk->put($path, $content);
        
        if (!$stored) {
            throw new \Exception('Impossible d\'écrire le fichier sur le disque. Vérifiez les permissions du dossier de stockage.');
        }
        
        // Retourner le chemin relatif et l'URL sécurisée via le FileController
        return [
            'path' => $path,
            'url' => $this->getSecureUrl($path, $folder),
        ];
    }

    /**
     * Upload une image avec optimisation automatique
     * 
     * @param UploadedFile $file L'image à uploader
     * @param string $folder Le dossier de destination
     * @param string|null $oldPath Le chemin de l'ancien fichier à supprimer
     * @param int|null $maxWidth Largeur maximale (null = pas de redimensionnement)
     * @param int $quality Qualité JPEG (1-100)
     * @return array ['path' => string, 'url' => string]
     */
    public function uploadImage(
        UploadedFile $file, 
        string $folder, 
        ?string $oldPath = null,
        ?int $maxWidth = null,
        int $quality = 85
    ): array {
        $disk = Storage::disk('local'); // Utiliser le disque privé
        
        // Supprimer l'ancien fichier si fourni
        if ($oldPath) {
            $this->deleteFile($oldPath);
        }
        
        // Générer un nom de fichier unique
        $filename = $this->generateUniqueFilename($file);
        
        // Construire le chemin complet
        $path = rtrim($folder, '/') . '/' . $filename;
        $fullPath = $disk->path($path);
        
        // S'assurer que le dossier existe
        $this->ensureDirectoryExists($disk, $folder);
        
        try {
            // Si redimensionnement requis, utiliser Intervention Image
            if ($maxWidth && $this->isImage($file)) {
                $image = Image::make($file->getRealPath());
                
                // Redimensionner si nécessaire
                if ($image->width() > $maxWidth) {
                    $image->resize($maxWidth, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                
                // Sauvegarder l'image optimisée
                $image->save($fullPath, $quality);
            } else {
                // Sinon, copier le fichier tel quel
                $disk->put($path, file_get_contents($file->getRealPath()));
            }
        } catch (\Exception $e) {
            // En cas d'erreur avec Intervention Image, fallback vers upload normal
            \Log::warning("Erreur lors de l'optimisation de l'image: " . $e->getMessage());
            $disk->put($path, file_get_contents($file->getRealPath()));
        }
        
        return [
            'path' => $path,
            'url' => $this->getSecureUrl($path, $folder),
        ];
    }

    /**
     * Upload une vidéo
     * 
     * @param UploadedFile $file La vidéo à uploader
     * @param string $folder Le dossier de destination
     * @param string|null $oldPath Le chemin de l'ancien fichier à supprimer
     * @return array ['path' => string, 'url' => string]
     */
    public function uploadVideo(UploadedFile $file, string $folder, ?string $oldPath = null): array
    {
        return $this->upload($file, $folder, $oldPath);
    }

    /**
     * Upload un document
     * 
     * @param UploadedFile $file Le document à uploader
     * @param string $folder Le dossier de destination
     * @param string|null $oldPath Le chemin de l'ancien fichier à supprimer
     * @return array ['path' => string, 'url' => string]
     */
    public function uploadDocument(UploadedFile $file, string $folder, ?string $oldPath = null): array
    {
        return $this->upload($file, $folder, $oldPath);
    }

    /**
     * Supprimer un fichier
     * 
     * @param string $path Le chemin du fichier à supprimer
     * @return bool
     */
    public function deleteFile(string $path): bool
    {
        // Ignorer si c'est une URL externe
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        $disk = Storage::disk('local'); // Utiliser le disque privé
        
        // Supprimer le préfixe 'storage/' si présent
        $cleanPath = str_replace('storage/', '', $path);
        
        if ($disk->exists($cleanPath)) {
            return $disk->delete($cleanPath);
        }
        
        // Essayer avec le chemin tel quel
        if ($disk->exists($path)) {
            return $disk->delete($path);
        }
        
        return false;
    }

    /**
     * Générer un nom de fichier unique
     * 
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = Str::slug($originalName);
        
        // Nom unique: timestamp_hash_sanitizedname.extension
        $hash = Str::random(8);
        $timestamp = now()->format('YmdHis');
        
        return sprintf('%s_%s_%s.%s', $timestamp, $hash, $sanitizedName, $extension);
    }

    /**
     * S'assurer que le dossier existe
     * 
     * @param \Illuminate\Contracts\Filesystem\Filesystem $disk
     * @param string $folder
     * @return void
     */
    protected function ensureDirectoryExists($disk, string $folder): void
    {
        $path = $disk->path($folder);
        
        if (!file_exists($path)) {
            $created = @mkdir($path, 0755, true);
            if (!$created) {
                throw new \Exception("Impossible de créer le dossier de stockage : {$path}. Vérifiez les permissions.");
            }
        }
        
        // Vérifier que le dossier est accessible en écriture
        if (!is_writable($path)) {
            throw new \Exception("Le dossier de stockage n'est pas accessible en écriture : {$path}. Vérifiez les permissions.");
        }
    }

    /**
     * Vérifier si le fichier est une image
     * 
     * @param UploadedFile $file
     * @return bool
     */
    protected function isImage(UploadedFile $file): bool
    {
        return strpos($file->getMimeType(), 'image/') === 0;
    }

    /**
     * Obtenir l'URL d'un fichier (sécurisée via FileController)
     * 
     * @param string $path Le chemin relatif du fichier
     * @param string|null $folder Le dossier (optionnel, déduit depuis le chemin si non fourni)
     * @return string
     */
    public function getUrl(string $path, ?string $folder = null): string
    {
        // Si c'est déjà une URL, la retourner tel quel
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        return $this->getSecureUrl($path, $folder);
    }

    /**
     * Obtenir l'URL sécurisée via le FileController
     * 
     * @param string $path Le chemin du fichier
     * @param string|null $folder Le dossier (déduit si non fourni)
     * @return string
     */
    protected function getSecureUrl(string $path, ?string $folder = null): string
    {
        // Nettoyer le chemin
        $cleanPath = str_replace('storage/', '', $path);
        
        // Déterminer le type de fichier depuis le dossier
        if (!$folder) {
            // Essayer de déduire depuis le chemin
            if (strpos($cleanPath, 'courses/thumbnails') === 0) {
                $type = 'thumbnails';
            } elseif (strpos($cleanPath, 'courses/previews') === 0) {
                $type = 'previews';
            } elseif (strpos($cleanPath, 'courses/lessons') === 0) {
                $type = 'lessons';
            } elseif (strpos($cleanPath, 'courses/downloads') === 0) {
                $type = 'downloads';
            } elseif (strpos($cleanPath, 'avatars') === 0) {
                $type = 'avatars';
            } elseif (strpos($cleanPath, 'banners') === 0) {
                $type = 'banners';
            } else {
                $type = 'files'; // Type par défaut
            }
        } else {
            // Déterminer le type depuis le folder
            $type = $this->getTypeFromFolder($folder);
        }
        
        // Extraire le nom du fichier du chemin complet
        $filename = basename($cleanPath);
        
        // Retourner l'URL de la route sécurisée
        return route('files.serve', ['type' => $type, 'path' => $filename]);
    }

    /**
     * Déterminer le type de fichier depuis le dossier
     */
    protected function getTypeFromFolder(string $folder): string
    {
        if (strpos($folder, 'courses/thumbnails') !== false) {
            return 'thumbnails';
        } elseif (strpos($folder, 'courses/previews') !== false) {
            return 'previews';
        } elseif (strpos($folder, 'courses/lessons') !== false) {
            return 'lessons';
        } elseif (strpos($folder, 'courses/downloads') !== false) {
            return 'downloads';
        } elseif (strpos($folder, 'avatars') !== false) {
            return 'avatars';
        } elseif (strpos($folder, 'banners') !== false) {
            return 'banners';
        }
        
        return 'files';
    }
}

