<?php

namespace App\Http\Controllers;

use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileController extends Controller
{
    /**
     * Servir un fichier de manière sécurisée
     * Route: /files/{type}/{path}
     *
     * @param  string  $type  Le type de fichier (thumbnails, previews, lessons, downloads, avatars, banners, package-thumbnails, package-covers, etc.)
     * @param  string  $path  Le chemin relatif du fichier
     * @return BinaryFileResponse|\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function serve(Request $request, string $type, string $path)
    {
        // Décoder le chemin si nécessaire
        $path = urldecode($path);

        // Construire le chemin complet
        $fullPath = $this->getFullPath($type, $path);

        $disk = Storage::disk('local');

        if (! $disk->exists($fullPath)) {
            $cleanPath = ltrim(preg_replace('#^storage/#', '', $path), '/');

            if ($disk->exists($cleanPath)) {
                $fullPath = $cleanPath;
            } else {
                \Log::error('File not found', [
                    'type' => $type,
                    'path' => $path,
                    'fullPath' => $fullPath,
                    'cleanPath' => $cleanPath,
                ]);
                abort(404, 'Fichier non trouvé: '.$fullPath);
            }
        }

        // Vérifier les permissions selon le type
        if (! $this->hasAccess($type, $fullPath)) {
            abort(403, 'Accès refusé');
        }

        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        // Playlists HLS (fichiers courts)
        if ($ext === 'm3u8') {
            return response()->file($disk->path($fullPath), [
                'Content-Type' => 'application/vnd.apple.mpegurl',
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'public, max-age=86400, no-transform',
            ]);
        }

        // Types HLS / vidéo : mime parfois « application/octet-stream » sur disque privé
        if ($ext === 'ts') {
            $mimeType = 'video/mp2t';
        } else {
            $mimeType = $disk->mimeType($fullPath);
            if (! $mimeType) {
                $mimeType = 'application/octet-stream';
            }
        }

        $mimeType = $this->resolveStreamableVideoMime($ext, $mimeType);

        // Vidéo : lecture progressive (Range / 206) — BinaryFileResponse gère les plages correctement
        if (str_starts_with($mimeType, 'video/')) {
            return $this->streamVideo($disk, $fullPath, $mimeType);
        }

        return response()->file($disk->path($fullPath), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Corrige le Content-Type quand finfo renvoie octet-stream pour un .mp4 / .webm, etc.
     * Sans video/* + Range fiables, le navigateur peut se comporter comme s’il devait tout charger.
     */
    protected function resolveStreamableVideoMime(string $extension, string $detectedMime): string
    {
        if (str_starts_with($detectedMime, 'video/')) {
            return $detectedMime;
        }

        $map = [
            'mp4' => 'video/mp4',
            'm4v' => 'video/x-m4v',
            'webm' => 'video/webm',
            'ogv' => 'video/ogg',
            'ogg' => 'video/ogg',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'avi' => 'video/x-msvideo',
            'ts' => 'video/mp2t',
        ];

        return $map[$extension] ?? $detectedMime;
    }

    /**
     * Obtenir le chemin complet selon le type
     */
    protected function getFullPath(string $type, string $path): string
    {
        $basePath = '';

        switch ($type) {
            case 'thumbnails':
                $basePath = 'courses/thumbnails';
                break;
            case 'previews':
                $basePath = 'courses/previews';
                break;
            case 'lessons':
                $basePath = 'courses/lessons';
                break;
            case 'downloads':
                $basePath = 'courses/downloads';
                break;
            case 'avatars':
                $basePath = 'avatars';
                break;
            case 'banners':
                $basePath = 'banners';
                break;
            case 'email-images':
                $basePath = 'email-images';
                break;
            case 'media':
                $basePath = 'media';
                break;
            case 'temporary':
                $basePath = FileUploadService::TEMPORARY_BASE_PATH;
                break;
            case 'package-thumbnails':
                $basePath = 'packages/thumbnails';
                break;
            case 'package-covers':
                $basePath = 'packages/covers';
                break;
            case 'community-home':
                $basePath = 'site/community-home';
                break;
            case 'announcements':
                $basePath = 'announcements';
                break;
            case 'richtext-images':
                $basePath = 'richtext/images';
                break;
            default:
                abort(400, 'Type de fichier non valide');
        }

        // Sécuriser le chemin pour éviter les traversées de répertoire
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');

        return $basePath.'/'.$path;
    }

    /**
     * Vérifier si l'utilisateur a accès au fichier
     */
    protected function hasAccess(string $type, string $path): bool
    {
        // Les avatars, banners, email-images et media sont publics (mais protégés par l'URL)
        if (in_array($type, ['avatars', 'banners', 'email-images', 'media', 'community-home', 'announcements', 'richtext-images'], true)) {
            return true;
        }

        if ($type === 'temporary') {
            return Auth::check();
        }

        // Les thumbnails de cours sont publics (pour l'affichage des listes)
        if ($type === 'thumbnails') {
            return true;
        }

        // Visuels des packs (catalogue public)
        if (in_array($type, ['package-thumbnails', 'package-covers'], true)) {
            return true;
        }

        // Les vidéos de prévisualisation doivent être accessibles publiquement
        if ($type === 'previews') {
            return true;
        }

        // Pour les leçons, vérifier l'inscription / connexion
        if ($type === 'lessons') {
            return Auth::check();
        }

        // Pour les téléchargements, vérifier l'inscription au cours
        if ($type === 'downloads') {
            return Auth::check();
        }

        return true;
    }

    /**
     * Sert une vidéo avec support HTTP Range (lecture progressive, seek, HLS .ts).
     * Utilise BinaryFileResponse : implémentation éprouvée (If-Range, 206, plages invalides).
     */
    protected function streamVideo($disk, string $path, string $mimeType): BinaryFileResponse
    {
        $filePath = $disk->path($path);

        if (! is_file($filePath) || ! is_readable($filePath)) {
            abort(404, 'Fichier non trouvé');
        }

        $response = response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400, no-transform',
        ]);

        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
