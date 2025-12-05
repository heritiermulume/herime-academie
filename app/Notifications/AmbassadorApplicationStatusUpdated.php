<?php

namespace App\Notifications;

use App\Models\AmbassadorApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AmbassadorApplicationStatusUpdated extends Notification
{
    use Queueable;

    protected AmbassadorApplication $application;
    protected string $status;

    public function __construct(AmbassadorApplication $application, string $status)
    {
        $this->application = $application;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $messages = $this->resolveMessage($this->status);

        return [
            'title' => $messages['title'],
            'excerpt' => $messages['message'],
            'type' => $messages['type'],
            'button_text' => 'Voir ma candidature',
            'button_url' => route('ambassador-application.status', $this->application),
            'status' => $this->status,
            'admin_notes' => $this->application->admin_notes,
        ];
    }

    protected function resolveMessage(string $status): array
    {
        $notes = $this->application->admin_notes;

        switch ($status) {
            case 'under_review':
                return [
                    'title' => 'Votre candidature ambassadeur est en cours d\'examen',
                    'message' => 'Notre équipe analyse actuellement votre profil. Nous reviendrons vers vous dès que possible.',
                    'type' => 'info',
                ];
            case 'approved':
                return [
                    'title' => 'Félicitations ! Vous êtes désormais ambassadeur',
                    'message' => 'Votre candidature a été acceptée. Vous avez reçu un code promo unique que vous pouvez partager pour gagner des commissions.',
                    'type' => 'success',
                ];
            case 'rejected':
                return [
                    'title' => 'Votre candidature ambassadeur n\'a pas été retenue',
                    'message' => $notes
                        ? "Après analyse, votre candidature a été refusée. Motif : {$notes}"
                        : 'Après analyse, votre candidature a été refusée. Vous pouvez soumettre une nouvelle candidature ultérieurement.',
                    'type' => 'error',
                ];
            default:
                return [
                    'title' => 'Mise à jour de votre candidature ambassadeur',
                    'message' => 'Votre candidature est en attente. Vous serez notifié dès qu\'elle sera traitée.',
                    'type' => 'warning',
                ];
        }
    }
}
