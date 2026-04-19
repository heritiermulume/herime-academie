<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingInviteController extends Controller
{
    /**
     * Lien signé : ouvre la modale de notation après connexion sur la page du contenu.
     */
    public function show(Request $request, Course $course)
    {
        $userId = $request->query('user');
        if (! $userId || ! User::whereKey($userId)->exists()) {
            abort(404);
        }

        if (! Auth::check()) {
            session()->put('url.intended', $request->fullUrl());

            return redirect()->route('login')
                ->with('info', 'Connectez-vous pour noter ce contenu et laisser votre avis.');
        }

        if ((int) Auth::id() !== (int) $userId) {
            abort(403, 'Ce lien de notation est destiné à un autre compte.');
        }

        session()->put('pending_content_rating_course_id', $course->id);

        return redirect()->route('contents.show', $course);
    }
}
