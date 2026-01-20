<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonResourceController extends Controller
{
    /**
     * Obtenir toutes les ressources d'une leçon
     */
    public function index(Course $course, CourseLesson $lesson)
    {
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Vérifier que le cours n'est pas téléchargeable
        if ($course->is_downloadable) {
            return response()->json(['success' => false, 'message' => 'Ce cours est disponible uniquement en téléchargement.'], 403);
        }

        $resources = LessonResource::where('lesson_id', $lesson->id)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($resource) {
                return [
                    'id' => $resource->id,
                    'title' => $resource->title,
                    'description' => $resource->description,
                    'type' => $resource->type,
                    'file_type' => $resource->file_type,
                    'file_size' => $resource->formatted_file_size,
                    'download_count' => $resource->download_count,
                    'is_downloadable' => $resource->is_downloadable,
                    'url' => $resource->file_url,
                    'created_at' => $resource->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'resources' => $resources
        ]);
    }

    /**
     * Télécharger une ressource
     */
    public function download(Course $course, CourseLesson $lesson, LessonResource $resource)
    {
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Vérifier que le cours n'est pas téléchargeable
        if ($course->is_downloadable) {
            return response()->json(['success' => false, 'message' => 'Ce cours est disponible uniquement en téléchargement.'], 403);
        }

        if (!$resource->is_downloadable) {
            return response()->json(['success' => false, 'message' => 'Cette ressource n\'est pas téléchargeable'], 403);
        }

        // Incrémenter le compteur
        $resource->incrementDownloadCount();

        // Si c'est un lien externe
        if ($resource->type === 'link') {
            return response()->json([
                'success' => true,
                'type' => 'redirect',
                'url' => $resource->external_url
            ]);
        }

        // Si c'est un fichier local
        if ($resource->file_path && Storage::disk('local')->exists($resource->file_path)) {
            return response()->download(
                Storage::disk('local')->path($resource->file_path),
                $resource->title . '.' . pathinfo($resource->file_path, PATHINFO_EXTENSION)
            );
        }

        return response()->json(['success' => false, 'message' => 'Fichier introuvable'], 404);
    }

    /**
     * Créer une nouvelle ressource (pour instructeurs)
     */
    public function store(Request $request, CourseLesson $lesson)
    {
        // Vérifier que l'utilisateur est l'instructeur du cours
        $course = $lesson->course;
        if (!auth()->check() || $course->provider_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:file,link',
            'file' => 'required_if:type,file|file|max:51200', // 50MB max
            'external_url' => 'required_if:type,link|url',
            'is_downloadable' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $filePath = null;
        $fileType = null;
        $fileSize = null;

        if ($validated['type'] === 'file' && $request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('lesson-resources', 'local');
            $fileType = $file->getClientOriginalExtension();
            $fileSize = $file->getSize();
        }

        $resource = LessonResource::create([
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'file_path' => $filePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'external_url' => $validated['external_url'] ?? null,
            'is_downloadable' => $validated['is_downloadable'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ressource ajoutée avec succès',
            'resource' => $resource
        ], 201);
    }

    /**
     * Mettre à jour une ressource
     */
    public function update(Request $request, CourseLesson $lesson, LessonResource $resource)
    {
        $course = $lesson->course;
        if (!auth()->check() || $course->provider_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_downloadable' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $resource->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ressource mise à jour avec succès',
            'resource' => $resource
        ]);
    }

    /**
     * Supprimer une ressource
     */
    public function destroy(CourseLesson $lesson, LessonResource $resource)
    {
        $course = $lesson->course;
        if (!auth()->check() || $course->provider_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Supprimer le fichier si c'est un fichier local
        if ($resource->type === 'file' && $resource->file_path) {
            Storage::disk('local')->delete($resource->file_path);
        }

        $resource->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ressource supprimée avec succès'
        ]);
    }
}
