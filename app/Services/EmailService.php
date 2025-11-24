<?php

namespace App\Services;

use App\Models\SentEmail;
use App\Models\User;
use App\Notifications\EmailSentNotification;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class EmailService
{
    /**
     * Envoyer un email et enregistrer l'envoi + créer une notification
     *
     * @param User|null $user L'utilisateur destinataire
     * @param string $email L'email du destinataire si pas d'utilisateur
     * @param Mailable $mailable L'email Mailable à envoyer
     * @param string $type Le type d'email (invoice, enrollment, announcement, custom, payment)
     * @param array $metadata Métadonnées supplémentaires à stocker
     * @return bool
     */
    public static function sendAndRecord(
        ?User $user,
        string $email,
        Mailable $mailable,
        string $type = 'custom',
        array $metadata = []
    ): bool {
        // Récupérer le sujet de l'email avant le try/catch
        $envelope = $mailable->envelope();
        $subject = $envelope->subject ?? 'Sans objet';
        
        // Déterminer le contenu de l'email
        $content = 'Email personnalisé';
        if ($mailable instanceof \App\Mail\InvoiceMail) {
            $content = 'Facture envoyée - voir pièce jointe';
        } elseif ($mailable instanceof \App\Mail\CourseEnrolledMail) {
            $content = 'Confirmation d\'inscription au cours';
        }
        
        try {
            // Envoyer l'email de manière synchrone (immédiate)
            // Mail::to()->send() envoie immédiatement, contrairement à Mail::to()->queue()
            Mail::to($email)->send($mailable);
            
            // Enregistrer l'email envoyé
            SentEmail::create([
                'user_id' => $user?->id,
                'recipient_email' => $email,
                'recipient_name' => $user?->name ?? null,
                'subject' => $subject,
                'content' => $content,
                'attachments' => null, // Pour l'instant, pas de gestion des pièces jointes dans ce service
                'type' => $type,
                'status' => 'sent',
                'sent_at' => now(),
                'metadata' => $metadata,
            ]);
            
            // Notifier l'utilisateur qu'un email lui a été envoyé (si utilisateur connecté)
                    // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
                    if ($user) {
                        try {
                            Notification::sendNow($user, new EmailSentNotification($subject, now()));
                } catch (\Exception $e) {
                    Log::error("Erreur lors de la création de la notification pour l'utilisateur {$user->id}: " . $e->getMessage());
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi d'email à {$email}: " . $e->getMessage());
            
            // Enregistrer l'échec
            try {
                SentEmail::create([
                    'user_id' => $user?->id,
                    'recipient_email' => $email,
                    'recipient_name' => $user?->name ?? null,
                    'subject' => $subject,
                    'content' => 'Email échoué',
                    'type' => $type,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'metadata' => $metadata,
                ]);
            } catch (\Exception $recordException) {
                Log::error("Erreur lors de l'enregistrement de l'échec d'email: " . $recordException->getMessage());
            }
            
            return false;
        }
    }
}
