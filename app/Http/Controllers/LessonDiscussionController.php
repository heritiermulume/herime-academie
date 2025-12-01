<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonDiscussion;
use Illuminate\Http\Request;

class LessonDiscussionController extends Controller
{
    /**
     * Obtenir les discussions d'une leçon (limité à 5 pour l'affichage initial)
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

        $discussions = LessonDiscussion::where('lesson_id', $lesson->id)
            ->mainThreads()
            ->with(['user:id,name,avatar', 'replies' => function($query) {
                $query->with('user:id,name,avatar')->latest();
            }])
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->limit(5)
            ->get();
        
        // Charger les likes de l'utilisateur pour toutes les discussions
        $userLikedDiscussionIds = [];
        if (auth()->check()) {
            $userLikedDiscussionIds = \App\Models\DiscussionLike::where('user_id', auth()->id())
                ->whereIn('discussion_id', $discussions->pluck('id'))
                ->pluck('discussion_id')
                ->toArray();
        }
        
        $discussions = $discussions->map(function($discussion) use ($userLikedDiscussionIds) {
            $isLiked = in_array($discussion->id, $userLikedDiscussionIds);
            
            return [
                'id' => $discussion->id,
                'content' => $discussion->content,
                'user' => [
                    'id' => $discussion->user->id,
                    'name' => $discussion->user->name,
                    'avatar' => $discussion->user->avatar,
                ],
                'user_id' => $discussion->user_id, // Ajout pour vérifier la propriété
                'likes_count' => $discussion->likes_count,
                'is_liked' => $isLiked,
                'is_pinned' => $discussion->is_pinned,
                'is_answered' => $discussion->is_answered,
                'replies_count' => $discussion->replies->count(),
                'replies' => $discussion->replies->map(function($reply) {
                    return [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'user' => [
                            'id' => $reply->user->id,
                            'name' => $reply->user->name,
                            'avatar' => $reply->user->avatar,
                        ],
                        'user_id' => $reply->user_id, // Ajout pour vérifier la propriété
                        'likes_count' => $reply->likes_count,
                        'created_at' => $reply->created_at->diffForHumans(),
                    ];
                }),
                'created_at' => $discussion->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'success' => true,
            'discussions' => $discussions
        ]);
    }

    /**
     * Afficher toutes les discussions avec pagination
     */
    public function all(Course $course, CourseLesson $lesson)
    {
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            abort(403, 'Accès non autorisé');
        }

        // Vérifier que le cours n'est pas téléchargeable
        if ($course->is_downloadable) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $discussions = LessonDiscussion::where('lesson_id', $lesson->id)
            ->mainThreads()
            ->with(['user:id,name,avatar', 'replies' => function($query) {
                $query->with('user:id,name,avatar')->latest();
            }])
            ->orderBy('is_pinned', 'desc')
            ->latest()
            ->paginate(15);

        // Charger les likes de l'utilisateur pour toutes les discussions
        if (auth()->check()) {
            $userLikedDiscussionIds = \App\Models\DiscussionLike::where('user_id', auth()->id())
                ->whereIn('discussion_id', $discussions->pluck('id'))
                ->pluck('discussion_id')
                ->toArray();
            
            // Ajouter l'information is_liked à chaque discussion
            $discussions->getCollection()->transform(function($discussion) use ($userLikedDiscussionIds) {
                $discussion->is_liked = in_array($discussion->id, $userLikedDiscussionIds);
                return $discussion;
            });
        }

        return view('learning.discussions', compact('course', 'lesson', 'discussions'));
    }

    /**
     * Créer une nouvelle discussion
     */
    public function store(Request $request, Course $course, CourseLesson $lesson)
    {
        // Détecter si c'est une requête AJAX/JSON
        $isAjax = $request->expectsJson() || $request->ajax() || $request->wantsJson() || 
                  $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                  $request->header('Accept') === 'application/json';
        
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
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
            return redirect()->route('courses.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:lesson_discussions,id'
        ]);

        // Récupérer parent_id depuis la requête (peut être null)
        $parentId = $request->input('parent_id');
        
        $discussion = LessonDiscussion::create([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'parent_id' => $parentId,
            'content' => $validated['content'],
        ]);

        $discussion->load('user:id,name,avatar');

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => $parentId ? '💬 Réponse publiée avec succès !' : '✨ Discussion créée avec succès !',
                'discussion' => [
                    'id' => $discussion->id,
                    'content' => $discussion->content,
                    'user' => [
                        'id' => $discussion->user->id,
                        'name' => $discussion->user->name,
                        'avatar' => $discussion->user->avatar,
                    ],
                    'likes_count' => $discussion->likes_count,
                    'is_pinned' => $discussion->is_pinned,
                    'created_at' => $discussion->created_at->diffForHumans(),
                ]
            ], 201);
        }

        return redirect()->route('learning.discussions.all', ['course' => $course->slug, 'lesson' => $lesson->id])
            ->with('success', $parentId ? '💬 Réponse publiée avec succès !' : '✨ Discussion créée avec succès !');
    }

    /**
     * Mettre à jour une discussion
     */
    public function update(Request $request, Course $course, CourseLesson $lesson, LessonDiscussion $discussion)
    {
        if (!auth()->check() || $discussion->user_id !== auth()->id()) {
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
            return redirect()->route('courses.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000'
        ]);

        $discussion->update(['content' => $validated['content']]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '✨ Discussion mise à jour avec succès !',
                'discussion' => $discussion
            ]);
        }

        return redirect()->route('learning.discussions.all', ['course' => $course->slug, 'lesson' => $lesson->id])
            ->with('success', '✨ Discussion mise à jour avec succès !');
    }

    /**
     * Supprimer une discussion
     */
    public function destroy(Request $request, Course $course, CourseLesson $lesson, LessonDiscussion $discussion)
    {
        // Détecter si c'est une requête AJAX/JSON
        $isAjax = $request->expectsJson() || $request->ajax() || $request->wantsJson() || 
                  $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                  $request->header('Accept') === 'application/json';
        
        if (!auth()->check() || ($discussion->user_id !== auth()->id() && $course->instructor_id !== auth()->id())) {
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
            return redirect()->route('courses.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $discussion->delete();

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ Discussion supprimée avec succès'
            ]);
        }

        return redirect()->route('learning.discussions.all', ['course' => $course->slug, 'lesson' => $lesson->id])
            ->with('success', '🗑️ Discussion supprimée avec succès');
    }

    /**
     * Liker/Unliker une discussion
     */
    public function toggleLike(Request $request, Course $course, CourseLesson $lesson, LessonDiscussion $discussion)
    {
        // Détecter si c'est une requête AJAX/JSON
        $isAjax = $request->expectsJson() || $request->ajax() || $request->wantsJson() || 
                  $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                  $request->header('Accept') === 'application/json';
        
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
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
            return redirect()->route('courses.show', $course)
                ->with('error', 'Ce cours est disponible uniquement en téléchargement.');
        }

        $userId = auth()->id();
        
        // Vérifier si l'utilisateur a déjà liké cette discussion
        $existingLike = \App\Models\DiscussionLike::where('user_id', $userId)
            ->where('discussion_id', $discussion->id)
            ->first();
        
        if ($existingLike) {
            // Si l'utilisateur a déjà liké, retirer le like (unlike)
            $existingLike->delete();
            $discussion->decrementLikes();
            $isLiked = false;
        } else {
            // Si l'utilisateur n'a pas encore liké, ajouter le like
            \App\Models\DiscussionLike::create([
                'user_id' => $userId,
                'discussion_id' => $discussion->id,
            ]);
            $discussion->incrementLikes();
            $isLiked = true;
        }
        
        // Recharger la discussion pour obtenir le nouveau compteur
        $discussion->refresh();

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'likes_count' => $discussion->likes_count,
                'is_liked' => $isLiked
            ]);
        }

        return redirect()->back()->with('success', $isLiked ? 'Like ajouté avec succès' : 'Like retiré avec succès');
    }

    /**
     * Épingler/Désépingler une discussion (instructeur uniquement)
     */
    public function togglePin(Course $course, CourseLesson $lesson, LessonDiscussion $discussion)
    {
        if (!auth()->check() || $course->instructor_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $discussion->update(['is_pinned' => !$discussion->is_pinned]);

        return response()->json([
            'success' => true,
            'is_pinned' => $discussion->is_pinned
        ]);
    }

    /**
     * Marquer comme répondu (instructeur uniquement)
     */
    public function markAsAnswered(Course $course, CourseLesson $lesson, LessonDiscussion $discussion)
    {
        if (!auth()->check() || $course->instructor_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $discussion->update(['is_answered' => !$discussion->is_answered]);

        return response()->json([
            'success' => true,
            'is_answered' => $discussion->is_answered
        ]);
    }
}
