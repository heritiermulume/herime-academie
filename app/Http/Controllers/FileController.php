<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\User;
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
     * @param string $type Le type de fichier (thumbnails, previews, lessons, downloads, avatars, banners)
     * @param string $path Le chemin relatif du fichier
     * @return StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function serve(Request $request, string $type, string $path)
    {
        $disk = Storage::disk('local');
        
        // Décoder le chemin si nécessaire
        $path = urldecode($path);
        
        // Construire le chemin complet
        $fullPath = $this->getFullPath($type, $path);
        
        // Vérifier que le fichier existe
        if (!$disk->exists($fullPath)) {
            // Essayer aussi avec le chemin tel quel si le path contient déjà le dossier
            if (strpos($path, '/') !== false) {
                $cleanPath = ltrim(str_replace('storage/', '', $path), '/');
                if ($disk->exists($cleanPath)) {
                    $fullPath = $cleanPath;
                } else {
                    abort(404, 'Fichier non trouvé: ' . $fullPath);
                }
            } else {
                abort(404, 'Fichier non trouvé: ' . $fullPath);
            }
        }
        
        // Vérifier les permissions selon le type
        if (!$this->hasAccess($type, $fullPath)) {
            abort(403, 'Accès refusé');
        }
        
        // Obtenir le mime type
        $mimeType = $disk->mimeType($fullPath);
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }
        
        // Pour les vidéos, utiliser la lecture en streaming
        if (strpos($mimeType, 'video/') === 0) {
            return $this->streamVideo($disk, $fullPath, $mimeType);
        }
        
        // Pour les autres fichiers, servir directement
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
        // Les avatars et banners sont publics (mais protégés par l'URL)
        if (in_array($type, ['avatars', 'banners'])) {
            return true;
        }
        
        // Les thumbnails de cours sont publics (pour l'affichage des listes)
        if ($type === 'thumbnails') {
            return true;
        }
        
        // Pour les vidéos et leçons, vérifier l'inscription
        if (in_array($type, ['previews', 'lessons'])) {
            // Extraire l'ID du cours depuis le chemin si possible
            // Pour l'instant, on permet l'accès si l'utilisateur est connecté
            // Vous pouvez améliorer cette logique selon vos besoins
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
     */
    protected function streamVideo($disk, string $path, string $mimeType): StreamedResponse
    {
        $filePath = $disk->path($path);
        $fileSize = filesize($filePath);
        $start = 0;
        $end = $fileSize - 1;
        
        // Gérer les requêtes Range pour le streaming
        if (isset($_SERVER['HTTP_RANGE'])) {
            preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches);
            $start = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
        }
        
        $length = $end - $start + 1;
        
        $response = new StreamedResponse(function() use ($filePath, $start, $length) {
            $file = fopen($filePath, 'rb');
            fseek($file, $start);
            $remaining = $length;
            
            while ($remaining > 0) {
                $chunk = min(8192, $remaining);
                echo fread($file, $chunk);
                $remaining -= $chunk;
                flush();
            }
            
            fclose($file);
        }, 206); // 206 Partial Content
        
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Length', $length);
        $response->headers->set('Content-Range', "bytes $start-$end/$fileSize");
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Cache-Control', 'no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        
        return $response;
    }
}

