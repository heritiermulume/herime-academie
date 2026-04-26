<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ContentRatingReminder extends Model
{
    /** Un seul email de demande d'avis par accès au contenu. */
    public const MAX_REMINDERS = 1;

    /** Délai avant envoi du mail de demande d'avis (achat ou accès gratuit). */
    public const FIRST_REMINDER_DELAY_HOURS = 24;

    protected $fillable = [
        'user_id',
        'content_id',
        'enrollment_id',
        'campaign_started_at',
        'reminders_sent',
        'last_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'campaign_started_at' => 'datetime',
            'last_sent_at' => 'datetime',
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

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function campaignEndsAt(): Carbon
    {
        return $this->campaign_started_at->copy();
    }

    public function isCampaignActive(): bool
    {
        return now()->lte($this->campaignEndsAt());
    }
}
