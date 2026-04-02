<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Servir un fichier de manière sécurisée
     * Route: /files/{type}/{path}
     * 
     * @param Request $request
     * @param string $type Le type de fichier (thumbnails, previews, lessons, downloads, avatars, banners, package-thumbnails, package-covers, etc.)
     * @param string $path Le chemin relatif du fichier
     * @return StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function serve(Request $request, string $type, string $path)
    {
        // Décoder le chemin si nécessaire
        $path = urldecode($path);
        
        // Construire le chemin complet
        $fullPath = $this->getFullPath($type, $path);
        
        $disk = Storage::disk('local');

        if (!$disk->exists($fullPath)) {
            $cleanPath = ltrim(preg_replace('#^storage/#', '', $path), '/');

            if ($disk->exists($cleanPath)) {
                $fullPath = $cleanPath;
            } else {
                \Log::error("File not found", [
                    'type' => $type,
                    'path' => $path,
                    'fullPath' => $fullPath,
                    'cleanPath' => $cleanPath
                ]);
                abort(404, 'Fichier non trouvé: ' . $fullPath);
            }
        }
        
        // Vérifier les permissions selon le type
        if (!$this->hasAccess($type, $fullPath)) {
            abort(403, 'Accès refusé');
        }
        
        // Types HLS / segments (mime parfois mal détecté sur le disque privé)
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($ext === 'm3u8') {
            $mimeType = 'application/vnd.apple.mpegurl';
        } elseif ($ext === 'ts') {
            $mimeType = 'video/mp2t';
        } else {
            $mimeType = $disk->mimeType($fullPath);
            if (! $mimeType) {
                $mimeType = 'application/octet-stream';
            }
        }

        // Pour les vidéos et segments TS, lecture avec support Range
        if (strpos($mimeType, 'video/') === 0) {
            return $this->streamVideo($disk, $fullPath, $mimeType);
        }

        // Playlists HLS (fichiers courts, pas besoin de Range)
        if ($ext === 'm3u8') {
            return response()->file($disk->path($fullPath), [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }

        return response()->file($disk->path($fullPath), [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
        ]);
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
            default:
                abort(400, 'Type de fichier non valide');
        }
        
        // Sécuriser le chemin pour éviter les traversées de répertoire
        $path = str_replace('..', '', $path);
        $path = ltrim($path, '/');
        
        return $basePath . '/' . $path;
    }

    /**
     * Vérifier si l'utilisateur a accès au fichier
     */
    protected function hasAccess(string $type, string $path): bool
    {
        // Les avatars, banners, email-images et media sont publics (mais protégés par l'URL)
        if (in_array($type, ['avatars', 'banners', 'email-images', 'media', 'community-home'], true)) {
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
     * Streamer une vidéo avec support Range pour la lecture
     * - Taille des lectures fichier : config video.stream_chunk_bytes (défaut 512 Ko)
     * - Cache navigateur pour éviter re-téléchargements
     */
    protected function streamVideo($disk, string $path, string $mimeType): StreamedResponse
    {
        $filePath = $disk->path($path);
        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;

        // Taille de lecture configurable (défaut config/video.php, mutualisé ~256 Ko)
        $chunkSize = (int) config('video.stream_chunk_bytes', 262144);
        $chunkSize = max(65536, min($chunkSize, 8 * 1024 * 1024));

        $isRangeRequest = false;
        if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
            $isRangeRequest = true;
            $start = (int) $matches[1];
            if (!empty($matches[2])) {
                $end = (int) $matches[2];
            }
        }

        $length = $end - $start + 1;
        $statusCode = $isRangeRequest ? 206 : 200;

        $response = new StreamedResponse(function () use ($filePath, $start, $length, $chunkSize) {
            $file = fopen($filePath, 'rb');
            fseek($file, $start);
            $remaining = $length;

            while ($remaining > 0) {
                $readSize = min($chunkSize, $remaining);
                echo fread($file, $readSize);
                $remaining -= $readSize;
                flush();
            }

            fclose($file);
        }, $statusCode);

        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', (string) $length);
        $response->headers->set('Accept-Ranges', 'bytes');
        if ($isRangeRequest) {
            $response->headers->set('Content-Range', "bytes $start-$end/$fileSize");
        }

        // Cache agressif pour vidéos (contenu stable)
        // public + max-age permet au navigateur de garder les segments en cache
        $response->headers->set('Cache-Control', 'public, max-age=86400');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');
        return $response;
    }
}

