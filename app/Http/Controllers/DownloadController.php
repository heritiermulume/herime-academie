<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseDownload;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DownloadController extends Controller
{
    /**
     * Download course materials
     */
    public function course(Course $course)
    {
        // Vérifier que le cours est publié
        if (!$course->is_published) {
            abort(404, 'Ce cours n\'est pas disponible.');
        }

        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour télécharger ce cours.');
        }

        // Vérifier que le cours est téléchargeable
        if (!$course->is_downloadable) {
            return back()->with('error', 'Ce cours n\'est pas disponible en téléchargement.');
        }

        // Vérifier l'accès au cours selon le type (gratuit/payant)
        // Les utilisateurs déjà inscrits peuvent toujours télécharger, même si is_sale_enabled est maintenant false
        if (!$this->hasAccessToCourse($course, Auth::id())) {
            // Si la vente est désactivée et l'utilisateur n'est pas inscrit, bloquer
            if (!$course->is_sale_enabled) {
                return back()->with('error', 'Ce cours n\'est pas actuellement disponible.');
            }
            
            if ($course->is_free) {
                return back()->with('error', 'Vous devez être inscrit à ce cours pour le télécharger.');
            } else {
                return back()->with('error', 'Vous devez acheter ce cours pour le télécharger.');
            }
        }

        // Si un fichier de téléchargement spécifique est défini, le télécharger directement
        if ($course->download_file_path) {
            // Enregistrer le téléchargement AVANT de télécharger
            $this->recordDownload($course, 'file');
            return $this->downloadSpecificFile($course);
        }

        // Sinon, créer un ZIP avec tout le contenu du cours
        // Enregistrer le téléchargement AVANT de créer le ZIP
        $this->recordDownload($course, 'zip');
        return $this->downloadCourseAsZip($course);
    }

    /**
     * Enregistrer un téléchargement
     */
    private function recordDownload(Course $course, $downloadType = 'zip')
    {
        try {
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent();
            
            // Obtenir les informations géographiques depuis l'IP
            $geoInfo = $this->getGeoInfoFromIp($ipAddress);
            
            CourseDownload::create([
                'course_id' => $course->id,
                'user_id' => Auth::id(),
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'country' => $geoInfo['country'] ?? null,
                'country_name' => $geoInfo['country_name'] ?? null,
                'city' => $geoInfo['city'] ?? null,
                'region' => $geoInfo['region'] ?? null,
                'download_type' => $downloadType,
            ]);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer le téléchargement
            \Log::warning('Erreur lors de l\'enregistrement du téléchargement: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les informations géographiques depuis une adresse IP
     */
    private function getGeoInfoFromIp($ipAddress)
    {
        // Pour les IPs locales, retourner null
        if (in_array($ipAddress, ['127.0.0.1', '::1']) || str_starts_with($ipAddress, '192.168.') || str_starts_with($ipAddress, '10.') || str_starts_with($ipAddress, '172.')) {
            return [];
        }

        try {
            // Essayer d'utiliser un service gratuit comme ipapi.co ou ip-api.com
            // Utilisons ip-api.com (gratuit, limite 45 requêtes/min)
            $response = @file_get_contents("http://ip-api.com/json/{$ipAddress}?fields=status,country,countryCode,city,regionName", false, stream_context_create([
                'http' => [
                    'timeout' => 2, // Timeout de 2 secondes
                ]
            ]));

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['status']) && $data['status'] === 'success') {
                    return [
                        'country' => $data['countryCode'] ?? null,
                        'country_name' => $data['country'] ?? null,
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                    ];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Erreur lors de la récupération des informations géographiques: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Télécharger un fichier spécifique défini pour le cours
     */
    private function downloadSpecificFile(Course $course)
    {
        $filePath = null;
        $fileName = null;
        
        if (!filter_var($course->download_file_path, FILTER_VALIDATE_URL)) {
            $disk = Storage::disk('local');

            $cleanPath = ltrim($course->download_file_path, '/');

            if ($disk->exists($cleanPath)) {
                $filePath = $disk->path($cleanPath);
                $fileName = basename($cleanPath);
            }
        } else {
            // C'est une URL externe - rediriger vers cette URL
            return redirect($course->download_file_path);
        }
        
        if (!$filePath || !file_exists($filePath)) {
            return back()->with('error', 'Le fichier de téléchargement n\'existe plus sur le serveur.');
        }
        
        // Si pas de nom de fichier spécifique, utiliser un nom par défaut
        if (!$fileName) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileName = $this->sanitizeFileName($course->title) . '.' . ($extension ?: 'zip');
        }
        
        return response()->download($filePath, $fileName);
    }

    /**
     * Télécharger le cours complet sous forme de ZIP
     */
    private function downloadCourseAsZip(Course $course)
    {
        // Récupérer toutes les leçons publiées du cours avec leurs sections
        $course->load([
            'instructor',
            'category',
            'sections' => function($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            },
            'sections.lessons' => function($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            }
        ]);

        // Créer un fichier ZIP temporaire
        $zipFileName = 'cours-' . $course->slug . '-' . now()->format('Y-m-d') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        // Créer le dossier temp s'il n'existe pas
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
            return back()->with('error', 'Impossible de créer le fichier ZIP.');
        }

        // Ajouter un fichier README avec les informations du cours
        $readmeContent = $this->generateCourseReadme($course);
        $zip->addFromString('README.txt', $readmeContent);

        // Ajouter les leçons organisées par sections
        $hasContent = false;
        foreach ($course->sections as $section) {
            if ($section->lessons->isNotEmpty()) {
                $sectionPath = 'Section ' . $section->sort_order . ' - ' . $this->sanitizeFileName($section->title) . '/';
                
                foreach ($section->lessons as $lesson) {
                    if ($this->addLessonToZip($zip, $lesson, $sectionPath)) {
                        $hasContent = true;
                    }
                }
            }
        }

        // Ajouter les ressources supplémentaires si elles existent
        $this->addCourseResources($zip, $course);

        $zip->close();

        if (!$hasContent) {
            return back()->with('error', 'Aucun contenu téléchargeable disponible pour ce cours.');
        }

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Download a specific lesson file
     */
    public function lesson(Course $course, CourseLesson $lesson)
    {
        // Vérifier que le cours est publié
        if (!$course->is_published) {
            abort(404, 'Ce cours n\'est pas disponible.');
        }

        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour télécharger ce fichier.');
        }

        // Vérifier que le cours est téléchargeable
        if (!$course->is_downloadable) {
            return back()->with('error', 'Ce cours n\'est pas disponible en téléchargement.');
        }

        // Vérifier l'accès au cours selon le type (gratuit/payant)
        if (!$this->hasAccessToCourse($course, Auth::id())) {
            if ($course->is_free) {
                return back()->with('error', 'Vous devez être inscrit à ce cours pour télécharger ce fichier.');
            } else {
                return back()->with('error', 'Vous devez acheter ce cours pour télécharger ce fichier.');
            }
        }

        // Vérifier si la leçon appartient au cours
        if ($lesson->course_id !== $course->id) {
            return back()->with('error', 'Cette leçon n\'appartient pas à ce cours.');
        }

        // Chercher le fichier à télécharger (file_path ou content_url)
        $filePath = null;
        $fileName = $lesson->downloadable_filename ?? ($this->sanitizeFileName($lesson->title) . '.zip');

        if ($lesson->download_file_path && !filter_var($lesson->download_file_path, FILTER_VALIDATE_URL)) {
            $disk = Storage::disk('local');
            $cleanPath = ltrim($lesson->download_file_path, '/');

            if ($disk->exists($cleanPath)) {
                $filePath = $disk->path($cleanPath);
            } elseif ($disk->exists($lesson->download_file_path)) {
                $filePath = $disk->path($lesson->download_file_path);
            }
        } elseif ($lesson->download_file_path) {
            return redirect($lesson->download_file_path);
        }

        if (!$filePath || !file_exists($filePath)) {
            return back()->with('error', 'Aucun fichier disponible pour cette leçon ou le fichier n\'existe plus sur le serveur.');
        }

        $fileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title);
        if ($extension) {
            $fileName .= '.' . $extension;
        }
        
        return response()->download($filePath, $fileName);
    }

    /**
     * Vérifier si l'utilisateur a accès au cours
     */
    private function hasAccessToCourse(Course $course, $userId)
    {
        // Pour les cours gratuits, vérifier seulement l'inscription
        if ($course->is_free) {
            return $course->isEnrolledBy($userId);
        }

        // Pour les cours payants, vérifier d'abord l'inscription
        $enrollment = $course->enrollments()
            ->where('user_id', $userId)
            ->whereIn('status', ['active', 'completed'])
            ->first();

        if ($enrollment) {
            // Si l'utilisateur est inscrit, il a accès au téléchargement
            // Vérifier si la commande associée est payée (si elle existe)
            if ($enrollment->order_id) {
                $order = $enrollment->order;
                // Si la commande existe et est payée, accès autorisé
                if ($order && in_array($order->status, ['paid', 'completed'])) {
                    return true;
                }
            } else {
                // Si pas d'order_id mais enrollment existe, c'est probablement un cours gratuit ou inscription manuelle
                // Autoriser l'accès
                return true;
            }
        }

        // Si pas d'inscription, vérifier si l'utilisateur a acheté le cours via une commande payée
        $hasPurchased = \App\Models\Order::where('user_id', $userId)
            ->whereIn('status', ['paid', 'completed'])
            ->whereHas('orderItems', function($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->exists();

        return $hasPurchased;
    }

    /**
     * Générer le contenu README pour le cours
     */
    private function generateCourseReadme(Course $course)
    {
        $content = "COURS: " . $course->title . "\n";
        $content .= "=" . str_repeat("=", strlen($course->title)) . "\n\n";
        
        $content .= "Description:\n";
        $content .= $course->description . "\n\n";
        
        $content .= "Instructeur: " . $course->instructor->name . "\n";
        $content .= "Durée: " . $course->duration . " minutes\n";
        $content .= "Niveau: " . ucfirst($course->level) . "\n";
        $content .= "Catégorie: " . $course->category->name . "\n\n";
        
        $content .= "STRUCTURE DU COURS:\n";
        $content .= str_repeat("-", 20) . "\n\n";
        
        foreach ($course->sections as $section) {
            $content .= "Section " . $section->sort_order . ": " . $section->title . "\n";
            if ($section->description) {
                $content .= "  " . $section->description . "\n";
            }
            $content .= "\n";
            
            foreach ($section->lessons as $lesson) {
                $content .= "  Leçon " . $lesson->sort_order . ": " . $lesson->title . "\n";
                if ($lesson->description) {
                    $content .= "    " . $lesson->description . "\n";
                }
                $content .= "    Type: " . ucfirst($lesson->type) . "\n";
                $content .= "    Durée: " . $lesson->duration . " minutes\n\n";
            }
        }
        
        $content .= "Téléchargé le: " . now()->format('d/m/Y à H:i') . "\n";
        $content .= "Source: Herime Academie\n";
        
        return $content;
    }

    /**
     * Ajouter une leçon au ZIP
     */
    private function addLessonToZip($zip, $lesson, $sectionPath)
    {
        $added = false;
        $disk = Storage::disk('local'); // Utiliser le stockage privé
        
        // Ajouter le fichier de la leçon s'il existe (file_path)
        $sourceForExtension = $lesson->file_path ?: $lesson->content_url;
        $extension = $sourceForExtension ? pathinfo($sourceForExtension, PATHINFO_EXTENSION) : 'bin';
        $lessonFileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title);
        if ($extension) {
            $lessonFileName .= '.' . $extension;
        }

        if ($lesson->file_path && !filter_var($lesson->file_path, FILTER_VALIDATE_URL)) {
            $cleanPath = ltrim($lesson->file_path, '/');
            if ($disk->exists($cleanPath)) {
                $zip->addFile($disk->path($cleanPath), $sectionPath . $lessonFileName);
                return true;
            }
        }

        if ($lesson->content_url && !filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
            $cleanPath = ltrim($lesson->content_url, '/');
            if ($disk->exists($cleanPath)) {
                $zip->addFile($disk->path($cleanPath), $sectionPath . $lessonFileName);
                return true;
            }
        }
        
        // Ajouter le contenu texte s'il existe
        if ($lesson->content_text) {
            $textFileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title) . ' - Contenu.txt';
            $zip->addFromString($sectionPath . $textFileName, $lesson->content_text);
            $added = true;
        }
        
        // Ajouter l'URL de la vidéo s'il s'agit d'une leçon vidéo (YouTube ou autre URL externe)
        if ($lesson->type === 'video') {
            $videoInfo = "Titre: " . $lesson->title . "\n";
            $videoInfo .= "Description: " . ($lesson->description ?? 'Aucune description') . "\n";
            $videoInfo .= "Durée: " . $lesson->duration . " minutes\n";
            
            // Ajouter l'URL YouTube si disponible
            if ($lesson->youtube_video_id) {
                $videoInfo .= "URL YouTube: https://www.youtube.com/watch?v=" . $lesson->youtube_video_id . "\n";
            }
            
            // Ajouter l'URL de contenu si c'est une URL externe
            if ($lesson->content_url && filter_var($lesson->content_url, FILTER_VALIDATE_URL)) {
                $videoInfo .= "URL de la vidéo: " . $lesson->content_url . "\n";
            }
            
            $videoFileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title) . ' - Info Vidéo.txt';
            $zip->addFromString($sectionPath . $videoFileName, $videoInfo);
            $added = true;
        }
        
        // Pour les leçons de type PDF, DOC, etc., ajouter aussi les informations
        if (in_array($lesson->type, ['pdf', 'text', 'quiz'])) {
            $infoFileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title) . ' - Info.txt';
            $infoContent = "Titre: " . $lesson->title . "\n";
            $infoContent .= "Type: " . ucfirst($lesson->type) . "\n";
            $infoContent .= "Description: " . ($lesson->description ?? 'Aucune description') . "\n";
            if ($lesson->duration) {
                $infoContent .= "Durée: " . $lesson->duration . " minutes\n";
            }
            $zip->addFromString($sectionPath . $infoFileName, $infoContent);
            $added = true;
        }
        
        return $added;
    }

    /**
     * Ajouter les ressources supplémentaires du cours
     */
    private function addCourseResources($zip, $course)
    {
        // Ajouter l'image/thumbnail du cours si elle existe
        if ($course->thumbnail && !filter_var($course->thumbnail, FILTER_VALIDATE_URL)) {
            $thumbnailPath = ltrim($course->thumbnail, '/');
            $disk = Storage::disk('local');

            if ($disk->exists($thumbnailPath)) {
                $fullPath = $disk->path($thumbnailPath);
                $zip->addFile($fullPath, 'image-cours.' . pathinfo($fullPath, PATHINFO_EXTENSION));
            }
        }

        if ($course->image_path && $course->image_path !== $course->thumbnail && !filter_var($course->image_path, FILTER_VALIDATE_URL)) {
            $imagePath = ltrim($course->image_path, '/');
            $disk = Storage::disk('local');

            if ($disk->exists($imagePath)) {
                $fullPath = $disk->path($imagePath);
                $zip->addFile($fullPath, 'image-cours-2.' . pathinfo($fullPath, PATHINFO_EXTENSION));
            }
        }
        
        // Ajouter un fichier d'index HTML pour une meilleure navigation
        $indexContent = $this->generateIndexHtml($course);
        $zip->addFromString('index.html', $indexContent);
    }

    /**
     * Générer un fichier index HTML pour le cours
     */
    private function generateIndexHtml(Course $course)
    {
        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($course->title) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { border-bottom: 2px solid #007bff; padding-bottom: 20px; margin-bottom: 30px; }
        .section { margin-bottom: 30px; }
        .lesson { margin-left: 20px; margin-bottom: 15px; }
        .lesson-type { background: #f8f9fa; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($course->title) . '</h1>
        <p><strong>Instructeur:</strong> ' . htmlspecialchars($course->instructor->name) . '</p>
        <p><strong>Durée:</strong> ' . $course->duration . ' minutes</p>
        <p><strong>Niveau:</strong> ' . ucfirst($course->level) . '</p>
    </div>
    
    <div class="description">
        <h2>Description</h2>
        <p>' . nl2br(htmlspecialchars($course->description)) . '</p>
    </div>
    
    <div class="content">
        <h2>Structure du cours</h2>';
        
        foreach ($course->sections as $section) {
            $html .= '<div class="section">
                <h3>Section ' . $section->sort_order . ': ' . htmlspecialchars($section->title) . '</h3>';
            
            if ($section->description) {
                $html .= '<p>' . htmlspecialchars($section->description) . '</p>';
            }
            
            foreach ($section->lessons as $lesson) {
                $html .= '<div class="lesson">
                    <h4>Leçon ' . $lesson->sort_order . ': ' . htmlspecialchars($lesson->title) . '</h4>
                    <span class="lesson-type">' . ucfirst($lesson->type) . '</span>
                    <span> - ' . $lesson->duration . ' minutes</span>';
                
                if ($lesson->description) {
                    $html .= '<p>' . htmlspecialchars($lesson->description) . '</p>';
                }
                
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>
    
    <div class="footer">
        <p><em>Téléchargé le ' . now()->format('d/m/Y à H:i') . ' depuis Herime Academie</em></p>
    </div>
</body>
</html>';
        
        return $html;
    }

    /**
     * Nettoyer un nom de fichier pour éviter les caractères problématiques
     */
    private function sanitizeFileName($fileName)
    {
        // Remplacer les caractères problématiques
        $fileName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $fileName);
        // Limiter la longueur
        return substr($fileName, 0, 100);
    }
}