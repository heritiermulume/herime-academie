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
     * Obtenir toutes les ressources d'une leçon (+ fichier principal hébergé, retéléchargeable)
     */
    public function index(Course $course, CourseLesson $lesson)
    {
        if (! auth()->check() || ! $course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        if ($lesson->content_id !== $course->id) {
            return response()->json(['success' => false, 'message' => 'Leçon introuvable'], 404);
        }

        $resources = LessonResource::where('lesson_id', $lesson->id)
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($resource) {
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
            'resources' => $resources,
            'lesson_attachment' => $this->lessonAttachmentPayload($course, $lesson),
        ]);
    }

    /**
     * Téléchargement du fichier principal de la leçon (hébergé sur le disque privé).
     */
    public function downloadLessonAttachment(Course $course, CourseLesson $lesson)
    {
        if (! auth()->check() || ! $course->isEnrolledBy(auth()->id())) {
            abort(403);
        }

        if ($lesson->content_id !== $course->id) {
            abort(404);
        }

        $relative = $lesson->getStoredLessonFileRelativePath();
        if (! $relative) {
            abort(404, 'Aucun fichier téléchargeable pour cette leçon.');
        }

        $disk = Storage::disk('local');
        if (! $disk->exists($relative)) {
            abort(404, 'Fichier introuvable.');
        }

        $ext = pathinfo($relative, PATHINFO_EXTENSION);
        $base = 'Lecon-'.preg_replace('/[^a-zA-Z0-9_-]+/', '-', $lesson->title);
        $filename = $ext !== '' && $ext !== '0' ? $base.'.'.$ext : $base;

        return $disk->download($relative, $filename);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function lessonAttachmentPayload(Course $course, CourseLesson $lesson): ?array
    {
        $disk = Storage::disk('local');
        $relative = $lesson->getStoredLessonFileRelativePath();
        if ($relative && $disk->exists($relative)) {
            $bytes = (int) $disk->size($relative);
            $ext = pathinfo($relative, PATHINFO_EXTENSION);

            return [
                'kind' => 'file',
                'title' => 'Fichier de la leçon : '.$lesson->title,
                'description' => 'Téléchargez à nouveau le fichier principal de cette leçon (même contenu que dans le lecteur ou la vue associée).',
                'file_type' => $ext ? strtoupper((string) $ext) : 'Fichier',
                'file_size' => $this->formatBytes($bytes),
                'download_count' => null,
                'download_url' => route('learning.lesson.attachment.download', [
                    'course' => $course->slug,
                    'lesson' => $lesson->id,
                ]),
            ];
        }

        foreach (['file_path', 'content_url'] as $attr) {
            $value = $lesson->getRawOriginal($attr) ?? $lesson->getAttribute($attr);
            if (! empty($value) && filter_var($value, FILTER_VALIDATE_URL)) {
                return [
                    'kind' => 'link',
                    'title' => 'Lien associé à la leçon',
                    'description' => 'Ouvrir la ressource dans un nouvel onglet.',
                    'file_type' => 'Lien',
                    'file_size' => '—',
                    'download_count' => null,
                    'external_url' => $value,
                ];
            }
        }

        return null;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $v = (float) $bytes;
        while ($v >= 1024 && $i < count($units) - 1) {
            $v /= 1024;
            $i++;
        }

        return round($v, 2).' '.$units[$i];
    }

    /**
     * Télécharger une ressource
     */
    public function download(Course $course, CourseLesson $lesson, LessonResource $resource)
    {
        if (! auth()->check() || ! $course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        if ($lesson->content_id !== $course->id || $resource->lesson_id !== $lesson->id) {
            return response()->json(['success' => false, 'message' => 'Ressource introuvable'], 404);
        }

        if (! $resource->is_downloadable) {
            return response()->json(['success' => false, 'message' => 'Cette ressource n\'est pas téléchargeable'], 403);
        }

        // Incrémenter le compteur
        $resource->incrementDownloadCount();

        // Si c'est un lien externe
        if ($resource->type === 'link') {
            return response()->json([
                'success' => true,
                'type' => 'redirect',
                'url' => $resource->external_url,
            ]);
        }

        // Si c'est un fichier local
        if ($resource->file_path && Storage::disk('local')->exists($resource->file_path)) {
            return response()->download(
                Storage::disk('local')->path($resource->file_path),
                $resource->title.'.'.pathinfo($resource->file_path, PATHINFO_EXTENSION)
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
        if (! auth()->check() || $course->provider_id !== auth()->id()) {
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
            'resource' => $resource,
        ], 201);
    }

    /**
     * Mettre à jour une ressource
     */
    public function update(Request $request, CourseLesson $lesson, LessonResource $resource)
    {
        $course = $lesson->course;
        if (! auth()->check() || $course->provider_id !== auth()->id()) {
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
            'resource' => $resource,
        ]);
    }

    /**
     * Supprimer une ressource
     */
    public function destroy(CourseLesson $lesson, LessonResource $resource)
    {
        $course = $lesson->course;
        if (! auth()->check() || $course->provider_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Supprimer le fichier si c'est un fichier local
        if ($resource->type === 'file' && $resource->file_path) {
            Storage::disk('local')->delete($resource->file_path);
        }

        $resource->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ressource supprimée avec succès',
        ]);
    }
}
