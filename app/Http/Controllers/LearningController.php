<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\LessonProgress;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    /**
     * Afficher la page d'apprentissage d'un cours
     */
    public function learn(Course $course)
    {
        // Vérifier que l'utilisateur est inscrit au cours
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Vous devez être inscrit à ce cours pour y accéder.');
        }

        $enrollment = $course->getEnrollmentFor(auth()->id());
        
        // Charger les données complètes depuis la base de données
        $course->load([
            'sections' => function($query) {
                $query->orderBy('sort_order');
            },
            'sections.lessons' => function($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            },
            'instructor' => function($query) {
                $query->select('id', 'name', 'email', 'bio', 'avatar', 'created_at');
            },
            'category',
            'reviews' => function($query) {
                $query->with(['user' => function($query) {
                    $query->select('id', 'name', 'avatar');
                }])->latest()->limit(10);
            },
            'enrollments' => function($query) {
                $query->with(['user' => function($query) {
                    $query->select('id', 'name', 'avatar');
                }])->latest()->limit(5);
            }
        ]);

        // Obtenir la progression de l'utilisateur
        $progress = $this->getUserProgress($course);

        // Obtenir les statistiques du cours
        $courseStats = $this->getCourseStats($course);
        
        // Obtenir les cours recommandés
        $recommendedCourses = $this->getRecommendedCourses($course);

        // Obtenir la leçon active (si une leçon est en cours de visualisation)
        $activeLessonId = session('active_lesson_id', null);
        
        // Toujours vérifier s'il y a une dernière leçon consultée pour rediriger
        // (même si activeLessonId existe en session, car la session peut être expirée)
        $lastLesson = $this->getLastLessonForUser($course);
        
        // Si on a trouvé une dernière leçon et qu'elle est différente de celle en session
        // OU si aucune leçon n'est en session, rediriger vers la dernière leçon
        if ($lastLesson && (!$activeLessonId || $activeLessonId != $lastLesson->id)) {
            // Vérifier que la leçon a vraiment une progression (time_watched > 0)
            $lastLessonProgress = $this->getLessonProgress($lastLesson);
            if ($lastLessonProgress && $lastLessonProgress->time_watched > 0) {
                // Rediriger automatiquement vers la dernière leçon consultée
                return redirect()->route('learning.lesson', ['course' => $course->slug, 'lesson' => $lastLesson->id]);
            }
        }

        return view('learning.course', compact('course', 'enrollment', 'progress', 'courseStats', 'recommendedCourses', 'activeLessonId'));
    }

    /**
     * Afficher une leçon spécifique
     */
    public function lesson(Course $course, CourseLesson $lesson)
    {
        // Vérifier que l'utilisateur est inscrit au cours
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Vous devez être inscrit à ce cours pour y accéder.');
        }

        // Vérifier que la leçon appartient au cours
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        // Charger les données nécessaires
        $course->load([
            'sections' => function($query) {
            $query->orderBy('sort_order');
            },
            'sections.lessons' => function($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            }
        ]);

        // Obtenir la progression de cette leçon
        $lessonProgress = $this->getLessonProgress($lesson);

        // Trouver la leçon précédente et suivante dans tout le cours
        $allLessons = $course->lessons()
            ->join('course_sections', 'course_lessons.section_id', '=', 'course_sections.id')
            ->orderBy('course_sections.sort_order')
            ->orderBy('course_lessons.sort_order')
            ->select('course_lessons.*')
            ->get();
        $currentIndex = $allLessons->search(function($item) use ($lesson) {
            return $item->id === $lesson->id;
        });

        $previousLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        // Stocker l'ID de la leçon active dans la session
        session(['active_lesson_id' => $lesson->id]);

        // Obtenir la progression de l'utilisateur
        $progress = $this->getUserProgress($course);

        // Obtenir les statistiques du cours
        $courseStats = $this->getCourseStats($course);
        
        // Obtenir les cours recommandés
        $recommendedCourses = $this->getRecommendedCourses($course);

        // Obtenir la leçon active
        $activeLesson = $lesson;
        $activeLessonId = $lesson->id;

        return view('learning.course', compact('course', 'activeLesson', 'activeLessonId', 'lessonProgress', 'previousLesson', 'nextLesson', 'progress', 'courseStats', 'recommendedCourses'));
    }

    /**
     * Marquer une leçon comme commencée
     */
    public function startLesson(Request $request, Course $course, CourseLesson $lesson)
    {
        $request->validate([
            'time_watched' => 'integer|min:0'
        ]);

        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $progress = LessonProgress::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->markAsStarted();
        $progress->updateTimeWatched($request->time_watched ?? 0);

        return response()->json([
            'success' => true,
            'progress' => $progress->progress_percentage
        ]);
    }

    /**
     * Mettre à jour la progression d'une leçon
     */
    public function updateProgress(Request $request, Course $course, CourseLesson $lesson)
    {
        $request->validate([
            'time_watched' => 'required|integer|min:0',
            'is_completed' => 'boolean'
        ]);

        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $progress = LessonProgress::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->updateTimeWatched($request->time_watched);

        if ($request->is_completed) {
            $progress->markAsCompleted();
        }

        // Mettre à jour la progression globale du cours
        $this->updateCourseProgress($course);

        return response()->json([
            'success' => true,
            'progress' => $progress->progress_percentage,
            'is_completed' => $progress->is_completed
        ]);
    }

    /**
     * Marquer une leçon comme terminée
     */
    public function completeLesson(Request $request, Course $course, CourseLesson $lesson)
    {
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $progress = LessonProgress::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        $progress->markAsCompleted();

        // Mettre à jour la progression globale du cours
        $this->updateCourseProgress($course);

        return response()->json([
            'success' => true,
            'message' => 'Leçon marquée comme terminée'
        ]);
    }

    /**
     * Soumettre un quiz
     */
    public function submitQuiz(Request $request, Course $course, CourseLesson $lesson)
    {
        if (!auth()->check() || !$course->isEnrolledBy(auth()->id())) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'answers' => 'required|array'
        ]);

        // Vérifier que c'est bien un quiz
        if ($lesson->type !== 'quiz') {
            return response()->json(['success' => false, 'message' => 'Ce n\'est pas un quiz'], 400);
        }

        // Calculer le score
        $quizData = json_decode($lesson->quiz_data, true);
        $questions = $quizData['questions'] ?? [];
        $totalQuestions = count($questions);
        $score = 0;

        foreach ($questions as $index => $question) {
            $correctAnswer = $question['correct_answer'] ?? null;
            $userAnswer = $request->answers[$index] ?? null;
            
            if ($userAnswer == $correctAnswer) {
                $score++;
            }
        }

        // Créer ou mettre à jour la progression
        $progress = LessonProgress::firstOrCreate([
            'user_id' => auth()->id(),
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        // Si le quiz est réussi (par exemple, au moins 70%), marquer comme terminé
        if ($score >= ($totalQuestions * 0.7)) {
            $progress->markAsCompleted();
            $this->updateCourseProgress($course);
        }

        return response()->json([
            'success' => true,
            'results' => [
                'score' => $score,
                'total' => $totalQuestions,
                'percentage' => round(($score / $totalQuestions) * 100, 2),
                'passed' => $score >= ($totalQuestions * 0.7)
            ]
        ]);
    }

    /**
     * Obtenir la progression de l'utilisateur pour un cours
     */
    private function getUserProgress(Course $course)
    {
        $progress = LessonProgress::where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->with(['lesson' => function ($query) {
                // S'assurer que la relation lesson est bien chargée avec les champs nécessaires
                $query->select('id', 'duration', 'title');
            }])
            ->get()
            ->keyBy('lesson_id');

        // S'assurer que la relation lesson est bien chargée pour chaque progression
        // Charger les leçons manquantes si nécessaire
        $lessonIds = $progress->pluck('lesson_id')->filter();
        $lessons = $course->lessons()->whereIn('id', $lessonIds)->get()->keyBy('id');
        
        $progress->each(function ($progressItem) use ($lessons) {
            // Si la relation lesson n'est pas chargée, la charger manuellement
            if (!$progressItem->relationLoaded('lesson') && isset($lessons[$progressItem->lesson_id])) {
                $progressItem->setRelation('lesson', $lessons[$progressItem->lesson_id]);
            }
        });

        $totalLessons = $course->lessons()->count();
        $completedLessons = $progress->where('is_completed', true)->count();
        $overallProgress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;

        // Obtenir les IDs des leçons terminées et commencées
        $completedLessonsIds = $progress->where('is_completed', true)->keys();
        $startedLessonsIds = $progress->where('is_started', true)->keys();

        return [
            'overall_progress' => round($overallProgress, 2),
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'lesson_progress' => $progress,
            'completed_lessons_ids' => $completedLessonsIds,
            'started_lessons_ids' => $startedLessonsIds
        ];
    }

    /**
     * Obtenir la progression d'une leçon spécifique
     */
    private function getLessonProgress(CourseLesson $lesson)
    {
        return LessonProgress::where('user_id', auth()->id())
            ->where('lesson_id', $lesson->id)
            ->with('lesson') // Charger la relation lesson pour le calcul de progress_percentage
            ->first();
    }

    /**
     * Obtenir la dernière leçon consultée par l'utilisateur pour un cours
     * Retourne la dernière leçon commencée mais non complétée, ou la première leçon non commencée
     */
    private function getLastLessonForUser(Course $course)
    {
        $userId = auth()->id();
        
        // Charger toutes les leçons du cours dans l'ordre
        $allLessons = $course->lessons()
            ->join('course_sections', 'course_lessons.section_id', '=', 'course_sections.id')
            ->where('course_lessons.is_published', true)
            ->orderBy('course_sections.sort_order')
            ->orderBy('course_lessons.sort_order')
            ->select('course_lessons.*')
            ->get();
        
        if ($allLessons->isEmpty()) {
            return null;
        }
        
        // Obtenir toutes les progressions de l'utilisateur pour ce cours
        $progresses = LessonProgress::where('user_id', $userId)
            ->where('course_id', $course->id)
            ->with('lesson')
            ->get()
            ->keyBy('lesson_id');
        
        // Chercher la dernière leçon consultée (avec progression mais non complétée)
        // Utiliser updated_at car il est mis à jour à chaque progression (toutes les 10 secondes)
        $lastViewedLesson = null;
        $lastUpdatedAt = null;
        
        foreach ($allLessons as $lesson) {
            $progress = $progresses->get($lesson->id);
            
            // Si la leçon a une progression (time_watched > 0) mais n'est pas complétée
            if ($progress && $progress->time_watched > 0 && !$progress->is_completed) {
                // Utiliser updated_at pour trouver la dernière leçon consultée
                $updatedAt = $progress->updated_at ? $progress->updated_at->timestamp : 0;
                if (!$lastUpdatedAt || $updatedAt > $lastUpdatedAt) {
                    $lastUpdatedAt = $updatedAt;
                    $lastViewedLesson = $lesson;
                }
            }
        }
        
        // Si on a trouvé une leçon consultée mais non complétée, la retourner
        if ($lastViewedLesson) {
            return $lastViewedLesson;
        }
        
        // Sinon, chercher la première leçon non commencée (sans progression)
        foreach ($allLessons as $lesson) {
            $progress = $progresses->get($lesson->id);
            if (!$progress || ($progress->time_watched == 0 && !$progress->is_started)) {
                return $lesson;
            }
        }
        
        // Si toutes les leçons sont complétées, retourner la dernière leçon
        return $allLessons->last();
    }

    /**
     * Mettre à jour la progression globale du cours
     */
    private function updateCourseProgress(Course $course)
    {
        $enrollment = $course->getEnrollmentFor(auth()->id());
        if (!$enrollment) return;

        $totalLessons = $course->lessons()->count();
        $completedLessons = LessonProgress::where('user_id', auth()->id())
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->count();

        $progress = $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0;

        $enrollment->update([
            'progress' => $progress,
            'status' => $progress >= 100 ? 'completed' : 'active',
            'completed_at' => $progress >= 100 ? now() : null
        ]);
    }

    /**
     * Obtenir les statistiques du cours
     */
    private function getCourseStats(Course $course)
    {
        $totalLessons = $course->lessons()->count();
        $totalDuration = $course->lessons()->sum('duration');
        $videoLessons = $course->lessons()->where('type', 'video')->count();
        $textLessons = $course->lessons()->where('type', 'text')->count();
        $pdfLessons = $course->lessons()->where('type', 'pdf')->count();
        $quizLessons = $course->lessons()->where('type', 'quiz')->count();
        
        // Statistiques avancées
        $averageRating = $course->reviews()->avg('rating') ?? 0;
        $totalReviews = $course->reviews()->count();
        $totalStudents = $course->enrollments()->count();
        $completedStudents = $course->enrollments()->where('status', 'completed')->count();
        $completionRate = $totalStudents > 0 ? ($completedStudents / $totalStudents) * 100 : 0;
        
        // Distribution des notes
        $ratingDistribution = $course->reviews()
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();
        
        // Leçons récentes
        $recentLessons = $course->lessons()
            ->where('is_published', true)
            ->latest('created_at')
            ->limit(3)
            ->get();
        
        // Progression moyenne des étudiants
        $averageProgress = $course->enrollments()
            ->where('status', '!=', 'cancelled')
            ->avg('progress') ?? 0;

        return [
            'total_lessons' => $totalLessons,
            'total_duration' => $totalDuration,
            'video_lessons' => $videoLessons,
            'text_lessons' => $textLessons,
            'pdf_lessons' => $pdfLessons,
            'quiz_lessons' => $quizLessons,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'total_students' => $totalStudents,
            'completed_students' => $completedStudents,
            'completion_rate' => round($completionRate, 1),
            'rating_distribution' => $ratingDistribution,
            'recent_lessons' => $recentLessons,
            'average_progress' => round($averageProgress, 1),
            'course_level' => $course->level,
            'course_language' => $course->language,
            'is_downloadable' => $course->is_downloadable,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at
        ];
    }
    
    /**
     * Obtenir des cours recommandés (même algorithme que le panier)
     */
    private function getRecommendedCourses(Course $course)
    {
        // Exclure le cours actuel et les cours déjà achetés/inscrits
        $excludedCourseIds = [$course->id];
        
        if (auth()->check()) {
            $userEnrollments = auth()->user()->enrollments()
                ->whereIn('status', ['active', 'completed'])
                
                ->pluck('course_id')
                ->toArray();
            $excludedCourseIds = array_merge($excludedCourseIds, $userEnrollments);
        }

        $recommendations = collect();

        // 1. Cours complémentaires de la même catégorie
        $categoryRecommendations = Course::published()
            ->where('is_free', false)
            ->where('category_id', $course->category_id)
            ->whereNotIn('id', $excludedCourseIds)
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $categoryRecommendations = $categoryRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($categoryRecommendations);

        // 2. Cours du même niveau de difficulté (pour progression)
        $levelRecommendations = Course::published()
            ->where('is_free', false)
            ->where('level', $course->level)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $levelRecommendations = $levelRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($levelRecommendations);

        // 3. Cours du même instructeur (si l'utilisateur aime le style)
        $instructorRecommendations = Course::published()
            ->where('is_free', false)
            ->where('instructor_id', $course->instructor_id)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $instructorRecommendations = $instructorRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($instructorRecommendations);

        // 4. Cours populaires récents (tendance)
        $trendingRecommendations = Course::published()
            ->where('is_free', false)
            ->whereNotIn('id', $excludedCourseIds)
            ->whereNotIn('id', $recommendations->pluck('id'))
            ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->get();

        // Filtrer manuellement les cours gratuits et achetés
        $trendingRecommendations = $trendingRecommendations->filter(function($course) {
            return !$course->is_free && !$this->isCoursePurchased($course);
        });

        $recommendations = $recommendations->merge($trendingRecommendations);

        // 5. Si l'utilisateur est connecté, recommandations basées sur ses préférences
        if (auth()->check()) {
            $userEnrollments = auth()->user()->enrollments()
                ->with('course')
            ->get()
                ->pluck('course.category_id')
                ->unique()
                ->toArray();

            if (!empty($userEnrollments)) {
                $userPreferenceRecommendations = Course::published()
                    ->where('is_free', false)
                    ->whereIn('category_id', $userEnrollments)
                    ->whereNotIn('id', $excludedCourseIds)
                    ->whereNotIn('id', $recommendations->pluck('id'))
                    ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                    ->orderBy('created_at', 'desc')
                    ->limit(1)
                    ->get();

                // Filtrer manuellement les cours gratuits et achetés
                $userPreferenceRecommendations = $userPreferenceRecommendations->filter(function($course) {
                    return !$course->is_free && !$this->isCoursePurchased($course);
                });

                $recommendations = $recommendations->merge($userPreferenceRecommendations);
            }
        }

        // 6. Si on n'a pas assez de recommandations, ajouter des cours populaires
        if ($recommendations->count() < 4) {
            $popularRecommendations = Course::published()
                ->where('is_free', false)
                ->whereNotIn('id', $excludedCourseIds)
                ->whereNotIn('id', $recommendations->pluck('id'))
                ->with(['instructor', 'category', 'reviews', 'enrollments', 'sections.lessons'])
                ->orderBy('created_at', 'desc')
                ->limit(4 - $recommendations->count())
                ->get();

            // Filtrer manuellement les cours gratuits et achetés
            $popularRecommendations = $popularRecommendations->filter(function($course) {
                return !$course->is_free && !$this->isCoursePurchased($course);
            });

            $recommendations = $recommendations->merge($popularRecommendations);
        }

        // Filtrage final pour s'assurer qu'aucun cours gratuit ou acheté ne passe
        $finalRecommendations = $recommendations->filter(function($course) {
            // Exclure les cours gratuits
            if ($course->is_free) {
                return false;
            }
            
            // Exclure les cours achetés ou auxquels l'utilisateur est inscrit
            if (auth()->check()) {
                $isPurchased = auth()->user()
                    ->enrollments()
                    ->where('course_id', $course->id)
                    ->whereIn('status', ['active', 'completed'])
                    
                    ->exists();
                
                if ($isPurchased) {
                    return false;
                }
            }
            
            return true;
        })->shuffle()->take(4);
        
        return $finalRecommendations->map(function($course) {
            // Charger les relations nécessaires si elles ne sont pas déjà chargées
            if (!$course->relationLoaded('sections')) {
                $course->load(['sections.lessons', 'reviews', 'enrollments']);
            }
            
            // Ajouter les statistiques calculées
            $course->stats = [
                'total_lessons' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->count() : 0;
                }) : 0,
                'total_duration' => $course->sections ? $course->sections->sum(function($section) {
                    return $section->lessons ? $section->lessons->sum('duration') : 0;
                }) : 0,
                'total_students' => $course->enrollments ? $course->enrollments->count() : 0,
                'average_rating' => $course->reviews ? $course->reviews->avg('rating') ?? 0 : 0,
                'total_reviews' => $course->reviews ? $course->reviews->count() : 0,
            ];
            
            return $course;
        });
    }

    /**
     * Vérifier si un cours a été acheté ou si l'utilisateur y est inscrit
     */
    private function isCoursePurchased($course)
    {
        if (!auth()->check()) {
            return false;
        }
        
        $userId = auth()->id();
        
        // Vérifier si l'utilisateur est inscrit au cours
        $isEnrolled = $course->isEnrolledBy($userId);
        if ($isEnrolled) {
            return true;
        }
        
        // Vérifier si l'utilisateur a acheté le cours (pour les cours payants)
        if (!$course->is_free) {
            $hasPurchased = \App\Models\Order::where('user_id', $userId)
                ->where('status', 'paid')
                ->whereHas('orderItems', function($query) use ($course) {
                    $query->where('course_id', $course->id);
                })
                ->exists();
            
            if ($hasPurchased) {
                return true;
            }
        }
        
        return false;
    }
}