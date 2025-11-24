<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Notifications\CourseEnrolled;
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
        'course_id',
        'order_id',
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
            if (!static::$skipNotifications) {
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
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
     * Créer une inscription et envoyer automatiquement les notifications et emails
     * 
     * @param array $attributes Les attributs de l'inscription
     * @return Enrollment L'inscription créée
     */
    public static function createAndNotify(array $attributes): self
    {
        // Désactiver temporairement l'événement created pour éviter double envoi
        static::$skipNotifications = true;
        
        try {
            // Créer l'inscription (l'événement created sera ignoré)
            $enrollment = static::create($attributes);

            // Envoyer les notifications et emails manuellement
            $enrollment->sendEnrollmentNotifications();

            return $enrollment;
        } finally {
            // Réactiver les notifications pour les prochaines créations
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
            if (!$this->relationLoaded('course')) {
                $this->load('course');
            }
            if (!$this->relationLoaded('user')) {
                $this->load('user');
            }

            $course = $this->course;
            $user = $this->user;

            if (!$course || !$user) {
                \Log::warning("Impossible d'envoyer les notifications d'inscription: cours ou utilisateur manquant", [
                    'enrollment_id' => $this->id,
                    'course_id' => $this->course_id,
                    'user_id' => $this->user_id,
                ]);
                return;
            }

            // Charger les relations nécessaires du cours
            if (!$course->relationLoaded('instructor')) {
                $course->load('instructor');
            }
            if (!$course->relationLoaded('category')) {
                $course->load('category');
            }

            // Envoyer l'email directement de manière synchrone pour garantir l'envoi immédiat
            try {
                // Vérifier que l'email de l'utilisateur est valide
                if (empty($user->email) || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    \Log::error("Email invalide pour l'utilisateur - impossible d'envoyer CourseEnrolledMail", [
                        'enrollment_id' => $this->id,
                        'course_id' => $course->id,
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                    ]);
                    return;
                }

                // Envoyer l'email et capturer les erreurs SMTP
                $mailable = new \App\Mail\CourseEnrolledMail($course);
                Mail::to($user->email)->send($mailable);
                
                \Log::info("Email CourseEnrolledMail envoyé avec succès à {$user->email} pour le cours {$course->id}", [
                    'enrollment_id' => $this->id,
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'course_title' => $course->title,
                ]);
            } catch (\Swift_TransportException $transportException) {
                // Erreur de transport SMTP (connexion, authentification, etc.)
                \Log::error("Erreur SMTP lors de l'envoi de l'email CourseEnrolledMail", [
                    'enrollment_id' => $this->id,
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $transportException->getMessage(),
                    'error_code' => $transportException->getCode(),
                    'trace' => $transportException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer l'inscription
            } catch (\Exception $emailException) {
                // Autres erreurs (validation, template, etc.)
                \Log::error("Erreur lors de l'envoi de l'email CourseEnrolledMail", [
                    'enrollment_id' => $this->id,
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $emailException->getMessage(),
                    'error_class' => get_class($emailException),
                    'trace' => $emailException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer l'inscription
            } catch (\Throwable $throwable) {
                // Capturer toutes les erreurs fatales
                \Log::error("Erreur fatale lors de l'envoi de l'email CourseEnrolledMail", [
                    'enrollment_id' => $this->id,
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'error' => $throwable->getMessage(),
                    'error_class' => get_class($throwable),
                    'trace' => $throwable->getTraceAsString(),
                ]);
            }
            
            // Envoyer la notification (pour la base de données et l'affichage dans la navbar)
            // Utiliser sendNow() pour envoyer immédiatement sans passer par la queue
            try {
                Notification::sendNow($user, new CourseEnrolled($course));
                
                \Log::info("Notification CourseEnrolled envoyée à l'utilisateur {$user->id} pour le cours {$course->id}", [
                    'enrollment_id' => $this->id,
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
            } catch (\Exception $notifException) {
                \Log::error("Erreur lors de l'envoi de la notification CourseEnrolled", [
                    'enrollment_id' => $this->id,
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'error' => $notifException->getMessage(),
                    'trace' => $notifException->getTraceAsString(),
                ]);
                // Ne pas relancer l'exception pour ne pas bloquer l'inscription
            }
        } catch (\Exception $e) {
            \Log::error("Erreur lors de l'envoi de la notification d'inscription", [
                'enrollment_id' => $this->id,
                'course_id' => $this->course_id,
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
