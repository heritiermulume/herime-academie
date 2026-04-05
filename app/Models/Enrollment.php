<?php

namespace App\Models;

use App\Notifications\CourseEnrolled;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class Enrollment extends Model
{
    /**
     * Flag pour éviter d'envoyer les notifications deux fois
     * si createAndNotify est utilisé
     */
    protected static $skipNotifications = false;

    protected $fillable = [
        'user_id',
        'content_id',
        'order_id',
        'access_granted_by_subscription_id',
        'status',
        'progress',
        'completed_at',
    ];

    /**
     * Boot du modèle - Ajouter les événements
     */
    protected static function boot()
    {
        parent::boot();

        // Envoyer automatiquement les notifications après création
        static::created(function ($enrollment) {
            // Ne pas envoyer si on utilise createAndNotify (éviter double envoi)
            if (! static::$skipNotifications) {
                $enrollment->sendEnrollmentNotifications();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'progress' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'content_id');
    }

    /**
     * Alias pour compatibilité avec le nouveau nom
     */
    public function content(): BelongsTo
    {
        return $this->course();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function accessGrantingSubscription(): BelongsTo
    {
        return $this->belongsTo(UserSubscription::class, 'access_granted_by_subscription_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Créer une inscription et, si demandé, envoyer les notifications et emails par contenu.
     * Mettre $sendEnrollmentCommunications à false pour les cours achetés dans un pack (email/notif pack unique).
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function createAndNotify(array $attributes, bool $sendEnrollmentCommunications = true): self
    {
        static::$skipNotifications = true;

        try {
            $enrollment = static::create($attributes);

            if ($sendEnrollmentCommunications) {
                $enrollment->sendEnrollmentNotifications();
            }

            return $enrollment;
        } finally {
            static::$skipNotifications = false;
        }
    }

    /**
     * Envoyer les notifications et emails d'inscription
     * Cette méthode peut être appelée après la création d'une inscription
     */
    public function sendEnrollmentNotifications(): void
    {
        try {
            // Charger les relations nécessaires
            if (! $this->relationLoaded('course')) {
                $this->load('course');
            }
            if (! $this->relationLoaded('user')) {
                $this->load('user');
            }

            $course = $this->course;
            $user = $this->user;

            if (! $course || ! $user) {
                \Log::warning("Impossible d'envoyer les notifications d'inscription: cours ou utilisateur manquant", [
                    'enrollment_id' => $this->id,
                    'content_id' => $this->content_id,
                    'user_id' => $this->user_id,
                ]);

                return;
            }

            // Charger les relations nécessaires du cours
            if (! $course->relationLoaded('provider')) {
                $course->load('provider');
            }
            if (! $course->relationLoaded('category')) {
                $course->load('category');
            }

            $communicationService = null;
            try {
                $communicationService = app(\App\Services\CommunicationService::class);
            } catch (\Throwable $e) {
                \Log::warning('CommunicationService non disponible, envoi direct par Mail::send()', [
                    'enrollment_id' => $this->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Envoyer l'email d'inscription (synchrone)
            try {
                if (empty($user->email) || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    \Log::error("Email invalide pour l'utilisateur - impossible d'envoyer CourseEnrolledMail", [
                        'enrollment_id' => $this->id,
                        'content_id' => $course->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email ?? null,
                    ]);
                } else {
                    $alreadySent = SentEmail::query()
                        ->where('recipient_email', $user->email)
                        ->where('metadata->mail_class', \App\Mail\CourseEnrolledMail::class)
                        ->where('metadata->content_id', $course->id)
                        ->where('status', 'sent')
                        ->exists();

                    if (! $alreadySent) {
                        $mailable = new \App\Mail\CourseEnrolledMail($course);
                        if ($communicationService) {
                            $communicationService->sendEmailAndWhatsApp($user, $mailable);
                        } else {
                            Mail::to($user->email)->send($mailable);
                        }
                        \Log::info("Email CourseEnrolledMail envoyé à {$user->email} pour le cours {$course->id}", [
                            'enrollment_id' => $this->id,
                            'content_id' => $course->id,
                        ]);
                    } else {
                        \Log::info('CourseEnrolledMail déjà envoyé (déduplication)', [
                            'enrollment_id' => $this->id,
                            'content_id' => $course->id,
                            'user_id' => $user->id,
                        ]);
                    }
                }
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                \Log::error('Erreur SMTP CourseEnrolledMail', [
                    'enrollment_id' => $this->id,
                    'content_id' => $course->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email ?? null,
                    'error' => $e->getMessage(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Erreur envoi CourseEnrolledMail', [
                    'enrollment_id' => $this->id,
                    'content_id' => $course->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Reçu PDF : toujours tenter si conditions remplies (même si l'email d'inscription a échoué)
            $receiptPdfEnabled = \App\Models\Setting::get('receipt_pdf_enabled', true);
            $contentSendsReceipt = ($course->send_receipt_enabled ?? true) !== false;
            $userEmailValid = ! empty($user->email) && filter_var($user->email, FILTER_VALIDATE_EMAIL);

            if (! $userEmailValid) {
                \Log::info('Reçu PDF non envoyé: email utilisateur invalide ou vide', [
                    'enrollment_id' => $this->id,
                    'content_id' => $course->id,
                    'user_id' => $user->id,
                ]);
            } elseif (! $receiptPdfEnabled) {
                \Log::info('Reçu PDF non envoyé: option désactivée globalement (receipt_pdf_enabled)', [
                    'enrollment_id' => $this->id,
                    'content_id' => $course->id,
                ]);
            } elseif (! $contentSendsReceipt) {
                \Log::info('Reçu PDF non envoyé: option désactivée pour ce contenu (send_receipt_enabled)', [
                    'enrollment_id' => $this->id,
                    'content_id' => $course->id,
                    'send_receipt_enabled' => $course->send_receipt_enabled,
                ]);
            } else {
                try {
                    \Log::info("Tentative envoi reçu PDF d'inscription", [
                        'enrollment_id' => $this->id,
                        'content_id' => $course->id,
                        'user_email' => $user->email,
                    ]);
                    $receiptService = app(\App\Services\EnrollmentReceiptPdfService::class);
                    $pdfContent = $receiptService->generatePdfContent($this);
                    if ($pdfContent === '' || strlen($pdfContent) < 100) {
                        throw new \RuntimeException('Le contenu du PDF généré est vide ou invalide (taille: '.strlen($pdfContent).' octets).');
                    }
                    $receiptMail = new \App\Mail\EnrollmentReceiptMail($course, $pdfContent);
                    if ($communicationService) {
                        $communicationService->sendEmailAndWhatsApp($user, $receiptMail, null, false);
                    } else {
                        Mail::to($user->email)->send($receiptMail);
                    }
                    \Log::info("Reçu PDF d'inscription envoyé à {$user->email} pour le cours {$course->id}", [
                        'enrollment_id' => $this->id,
                        'content_id' => $course->id,
                    ]);
                } catch (\Throwable $receiptException) {
                    \Log::error("Erreur envoi reçu PDF d'inscription", [
                        'enrollment_id' => $this->id,
                        'content_id' => $course->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email ?? null,
                        'error' => $receiptException->getMessage(),
                        'exception_class' => get_class($receiptException),
                        'trace' => $receiptException->getTraceAsString(),
                    ]);
                }
            }

            // Envoyer la notification (pour la base de données et l'affichage dans la navbar)
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            try {
                Notification::sendNow($user, new CourseEnrolled($course));

                \Log::info("Notification CourseEnrolled envoyée à l'utilisateur {$user->id} pour le cours {$course->id}", [
                    'enrollment_id' => $this->id,
                    'content_id' => $course->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $notifException) {
                \Log::error("Erreur lors de l'envoi de la notification CourseEnrolled", [
                    'enrollment_id' => $this->id,
                    'content_id' => $course->id,
                    'user_id' => $user->id,
                    'error' => $notifException->getMessage(),
                    'trace' => $notifException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer l'inscription
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi de la notification d'inscription", [
                'enrollment_id' => $this->id,
                'content_id' => $this->content_id,
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
