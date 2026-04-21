<?php

namespace App\Services;

use App\Mail\ContentRatingRequestMail;
use App\Models\ContentRatingReminder;
use App\Models\Course;
use App\Models\CourseDownload;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class ContentRatingReminderService
{
    public function __construct(
        protected ReviewEligibilityService $reviewEligibility
    ) {}

    public function ensureForEnrollment(Enrollment $enrollment): void
    {
        if (! in_array($enrollment->status, ['active', 'completed'], true)) {
            return;
        }

        $userId = $enrollment->user_id;
        $contentId = $enrollment->content_id;
        if (! $userId || ! $contentId) {
            return;
        }

        if (Review::where('user_id', $userId)->where('content_id', $contentId)->exists()) {
            return;
        }

        $reminder = ContentRatingReminder::firstOrCreate(
            ['user_id' => $userId, 'content_id' => $contentId],
            [
                'enrollment_id' => $enrollment->id,
                // Démarre la campagne au moment où la relance est effectivement mise en place.
                'campaign_started_at' => now(),
            ]
        );

        $this->reviveLegacyCampaignIfNeeded($reminder, $enrollment->id);
    }

    public function ensureForDownload(CourseDownload $download): void
    {
        $course = $download->course;
        if (! $course || ! $course->is_downloadable || ! $course->is_free) {
            return;
        }

        if (Review::where('user_id', $download->user_id)->where('content_id', $course->id)->exists()) {
            return;
        }

        $reminder = ContentRatingReminder::firstOrCreate(
            ['user_id' => $download->user_id, 'content_id' => $course->id],
            [
                'enrollment_id' => null,
                // Démarre la campagne au moment où la relance est effectivement mise en place.
                'campaign_started_at' => now(),
            ]
        );

        $this->reviveLegacyCampaignIfNeeded($reminder, null);
    }

    public static function forgetForUserAndContent(int $userId, int $contentId): void
    {
        ContentRatingReminder::where('user_id', $userId)
            ->where('content_id', $contentId)
            ->delete();
    }

    /**
     * Lien signé (ex. envoi manuel par l’admin), valable 30 jours.
     */
    public function makeSignedInviteUrl(User $user, Course $course): string
    {
        return URL::temporarySignedRoute(
            'rating.invite',
            now()->addDays(30),
            ['course' => $course->slug, 'user' => $user->id]
        );
    }

    public function sendDueReminders(): int
    {
        $sent = 0;

        // Jusqu’à 9 envois sur 3 jours (3× / jour), espacés d’au moins MIN_HOURS_BETWEEN_REMINDERS, tant que la campagne est active et qu’il n’y a pas d’avis.
        $minHours = ContentRatingReminder::MIN_HOURS_BETWEEN_REMINDERS;
        $query = ContentRatingReminder::query()
            ->with(['user', 'course', 'enrollment'])
            ->where('reminders_sent', '<', ContentRatingReminder::MAX_REMINDERS)
            ->where('campaign_started_at', '>=', now()->subDays(ContentRatingReminder::CAMPAIGN_DAYS)->startOfDay())
            ->where(function ($q) use ($minHours) {
                $q->whereNull('last_sent_at')
                    ->orWhere('last_sent_at', '<=', now()->subHours($minHours));
            });

        foreach ($query->cursor() as $reminder) {
            if (! $reminder->isCampaignActive()) {
                continue;
            }

            if (Review::where('user_id', $reminder->user_id)
                ->where('content_id', $reminder->content_id)
                ->exists()) {
                $reminder->delete();

                continue;
            }

            $user = $reminder->user;
            $course = $reminder->course;
            if (! $user || ! $course) {
                continue;
            }

            $eligibility = $this->reviewEligibility->evaluate($user, $course);
            if (! $eligibility['can_review']) {
                continue;
            }

            if (empty($user->email) || ! filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Relance notation: email utilisateur invalide', [
                    'user_id' => $user->id,
                    'content_id' => $course->id,
                ]);

                continue;
            }

            $ratingUrl = route('contents.rate', $course);
            $usePurchaseWording = $this->reminderUsesPurchaseWording($reminder);

            try {
                $communicationService = null;
                try {
                    $communicationService = app(CommunicationService::class);
                } catch (\Throwable) {
                }

                $mailable = new ContentRatingRequestMail($user, $course, $ratingUrl, $usePurchaseWording);
                if ($communicationService) {
                    $communicationService->sendEmailAndWhatsApp($user, $mailable, null, false);
                } else {
                    Mail::to($user->email)->send($mailable);
                }

                $reminder->forceFill([
                    'reminders_sent' => $reminder->reminders_sent + 1,
                    'last_sent_at' => now(),
                ])->save();

                $sent++;
            } catch (\Throwable $e) {
                Log::error('Relance notation: échec envoi email', [
                    'user_id' => $user->id,
                    'content_id' => $course->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Libellés « achat / acheteur » uniquement si l’accès provient d’une commande (order_id sur l’inscription).
     */
    private function reminderUsesPurchaseWording(ContentRatingReminder $reminder): bool
    {
        if (! $reminder->enrollment_id) {
            return false;
        }

        $enrollment = $reminder->relationLoaded('enrollment')
            ? $reminder->enrollment
            : Enrollment::find($reminder->enrollment_id);

        return $enrollment && $enrollment->order_id !== null;
    }

    /**
     * Réactive les anciennes campagnes jamais envoyées pour couvrir les accès
     * accordés avant la mise en place complète de l’automatisation.
     */
    private function reviveLegacyCampaignIfNeeded(ContentRatingReminder $reminder, ?int $enrollmentId): void
    {
        if ($reminder->reminders_sent > 0) {
            return;
        }

        if ($reminder->isCampaignActive()) {
            return;
        }

        $payload = [
            'campaign_started_at' => now(),
            'last_sent_at' => null,
        ];

        if ($enrollmentId !== null && $reminder->enrollment_id === null) {
            $payload['enrollment_id'] = $enrollmentId;
        }

        $reminder->forceFill($payload)->save();
    }
}
