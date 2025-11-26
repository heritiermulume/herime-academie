<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
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

        // Vérifier que l'utilisateur est inscrit au cours ou que le cours est gratuit
        if (!$course->isEnrolledBy($user->id) && !$course->is_free) {
            return redirect()->route('courses.show', $course)
                ->with('error', 'Vous devez être inscrit à ce cours pour le noter.');
        }

        // Valider les données
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        // Vérifier si l'utilisateur a déjà un avis pour ce cours
        $existingReview = Review::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingReview) {
            // Mettre à jour l'avis existant
            $existingReview->update([
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_approved' => false, // Réapprouver après modification
            ]);

            return redirect()->route('courses.show', $course)
                ->with('success', 'Votre avis a été mis à jour avec succès.');
        } else {
            // Créer un nouvel avis
            Review::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
                'is_approved' => false, // Nécessite l'approbation de l'admin
            ]);

            return redirect()->route('courses.show', $course)
                ->with('success', 'Votre avis a été soumis avec succès et sera publié après modération.');
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
            ->where('course_id', $course->id)
            ->firstOrFail();

        // Vérifier que l'utilisateur est propriétaire de l'avis
        if ($review->user_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer cet avis.');
        }

        $review->delete();

        return redirect()->route('courses.show', $course)
            ->with('success', 'Votre avis a été supprimé avec succès.');
    }
}

