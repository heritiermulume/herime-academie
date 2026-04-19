<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class ContentRatingReminder extends Model
{
    public const MAX_REMINDERS = 6;

    public const CAMPAIGN_DAYS = 3;

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
        return $this->campaign_started_at->copy()->addDays(self::CAMPAIGN_DAYS);
    }

    public function isCampaignActive(): bool
    {
        return now()->lte($this->campaignEndsAt());
    }
}
