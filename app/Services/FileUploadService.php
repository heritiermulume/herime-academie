<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public const TEMPORARY_BASE_PATH = 'tmp/uploads';

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
        $disk = Storage::disk('local'); // Disque privé (storage/app/private)

        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

        $filename = $this->generateUniqueFilename($file);
        $folder = trim($folder, '/');

        $this->ensureDirectoryExists($disk, $folder);

        $storedPath = $file->storeAs($folder, $filename, ['disk' => 'local']);

        if (!$storedPath) {
            throw new \Exception('Impossible d\'écrire le fichier sur le disque. Vérifiez les permissions du dossier de stockage.');
        }

        return [
            'path' => $storedPath,
            'url' => $this->getSecureUrl($storedPath, $folder),
        ];
    }

    /**
     * Upload un fichier dans le dossier temporaire
     *
     * @param UploadedFile $file
     * @param string $folder Dossier cible final (ex: courses/lessons)
     * @return array ['path' => string, 'url' => string]
     */
    public function uploadTemporary(UploadedFile $file, string $folder): array
    {
        $disk = Storage::disk('local');

        $filename = $this->generateUniqueFilename($file);
        $folder = trim($folder, '/');
        $temporaryFolder = $this->getTemporaryFolder($folder);

        $this->ensureDirectoryExists($disk, $temporaryFolder);

        $storedPath = $file->storeAs($temporaryFolder, $filename, ['disk' => 'local']);

        if (!$storedPath) {
            throw new \Exception('Impossible d\'écrire le fichier temporaire sur le disque.');
        }

        return [
            'path' => $storedPath,
            'url' => $this->getTemporaryUrl($storedPath),
        ];
    }

    /**
     * Promouvoir un fichier temporaire vers son dossier final
     *
     * @param string $path Chemin du fichier temporaire ou final
     * @param string $finalFolder Dossier final (ex: courses/lessons)
     * @return string Chemin final du fichier
     */
    public function promoteTemporaryFile(string $path, string $finalFolder): string
    {
        $disk = Storage::disk('local');
        $cleanPath = $this->sanitizePath($path);

        if (!$this->isTemporaryPath($cleanPath)) {
            // Le fichier est déjà dans un dossier final
            return $cleanPath;
        }

        if (!$disk->exists($cleanPath)) {
            throw new \RuntimeException("Fichier temporaire introuvable: {$cleanPath}");
        }

        $finalFolder = trim($finalFolder, '/');
        $this->ensureDirectoryExists($disk, $finalFolder);

        $filename = basename($cleanPath);
        $destinationPath = $finalFolder . '/' . $filename;

        // En cas de collision improbable, générer un nouveau nom
        if ($disk->exists($destinationPath)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $extension = pathinfo($filename, PATHINFO_EXTENSION);

            do {
                $candidate = $name . '_' . Str::random(6);
                if ($extension) {
                    $candidate .= '.' . $extension;
                }
                $destinationPath = $finalFolder . '/' . $candidate;
            } while ($disk->exists($destinationPath));
        }

        $moved = $disk->move($cleanPath, $destinationPath);

        if (!$moved) {
            throw new \RuntimeException("Impossible de déplacer le fichier vers {$destinationPath}");
        }

        // Nettoyer les dossiers temporaires vides
        $this->cleanupEmptyTemporaryDirectories(dirname($cleanPath));

        return $destinationPath;
    }

    /**
     * Déterminer si un chemin correspond au dossier temporaire
     */
    public function isTemporaryPath(string $path): bool
    {
        $clean = ltrim($path, '/');
        return str_starts_with($clean, self::TEMPORARY_BASE_PATH . '/');
    }

    /**
     * Supprimer un fichier temporaire (avec nettoyage des dossiers)
     */
    public function deleteTemporaryFile(string $path): bool
    {
        $clean = $this->sanitizePath($path);

        if (!$this->isTemporaryPath($clean)) {
            return false;
        }

        $deleted = $this->deleteFile($clean);

        if ($deleted) {
            $this->cleanupEmptyTemporaryDirectories(dirname($clean));
        }

        return $deleted;
    }

    /**
     * Retourner le dossier temporaire pour un dossier final donné
     */
    public function getTemporaryFolder(string $folder): string
    {
        $folder = trim($folder, '/');

        return self::TEMPORARY_BASE_PATH . '/' . $folder;
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
            if (
                $maxWidth &&
                $this->isImage($file) &&
                class_exists(\Intervention\Image\Facades\Image::class)
            ) {
                $image = \Intervention\Image\Facades\Image::make($file->getRealPath());

                if ($image->width() > $maxWidth) {
                    $image->resize($maxWidth, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }

                $image->save($fullPath, $quality);
            } else {
                // Sinon, stocker directement via le disque (supporte les gros fichiers)
                $storedPath = $file->storeAs($folder, $filename, ['disk' => 'local']);
                if (!$storedPath) {
                    throw new \Exception("Impossible d'enregistrer le fichier dans {$folder}");
                }
            }
        } catch (\Exception $e) {
            // En cas d'erreur avec Intervention Image, fallback vers upload normal
            \Log::warning("Erreur lors de l'optimisation de l'image: " . $e->getMessage());
            $storedPath = $file->storeAs($folder, $filename, ['disk' => 'local']);
            if (!$storedPath) {
                throw new \Exception("Impossible d'enregistrer le fichier dans {$folder}");
            }
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

        $disk = Storage::disk('local');

        $cleanPath = ltrim(preg_replace('#^storage/#', '', $path), '/');

        if ($disk->exists($cleanPath)) {
            return $disk->delete($cleanPath);
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
        if (!$disk->exists($folder)) {
            try {
                $created = $disk->makeDirectory($folder, 0755, true);
                if (!$created) {
                    $fullPath = $disk->path($folder);
                    throw new \RuntimeException(
                        "Impossible de créer le dossier de stockage : {$folder}. " .
                        "Chemin complet : {$fullPath}. " .
                        "Vérifiez les permissions du dossier parent."
                    );
                }
            } catch (\Exception $e) {
                $fullPath = $disk->path($folder);
                \Log::error('Failed to create directory', [
                    'folder' => $folder,
                    'full_path' => $fullPath,
                    'error' => $e->getMessage(),
                ]);
                throw new \RuntimeException(
                    "Erreur lors de la création du dossier : {$folder}. " .
                    "Vérifiez les permissions. Détails : " . $e->getMessage()
                );
            }
        }

        // Vérifier que le dossier est accessible en écriture
        $fullPath = $disk->path($folder);
        if (!is_writable($fullPath)) {
            throw new \RuntimeException(
                "Le dossier de stockage n'est pas accessible en écriture : {$folder}. " .
                "Chemin complet : {$fullPath}. " .
                "Vérifiez les permissions (doit être 755 ou 775)."
            );
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
        $cleanPath = ltrim($path, '/');
        
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
            } elseif (strpos($cleanPath, 'email-images') === 0 || strpos($cleanPath, 'email_images') === 0) {
                $type = 'email-images';
            } elseif (strpos($cleanPath, 'media/') === 0) {
                $type = 'media';
            } else {
                $type = 'files'; // Type par défaut
            }
        } else {
            // Déterminer le type depuis le folder
            $type = $this->getTypeFromFolder($folder);
        }
        
        $baseDir = $this->getBaseDirectoryForType($type);
        $relativePath = $cleanPath;

        if ($baseDir && str_starts_with($cleanPath, $baseDir)) {
            $relativePath = ltrim(substr($cleanPath, strlen($baseDir)), '/');
        }

        $relativePath = $relativePath ?: basename($cleanPath);

        return route('files.serve', ['type' => $type, 'path' => $relativePath]);
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
        } elseif (strpos($folder, 'email-images') !== false || strpos($folder, 'email_images') !== false) {
            return 'email-images';
        } elseif (strpos($folder, 'media/') !== false) {
            return 'media';
        } elseif (strpos($folder, self::TEMPORARY_BASE_PATH) !== false) {
            return 'temporary';
        }
        
        return 'files';
    }

    protected function getBaseDirectoryForType(string $type): ?string
    {
        return match ($type) {
            'thumbnails' => 'courses/thumbnails',
            'previews' => 'courses/previews',
            'lessons' => 'courses/lessons',
            'downloads' => 'courses/downloads',
            'avatars' => 'avatars',
            'banners' => 'banners',
            'email-images' => 'email-images',
            'media' => 'media',
            'temporary' => self::TEMPORARY_BASE_PATH,
            default => null,
        };
    }

    /**
     * Générer une URL sécurisée pour un fichier temporaire
     */
    protected function getTemporaryUrl(string $path): string
    {
        $relativePath = ltrim($path, '/');

        if (!$this->isTemporaryPath($relativePath)) {
            return $this->getSecureUrl($relativePath);
        }

        $temporaryRoot = self::TEMPORARY_BASE_PATH . '/';
        $trimmed = str_starts_with($relativePath, $temporaryRoot)
            ? substr($relativePath, strlen($temporaryRoot))
            : $relativePath;

        return route('files.serve', ['type' => 'temporary', 'path' => $trimmed]);
    }

    /**
     * Nettoyer les dossiers temporaires vides
     */
    protected function cleanupEmptyTemporaryDirectories(string $path): void
    {
        $disk = Storage::disk('local');
        $path = trim($path, '/');

        while ($path && $path !== self::TEMPORARY_BASE_PATH) {
            if ($disk->exists($path) && empty($disk->files($path)) && empty($disk->directories($path))) {
                $disk->deleteDirectory($path);
                $path = dirname($path);
            } else {
                break;
            }
        }
    }

    /**
     * Nettoyer un chemin donné
     */
    public function sanitizePath(string $path): string
    {
        $clean = trim($path);
        $clean = ltrim($clean, '/');

        if (str_starts_with($clean, 'storage/')) {
            $clean = ltrim(substr($clean, strlen('storage/')), '/');
        }

        return $clean;
    }
}

