<?php

namespace App\Helpers;

use App\Services\FileUploadService;
use Illuminate\Support\Facades\Storage;

class FileHelper
{
    /**
     * Obtenir l'URL d'un fichier de manière sécurisée
     * 
     * @param string|null $path Le chemin du fichier
     * @param string|null $folder Le dossier (optionnel, déduit depuis le chemin)
     * @return string
     */
    public static function url(?string $path, ?string $folder = null): string
    {
        // Si pas de chemin, retourner une image par défaut ou chaîne vide
        if (empty($path)) {
            return '';
        }

        // Si c'est déjà une URL externe, la retourner tel quel
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        // Si c'est une URL complète commençant par http/https, la retourner
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Utiliser le FileUploadService pour obtenir l'URL sécurisée
        $service = app(FileUploadService::class);
        return $service->getUrl($path, $folder);
    }

    /**
     * Obtenir l'URL d'une image de cours (thumbnail)
     */
    public static function courseThumbnail(?string $path): string
    {
        return self::url($path, 'courses/thumbnails');
    }

    /**
     * Obtenir l'URL d'une vidéo de prévisualisation
     */
    public static function coursePreview(?string $path): string
    {
        return self::url($path, 'courses/previews');
    }

    /**
     * Obtenir l'URL d'un fichier de leçon
     */
    public static function lessonFile(?string $path): string
    {
        return self::url($path, 'courses/lessons');
    }

    /**
     * Obtenir l'URL d'un avatar
     */
    public static function avatar(?string $path): string
    {
        return self::url($path, 'avatars');
    }

    /**
     * Obtenir l'URL d'une bannière
     */
    public static function banner(?string $path): string
    {
        // Pour les bannières, vérifier si c'est une URL externe ou base64
        if (empty($path)) {
            return '';
        }

        if (str_starts_with($path, 'data:') || str_starts_with($path, 'http')) {
            return $path;
        }

        // Si le chemin commence par storage/, c'est un ancien chemin public
        if (str_starts_with($path, 'storage/')) {
            // Essayer de trouver le fichier dans le stockage privé
            $cleanPath = str_replace('storage/', '', $path);
            return self::url($cleanPath, 'banners');
        }

        return self::url($path, 'banners');
    }

    /**
     * Vérifier si un fichier existe dans le stockage privé
     */
    public static function exists(string $path): bool
    {
        $disk = Storage::disk('local');
        
        // Nettoyer le chemin
        $cleanPath = str_replace('storage/', '', $path);
        
        return $disk->exists($cleanPath);
    }
}

