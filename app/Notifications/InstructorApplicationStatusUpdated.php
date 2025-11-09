<?php

namespace App\Notifications;

use App\Models\InstructorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InstructorApplicationStatusUpdated extends Notification
{
    use Queueable;

    protected InstructorApplication $application;

    public function __construct(InstructorApplication $application)
    {
        $this->application = $application;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = $this->application->status;
        $messages = $this->resolveMessage($status);

        return [
            'title' => $messages['title'],
            'excerpt' => $messages['message'],
            'type' => $messages['type'],
            'button_text' => 'Voir ma candidature',
            'button_url' => route('instructor-application.status', $this->application),
            'status' => $status,
            'admin_notes' => $this->application->admin_notes,
        ];
    }

    protected function resolveMessage(string $status): array
    {
        $notes = $this->application->admin_notes;

        return match ($status) {
            'under_review' => [
                'title' => 'Votre candidature est en cours d\'examen',
                'message' => 'Notre équipe analyse actuellement votre profil. Nous reviendrons vers vous dès que possible.',
                'type' => 'info',
            ],
            'approved' => [
                'title' => 'Félicitations ! Vous êtes désormais formateur',
                'message' => 'Votre candidature a été acceptée. Vous pouvez commencer à créer vos cours dès maintenant.',
                'type' => 'success',
            ],
            'rejected' => [
                'title' => 'Votre candidature n\'a pas été retenue',
                'message' => $notes
                    ? "Après analyse, votre candidature a été refusée. Motif : {$notes}"
                    : 'Après analyse, votre candidature a été refusée. Vous pouvez soumettre une nouvelle candidature ultérieurement.',
                'type' => 'error',
            ],
            default => [
                'title' => 'Mise à jour de votre candidature formateur',
                'message' => 'Votre candidature est en attente. Vous serez notifié dès qu\'elle sera traitée.',
                'type' => 'warning',
            ],
        };
    }
}


