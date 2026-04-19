<?php

namespace App\Mail;

use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseEnrolledMail extends Mailable
{
    use Queueable, SerializesModels;

    public $course;

    /**
     * Create a new message instance.
     */
    public function __construct(Course $course)
    {
        $this->course = $course;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address('academie@herime.com', 'Herime Académie'),
            subject: 'Inscription confirmée - '.$this->course->title.' - Herime Académie',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Charger les relations nécessaires
        $this->course->load(['provider', 'category']);

        // Personnaliser selon le type de contenu
        if ($this->course->is_in_person_program ?? false) {
            // Programme en présentiel
            $courseUrl = route('contents.show', $this->course->slug);
            $buttonText = 'Voir le programme';
            $messageTitle = $this->course->is_free ? 'Inscription au programme confirmée !' : 'Réservation confirmée !';
            $messageText = $this->course->is_free
                ? 'Votre inscription au programme en présentiel a été confirmée. Consultez la page du programme pour les coordonnées de contact (WhatsApp) et les prochaines étapes.'
                : 'Votre réservation pour ce programme en présentiel a été confirmée. Consultez la page du programme pour les coordonnées de contact (WhatsApp) et les détails de participation.';
            $features = [
                'Contacter l\'organisateur via WhatsApp',
                'Consulter les détails et le lieu du programme',
                'Recevoir les informations pratiques',
            ];
        } elseif ($this->course->is_downloadable) {
            // Contenu téléchargeable
            if ($this->course->is_free) {
                // Téléchargeable gratuit
                $courseUrl = route('contents.show', $this->course->slug);
                $buttonText = 'Télécharger le contenu gratuitement';
                $messageTitle = 'Contenu gratuit disponible !';
                $messageText = 'Félicitations ! Vous avez maintenant accès à ce contenu gratuit. Vous pouvez le télécharger dès maintenant et en profiter à tout moment.';
                $features = [
                    'Télécharger le contenu immédiatement',
                    'Accéder à tous les fichiers du produit',
                    'Conserver le contenu pour toujours',
                    'Accéder à votre bibliothèque de contenus téléchargeables',
                ];
            } else {
                // Téléchargeable payant
                $courseUrl = route('contents.show', $this->course->slug);
                $buttonText = 'Télécharger le produit maintenant';
                $messageTitle = 'Achat confirmé !';
                $messageText = 'Votre achat a été confirmé avec succès. Vous pouvez maintenant télécharger ce produit et en profiter immédiatement.';
                $features = [
                    'Télécharger le produit immédiatement',
                    'Accéder à tous les fichiers du produit',
                    'Conserver le produit pour toujours',
                    'Accéder à votre bibliothèque de contenus',
                ];
            }
        } elseif ($this->course->isEnrollmentReceiptOnly()) {
            $courseUrl = route('contents.show', $this->course->slug);
            $buttonText = 'Voir le contenu et le reçu';
            if ($this->course->is_free) {
                $messageTitle = 'Inscription confirmée !';
                $messageText = 'Votre inscription a été confirmée. Vous recevrez un reçu PDF par email ; vous pouvez aussi le télécharger depuis la page du contenu.';
                $features = [
                    'Consulter la page du contenu',
                    'Télécharger votre reçu d\'inscription en PDF',
                    'Conserver le reçu pour vos dossiers',
                ];
            } else {
                $messageTitle = 'Achat confirmé !';
                $messageText = 'Votre achat a été confirmé. Vous recevrez un reçu PDF par email ; vous pouvez aussi le télécharger depuis la page du contenu.';
                $features = [
                    'Consulter la page du contenu',
                    'Télécharger votre reçu d\'inscription en PDF',
                    'Conserver le reçu pour vos dossiers',
                ];
            }
        } else {
            // Contenu non téléchargeable
            if ($this->course->is_free) {
                // Non téléchargeable gratuit
                $courseUrl = route('learning.course', $this->course->slug);
                $buttonText = 'Commencer le cours maintenant';
                $messageTitle = 'Inscription confirmée !';
                $messageText = 'Votre inscription a été confirmée avec succès. Vous pouvez maintenant accéder à tous les contenus du cours et commencer votre apprentissage immédiatement.';
                $features = [
                    'Accéder à tous les modules et leçons du cours',
                    'Suivre votre progression en temps réel',
                    'Télécharger les ressources et supports de cours',
                    'Interagir avec le prestataire et les autres étudiants',
                    'Obtenir un certificat à la fin du cours',
                ];
            } else {
                // Non téléchargeable payant
                $courseUrl = route('learning.course', $this->course->slug);
                $buttonText = 'Commencer le cours maintenant';
                $messageTitle = 'Achat confirmé !';
                $messageText = 'Votre achat a été confirmé avec succès. Vous pouvez maintenant accéder à tous les contenus du cours et commencer votre apprentissage immédiatement.';
                $features = [
                    'Accéder à tous les modules et leçons du cours',
                    'Suivre votre progression en temps réel',
                    'Télécharger les ressources et supports de cours',
                    'Interagir avec le prestataire et les autres étudiants',
                    'Obtenir un certificat à la fin du cours',
                ];
            }
        }

        return new Content(
            view: 'emails.course-enrolled',
            with: [
                'course' => $this->course,
                'courseUrl' => $courseUrl,
                'buttonText' => $buttonText,
                'messageTitle' => $messageTitle,
                'messageText' => $messageText,
                'features' => $features,
                'logoUrl' => config('app.url').'/images/logo-herime-academie.png',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
