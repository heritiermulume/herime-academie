<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Messages reçus
        $receivedMessages = Message::where('receiver_id', $user->id)
            ->with(['sender', 'course'])
            ->latest()
            ->paginate(20);

        // Messages envoyés
        $sentMessages = Message::where('sender_id', $user->id)
            ->with(['receiver', 'course'])
            ->latest()
            ->paginate(20);

        // Messages non lus
        $unreadCount = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('messages.index', compact('receivedMessages', 'sentMessages', 'unreadCount'));
    }

    public function show(Message $message)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur peut voir ce message
        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce message.');
        }

        // Marquer comme lu si c'est un message reçu
        if ($message->receiver_id === $user->id && !$message->is_read) {
            $message->markAsRead();
        }

        $message->load(['sender', 'receiver', 'course']);

        return view('messages.show', compact('message'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        $recipients = collect();
        $courses = collect();

        // Si c'est un formateur, récupérer ses étudiants
        if ($user->isInstructor()) {
            $recipients = User::students()
                ->whereHas('enrollments', function($query) use ($user) {
                    $query->whereHas('course', function($q) use ($user) {
                        $q->where('instructor_id', $user->id);
                    });
                })
                ->get();
            
            $courses = $user->courses()->published()->get();
        }

        // Si c'est un étudiant, récupérer ses formateurs
        if ($user->isStudent()) {
            $recipients = User::instructors()
                ->whereHas('courses', function($query) use ($user) {
                    $query->whereHas('enrollments', function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
                })
                ->get();
            
            $courses = Course::whereHas('enrollments', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->published()->get();
        }

        return view('messages.create', compact('recipients', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        $receiver = User::findOrFail($request->receiver_id);

        // Vérifier que l'utilisateur peut envoyer un message à ce destinataire
        if (!$this->canSendMessage($user, $receiver, $request->course_id)) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas envoyer de message à cette personne.');
        }

        Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $receiver->id,
            'course_id' => $request->course_id,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return redirect()->route('messages.index')
            ->with('success', 'Message envoyé avec succès.');
    }

    public function reply(Message $originalMessage, Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = Auth::user();
        
        // Vérifier que l'utilisateur peut répondre à ce message
        if ($originalMessage->sender_id !== $user->id && $originalMessage->receiver_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce message.');
        }

        // Créer la réponse
        Message::create([
            'sender_id' => $user->id,
            'receiver_id' => $originalMessage->sender_id === $user->id ? 
                $originalMessage->receiver_id : $originalMessage->sender_id,
            'course_id' => $originalMessage->course_id,
            'subject' => 'Re: ' . $originalMessage->subject,
            'message' => $request->message,
        ]);

        return redirect()->route('messages.show', $originalMessage)
            ->with('success', 'Réponse envoyée avec succès.');
    }

    public function markAsRead(Message $message)
    {
        $user = Auth::user();
        
        if ($message->receiver_id === $user->id) {
            $message->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 403);
    }

    public function delete(Message $message)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur peut supprimer ce message
        if ($message->sender_id !== $user->id && $message->receiver_id !== $user->id) {
            abort(403, 'Accès non autorisé à ce message.');
        }

        $message->delete();

        return redirect()->route('messages.index')
            ->with('success', 'Message supprimé avec succès.');
    }

    private function canSendMessage($sender, $receiver, $courseId = null)
    {
        // Un utilisateur ne peut pas s'envoyer un message à lui-même
        if ($sender->id === $receiver->id) {
            return false;
        }

        // Si c'est un formateur qui envoie à un étudiant
        if ($sender->isInstructor() && $receiver->isStudent()) {
            if ($courseId) {
                // Vérifier que l'étudiant est inscrit au cours du formateur
                return $receiver->enrollments()
                    ->whereHas('course', function($query) use ($sender, $courseId) {
                        $query->where('instructor_id', $sender->id)
                              ->where('id', $courseId);
                    })
                    ->exists();
            }
            return true; // Un formateur peut envoyer des messages à ses étudiants
        }

        // Si c'est un étudiant qui envoie à un formateur
        if ($sender->isStudent() && $receiver->isInstructor()) {
            if ($courseId) {
                // Vérifier que l'étudiant est inscrit au cours du formateur
                return $sender->enrollments()
                    ->whereHas('course', function($query) use ($receiver, $courseId) {
                        $query->where('instructor_id', $receiver->id)
                              ->where('id', $courseId);
                    })
                    ->exists();
            }
            return true; // Un étudiant peut envoyer des messages à ses formateurs
        }

        return false;
    }
}
