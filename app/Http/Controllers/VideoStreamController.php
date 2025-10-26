<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VideoStreamController extends Controller
{
    public function stream(Request $request, $lessonId)
    {
        $lesson = CourseLesson::findOrFail($lessonId);
        $user = auth()->user();

        // Vérifier si l'utilisateur est inscrit au cours
        if (!$user || !$lesson->course->isEnrolledBy($user->id)) {
            abort(403, 'Accès non autorisé à cette leçon.');
        }

        // Vérifier que c'est une leçon vidéo
        if ($lesson->type !== 'video') {
            abort(404, 'Cette leçon n\'est pas une vidéo.');
        }

        $videoPath = storage_path('app/public/' . $lesson->content_url);
        
        if (!File::exists($videoPath)) {
            abort(404, 'Fichier vidéo non trouvé.');
        }

        return $this->streamVideo($videoPath, $request);
    }

    private function streamVideo($filePath, Request $request)
    {
        $fileSize = filesize($filePath);
        $file = fopen($filePath, 'rb');
        
        $start = 0;
        $end = $fileSize - 1;
        
        // Gérer les requêtes de plage (Range requests)
        if ($request->hasHeader('Range')) {
            $range = $request->header('Range');
            $ranges = explode('=', $range);
            $offsets = explode('-', $ranges[1]);
            
            $start = intval($offsets[0]);
            $end = isset($offsets[1]) && is_numeric($offsets[1]) ? intval($offsets[1]) : $fileSize - 1;
        }

        $length = $end - $start + 1;
        
        fseek($file, $start);
        
        $response = new Response();
        $response->headers->set('Content-Type', 'video/mp4');
        $response->headers->set('Content-Length', $length);
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Content-Range', "bytes $start-$end/$fileSize");
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        // Empêcher le téléchargement
        $response->headers->set('Content-Disposition', 'inline');
        $response->headers->set('X-Content-Disposition', 'inline');
        
        // Headers pour empêcher les captures d'écran
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        if ($request->hasHeader('Range')) {
            $response->setStatusCode(206);
        }

        $response->setCallback(function() use ($file, $length) {
            $buffer = 1024 * 8; // 8KB buffer
            $bytesRead = 0;
            
            while ($bytesRead < $length && !feof($file)) {
                $bytesToRead = min($buffer, $length - $bytesRead);
                echo fread($file, $bytesToRead);
                $bytesRead += $bytesToRead;
                
                if (connection_aborted()) {
                    break;
                }
                
                flush();
            }
            
            fclose($file);
        });

        return $response;
    }

    public function download(Request $request, $lessonId)
    {
        // Bloquer les tentatives de téléchargement
        abort(403, 'Le téléchargement de ce contenu n\'est pas autorisé.');
    }
}
