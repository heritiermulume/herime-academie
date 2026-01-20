<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonNote;
use Illuminate\Http\Request;

class LessonNoteController extends Controller
{
    /**
     * Obtenir les notes d'un utilisateur pour une leçon (limité à 5 pour l'affichage initial)
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

        $notes = LessonNote::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'notes' => $notes
        ]);
    }

    /**
     * Afficher toutes les notes avec pagination
     */
    public function all(Course $course, CourseLesson $lesson)
    {
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que le cours n'est pas téléchargeable
        if ($course->is_downloadable) {
            return redirect()->route('contents.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $notes = LessonNote::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('learning.notes', compact('course', 'lesson', 'notes'));
    }

    /**
     * Créer une nouvelle note
     */
    public function store(Request $request, Course $course, CourseLesson $lesson)
    {
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        // Vérifier que le cours n'est pas téléchargeable
        if ($course->is_downloadable) {
            return response()->json(['success' => false, 'message' => 'Ce cours est disponible uniquement en téléchargement.'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'timestamp' => 'nullable|integer|min:0'
        ]);

        $note = LessonNote::create([
            'user_id' => auth()->id(),
            'content_id' => $course->id,
            'lesson_id' => $lesson->id,
            'content' => $validated['content'],
            'timestamp' => $validated['timestamp'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => '✨ Note enregistrée avec succès !',
            'note' => $note
        ], 201);
    }

    /**
     * Mettre à jour une note
     */
    public function update(Request $request, Course $course, CourseLesson $lesson, LessonNote $note)
    {
        if (!auth()->check() || $note->user_id !== auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que le cours n'est pas téléchargeable
        if ($course->is_downloadable) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Ce cours est disponible uniquement en téléchargement.'], 403);
            }
            return redirect()->route('contents.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'timestamp' => 'nullable|integer|min:0'
        ]);

        $note->update([
            'content' => $validated['content'],
            'timestamp' => $validated['timestamp'] ?? $note->timestamp,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '✨ Note mise à jour avec succès !',
                'note' => $note
            ]);
        }

        return redirect()->route('learning.notes.all', ['course' => $course->slug, 'lesson' => $lesson->id])
            ->with('success', '✨ Note mise à jour avec succès !');
    }

    /**
     * Supprimer une note
     */
    public function destroy(Request $request, Course $course, CourseLesson $lesson, LessonNote $note)
    {
        // Détecter si c'est une requête AJAX/JSON
        $isAjax = $request->expectsJson() || $request->ajax() || $request->wantsJson() || 
                  $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                  $request->header('Accept') === 'application/json';
        
        if (!auth()->check() || $note->user_id !== auth()->id()) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que le cours n'est pas téléchargeable
        if ($course->is_downloadable) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Ce cours est disponible uniquement en téléchargement.'], 403);
            }
            return redirect()->route('contents.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $note->delete();

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ Note supprimée avec succès'
            ]);
        }

        return redirect()->route('learning.notes.all', ['course' => $course->slug, 'lesson' => $lesson->id])
            ->with('success', '🗑️ Note supprimée avec succès');
    }
}
