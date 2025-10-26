<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
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
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour télécharger ce cours.');
        }

        // Vérifier l'accès au cours selon le type (gratuit/payant)
        if (!$this->hasAccessToCourse($course, Auth::id())) {
            if ($course->is_free) {
                return back()->with('error', 'Vous devez être inscrit à ce cours pour le télécharger.');
            } else {
                return back()->with('error', 'Vous devez acheter ce cours pour le télécharger.');
            }
        }

        // Récupérer toutes les leçons du cours avec leurs sections
        $course->load(['sections.lessons' => function($query) {
            $query->orderBy('sort_order');
        }]);

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
        // Vérifier si l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vous devez être connecté pour télécharger ce fichier.');
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

        // Vérifier si la leçon a un fichier
        if (!$lesson->file_path) {
            return back()->with('error', 'Aucun fichier disponible pour cette leçon.');
        }

        $filePath = storage_path('app/' . $lesson->file_path);
        
        if (!file_exists($filePath)) {
            return back()->with('error', 'Le fichier n\'existe plus sur le serveur.');
        }

        $fileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title) . '.' . pathinfo($filePath, PATHINFO_EXTENSION);
        
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

        // Pour les cours payants, vérifier l'inscription ET le paiement
        $enrollment = $course->enrollments()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->first();

        if (!$enrollment) {
            return false;
        }

        // Vérifier que la commande associée est payée
        if ($enrollment->order_id) {
            $order = $enrollment->order;
            return $order && $order->status === 'paid';
        }

        // Si pas de commande associée, considérer comme accès refusé pour les cours payants
        return false;
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
        
        // Ajouter le fichier de la leçon s'il existe
        if ($lesson->file_path) {
            $filePath = storage_path('app/' . $lesson->file_path);
            if (file_exists($filePath)) {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $fileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title) . '.' . $extension;
                $zip->addFile($filePath, $sectionPath . $fileName);
                $added = true;
            }
        }
        
        // Ajouter le contenu texte s'il existe
        if ($lesson->content_text) {
            $textFileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title) . ' - Contenu.txt';
            $zip->addFromString($sectionPath . $textFileName, $lesson->content_text);
            $added = true;
        }
        
        // Ajouter l'URL de la vidéo s'il s'agit d'une leçon vidéo
        if ($lesson->type === 'video' && $lesson->content_url) {
            $videoInfo = "URL de la vidéo: " . $lesson->content_url . "\n";
            $videoInfo .= "Titre: " . $lesson->title . "\n";
            $videoInfo .= "Description: " . ($lesson->description ?? 'Aucune description') . "\n";
            $videoInfo .= "Durée: " . $lesson->duration . " minutes\n";
            
            $videoFileName = 'Leçon ' . $lesson->sort_order . ' - ' . $this->sanitizeFileName($lesson->title) . ' - Info Vidéo.txt';
            $zip->addFromString($sectionPath . $videoFileName, $videoInfo);
            $added = true;
        }
        
        return $added;
    }

    /**
     * Ajouter les ressources supplémentaires du cours
     */
    private function addCourseResources($zip, $course)
    {
        // Ajouter l'image du cours si elle existe
        if ($course->image_path) {
            $imagePath = storage_path('app/' . $course->image_path);
            if (file_exists($imagePath)) {
                $zip->addFile($imagePath, 'image-cours.' . pathinfo($imagePath, PATHINFO_EXTENSION));
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