<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use App\Services\ContentRatingReminderService;
use App\Services\ReviewEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewEligibilityService $reviewEligibility
    ) {}

    /**
     * Store a new review or update an existing one
     */
    public function store(Request $request, Course $course)
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour noter un cours.');
        }

        $user = Auth::user();

        $eligibility = $this->reviewEligibility->evaluate($user, $course);
        if (! $eligibility['can_review']) {
            return redirect()->route('contents.show', $course)
                ->with('error', $eligibility['message']);
        }

        // Valider les données
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        // Vérifier si l'utilisateur a déjà un avis pour ce cours
        $existingReview = Review::where('user_id', $user->id)
            ->where('content_id', $course->id)
            ->first();

        if ($existingReview) {
            // Mettre à jour l'avis existant
            $existingReview->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_approved' => true, // Approuver automatiquement après modification
            ]);

            ContentRatingReminderService::forgetForUserAndContent($user->id, $course->id);
            $request->session()->forget('pending_content_rating_course_id');

            return redirect()->route('contents.show', $course)
                ->with('success', 'Votre avis a été mis à jour avec succès.');
        } else {
            // Créer un nouvel avis
            Review::create([
                'user_id' => $user->id,
                'content_id' => $course->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_approved' => true, // Approuver automatiquement
            ]);

            ContentRatingReminderService::forgetForUserAndContent($user->id, $course->id);
            $request->session()->forget('pending_content_rating_course_id');

            return redirect()->route('contents.show', $course)
                ->with('success', 'Votre avis a été publié avec succès.');
        }
    }

    /**
     * Delete a review
     */
    public function destroy(Course $course)
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour supprimer un avis.');
        }

        $user = Auth::user();

        // Trouver l'avis de l'utilisateur pour ce cours
        $review = Review::where('user_id', $user->id)
            ->where('content_id', $course->id)
            ->firstOrFail();

        // Vérifier que l'utilisateur est propriétaire de l'avis
        if ($review->user_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer cet avis.');
        }

        $review->delete();

        return redirect()->route('contents.show', $course)
            ->with('success', 'Votre avis a été supprimé avec succès.');
    }
}

