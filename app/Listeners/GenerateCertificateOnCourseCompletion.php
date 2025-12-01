<?php

namespace App\Listeners;

use App\Events\CourseCompleted;
use App\Mail\CertificateIssuedMail;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class GenerateCertificateOnCourseCompletion
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private CertificateService $certificateService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(CourseCompleted $event): void
    {
        try {
            // Générer le certificat
            $certificate = $this->certificateService->generateCertificate(
                $event->user,
                $event->course
            );

            // Envoyer l'email et WhatsApp avec le certificat
            $mailable = new CertificateIssuedMail($certificate);
            $communicationService = app(\App\Services\CommunicationService::class);
            $communicationService->sendEmailAndWhatsApp($event->user, $mailable);

            Log::info('Certificat généré et envoyé', [
                'user_id' => $event->user->id,
                'course_id' => $event->course->id,
                'certificate_id' => $certificate->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du certificat', [
                'user_id' => $event->user->id,
                'course_id' => $event->course->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

