<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use App\Models\CourseDownload;
use App\Models\Order;
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

        // Vérifier les conditions selon le type de contenu
        $canReview = false;
        $errorMessage = '';

        if ($course->is_downloadable) {
            // Contenu téléchargeable
            if ($course->is_free) {
                // Téléchargeable gratuit : avoir téléchargé au moins une fois
                $hasDownloaded = CourseDownload::where('content_id', $course->id)
                    ->where('user_id', $user->id)
                    ->exists();
                
                if ($hasDownloaded) {
                    $canReview = true;
                } else {
                    $errorMessage = 'Vous devez avoir téléchargé ce contenu au moins une fois pour pouvoir le noter.';
                }
            } else {
                // Téléchargeable payant : avoir payé
                $hasPurchased = Order::where('user_id', $user->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereHas('orderItems', function ($query) use ($course) {
                        $query->where('content_id', $course->id);
                    })
                    ->exists();
                
                if ($hasPurchased) {
                    $canReview = true;
                } else {
                    $errorMessage = 'Vous devez avoir acheté ce contenu pour pouvoir le noter.';
                }
            }
        } else {
            // Contenu non téléchargeable
            if ($course->is_free) {
                // Non téléchargeable gratuit : être inscrit
                $isEnrolled = $course->isEnrolledBy($user->id);
                
                if ($isEnrolled) {
                    $canReview = true;
                } else {
                    $errorMessage = 'Vous devez être inscrit à ce contenu pour pouvoir le noter.';
                }
            } else {
                // Non téléchargeable payant : avoir payé
                $hasPurchased = Order::where('user_id', $user->id)
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereHas('orderItems', function ($query) use ($course) {
                        $query->where('content_id', $course->id);
                    })
                    ->exists();
                
                if ($hasPurchased) {
                    $canReview = true;
                } else {
                    $errorMessage = 'Vous devez avoir acheté ce contenu pour pouvoir le noter.';
                }
            }
        }

        if (!$canReview) {
            return redirect()->route('contents.show', $course)
                ->with('error', $errorMessage);
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

