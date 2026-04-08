<?php

namespace App\Models;

use App\Models\Pivots\SubscriptionPlanContentPivot;
use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubscriptionPlan extends Model
{
    /**
     * Offre « Membre Herime » : trois plans distincts (facturation), édités ensemble dans l’admin.
     *
     * @var array<string, string> période interne => slug
     */
    public const MEMBER_COMMUNITY_SLUGS = [
        'quarterly' => 'membre-herime-trimestriel',
        'semiannual' => 'membre-herime-semestriel',
        'yearly' => 'membre-herime-annuel',
    ];

    /** @var array<string, string> */
    public const MEMBER_COMMUNITY_PERIOD_LABELS = [
        'quarterly' => 'Trimestriel',
        'semiannual' => 'Semestriel',
        'yearly' => 'Annuel',
        'all' => 'Toutes périodes (Membre)',
    ];

    /**
     * Ordre des périodes Membre Hérimé pour une exigence « minimale » sur un contenu.
     * Inclut les anciennes valeurs admin (starter/pro/enterprise) pour rétrocompatibilité.
     *
     * @return array<string, int>
     */
    public static function memberPeriodRequirementRanks(): array
    {
        return [
            'quarterly' => 1,
            'semiannual' => 2,
            'yearly' => 3,
            'all' => 1,
            'any' => 1,
            'starter' => 1,
            'pro' => 2,
            'enterprise' => 3,
        ];
    }

    /**
     * Rang exigé par un contenu à partir de `contents.required_subscription_tier`.
     * `all` / `any` : toute période Membre (trimestriel, semestriel ou annuel) suffit — équivalent au rang minimal.
     */
    public static function requiredMemberPeriodRankFromStored(?string $stored): int
    {
        $key = strtolower(trim((string) $stored));
        $ranks = self::memberPeriodRequirementRanks();

        return $ranks[$key] ?? 1;
    }

    /**
     * La période de facturation du plan suffit-elle pour un contenu avec exigence d’abonnement Membre ?
     */
    public static function planMatchesCourseMemberPeriod(?self $plan, Course $course): bool
    {
        if (! $course->requires_subscription || $course->is_free) {
            return true;
        }

        $requiredRank = self::requiredMemberPeriodRankFromStored($course->required_subscription_tier);

        return self::subscriptionAccessRankForPlan($plan) >= $requiredRank;
    }

    /**
     * Accès au contenu réservé aux abonnés : période Membre suffisante ou achat standalone.
     */
    public static function userMeetsMemberPeriodForSubscriptionGatedContent(User $user, Course $course): bool
    {
        if (! $course->requires_subscription || $course->is_free) {
            return true;
        }

        if ($course->userHasValidStandalonePurchase($user->id)) {
            return true;
        }

        $requiredRank = self::requiredMemberPeriodRankFromStored($course->required_subscription_tier);
        $activeSubscriptions = $user->activeSubscriptions()->with('plan')->get();

        if ($activeSubscriptions->isEmpty()) {
            return false;
        }

        return $activeSubscriptions->contains(function ($subscription) use ($requiredRank) {
            return self::subscriptionAccessRankForPlan($subscription->plan) >= $requiredRank;
        });
    }

    /**
     * Rang d’accès fourni par le plan d’un abonnement actif (période de facturation ou ancien metadata `tier`).
     */
    public static function subscriptionAccessRankForPlan(?self $plan): int
    {
        if (! $plan) {
            // Aligné sur l’ancienne logique (metadata tier par défaut « starter » / rang minimal).
            return 1;
        }

        $ranks = self::memberPeriodRequirementRanks();
        $bp = strtolower((string) $plan->billing_period);
        if (isset($ranks[$bp])) {
            return $ranks[$bp];
        }

        $tier = strtolower((string) data_get($plan->metadata, 'tier', ''));
        if ($tier !== '' && isset($ranks[$tier])) {
            return $ranks[$tier];
        }

        return 1;
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'plan_type',
        'billing_period',
        'price',
        'annual_discount_percent',
        'trial_days',
        'is_active',
        'auto_renew_default',
        'content_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'annual_discount_percent' => 'decimal:2',
            'trial_days' => 'integer',
            'is_active' => 'boolean',
            'auto_renew_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SubscriptionPlan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });

        static::saved(function (SubscriptionPlan $plan) {
            if ($plan->wasChanged(['metadata', 'content_id'])) {
                SubscriptionService::deferEntitlementSyncForPlan((int) $plan->id);
            }
        });
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'content_id');
    }

    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'subscription_plan_content', 'subscription_plan_id', 'content_id')
            ->using(SubscriptionPlanContentPivot::class)
            ->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        if ($this->billing_period === 'yearly' && $this->annual_discount_percent > 0) {
            return (float) $this->price * (1 - ((float) $this->annual_discount_percent / 100));
        }

        return (float) $this->price;
    }

    public function effectivePriceForCurrency(?string $currency): float
    {
        $currency = strtoupper((string) $currency);
        $localized = data_get($this->metadata, "localized_pricing.{$currency}.amount");

        if ($localized !== null) {
            return (float) $localized;
        }

        return $this->effective_price;
    }

    /**
     * Abonnement « Membre Herime » / communauté : exclu des listes publiques d’abonnements.
     */
    public function isCommunityPremiumPlan(): bool
    {
        return filter_var(data_get($this->metadata, 'community_premium'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Pastille « populaire » sur la page adhésion communauté (une seule période à la fois, réglée en admin).
     */
    public function isCommunityCardPopular(): bool
    {
        return filter_var(data_get($this->metadata, 'community_card_popular'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Facturation par période (mensuel / semestriel / annuel), essais et renouvellements automatiques.
     */
    public function usesRecurringBilling(): bool
    {
        if (in_array($this->plan_type, ['recurring', 'premium', 'membre'], true)) {
            return true;
        }

        if (! $this->isCommunityPremiumPlan()) {
            return false;
        }

        if (self::isMemberCommunitySlug((string) $this->slug)) {
            return true;
        }

        return in_array($this->billing_period, ['quarterly', 'semiannual', 'yearly'], true);
    }

    /**
     * @return array<int, string>
     */
    public static function memberCommunitySlugList(): array
    {
        return array_values(self::MEMBER_COMMUNITY_SLUGS);
    }

    public static function isMemberCommunitySlug(string $slug): bool
    {
        return in_array($slug, self::memberCommunitySlugList(), true);
    }

    /**
     * Préfixe « nom de l’offre » sans le suffixe période (ex. « — Trimestriel »), aligné sur le champ du formulaire bundle admin.
     */
    public function memberOfferBaseName(): string
    {
        if (! self::isMemberCommunitySlug((string) $this->slug)) {
            return (string) $this->name;
        }

        $stripped = preg_replace('/\s—\s.+$/u', '', (string) $this->name);

        return $stripped !== '' ? $stripped : $this->name;
    }

    /**
     * Résout la ligne en base pour une période du bundle : slug canonique d’abord, sinon plan communauté premium
     * avec la même billing_period (utile si les slugs n’ont pas encore été normalisés).
     */
    public static function resolveMemberBundlePlanForPeriod(string $period): ?self
    {
        $canonical = self::MEMBER_COMMUNITY_SLUGS[$period] ?? null;
        if ($canonical === null) {
            return null;
        }

        $bySlug = self::query()->where('slug', $canonical)->orderBy('id')->first();
        if ($bySlug) {
            return $bySlug;
        }

        $billing = match ($period) {
            'quarterly' => 'quarterly',
            'semiannual' => 'semiannual',
            'yearly' => 'yearly',
            default => null,
        };
        if ($billing === null) {
            return null;
        }

        $candidates = self::query()
            ->where('billing_period', $billing)
            ->orderBy('id')
            ->get()
            ->filter(fn (self $p) => $p->isCommunityPremiumPlan())
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        $expectedOrder = match ($period) {
            'quarterly' => 0,
            'semiannual' => 1,
            'yearly' => 2,
            default => 99,
        };

        $byOrder = $candidates->first(
            fn (self $p) => (int) data_get($p->metadata, 'community_display_order', 99) === $expectedOrder
        );

        return $byOrder ?? $candidates->first();
    }

    /**
     * La ligne peut être modifiée via l’écran bundle (slug canonique ou plan communauté reconnu pour une période).
     */
    public static function allowsAdminMemberBundleManagement(self $plan): bool
    {
        foreach (array_keys(self::MEMBER_COMMUNITY_SLUGS) as $period) {
            $resolved = self::resolveMemberBundlePlanForPeriod($period);
            if ($resolved && (int) $resolved->id === (int) $plan->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    public static function memberBundlePlanIds(): array
    {
        $ids = [];
        foreach (array_keys(self::MEMBER_COMMUNITY_SLUGS) as $period) {
            $p = self::resolveMemberBundlePlanForPeriod($period);
            if ($p) {
                $ids[] = (int) $p->id;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Même jeu de lignes (rechargé depuis la DB) pour la liste admin et l’édition bundle — évite un décalage
     * entre les deux écrans (ordre, attributs, relations).
     *
     * @return array{
     *     slots: \Illuminate\Support\Collection<string, ?self>,
     *     plansOrdered: \Illuminate\Support\Collection<int, self>,
     *     listRows: \Illuminate\Support\Collection<int, array{period: string, period_label: string, plan: ?self}>
     * }
     */
    public static function adminMemberBundleContext(): array
    {
        $periods = ['quarterly', 'semiannual', 'yearly'];

        $rowsBySlug = self::query()
            ->whereIn('slug', self::memberCommunitySlugList())
            ->with(['content', 'contents'])
            ->get()
            ->keyBy(fn (self $p) => (string) $p->slug);

        $slots = collect();
        foreach (self::MEMBER_COMMUNITY_SLUGS as $period => $slug) {
            $plan = $rowsBySlug->get($slug);
            if (! $plan) {
                $plan = self::resolveMemberBundlePlanForPeriod($period);
            }
            $slots->put($period, $plan);
        }

        $plansOrdered = collect($periods)
            ->map(fn (string $period) => $slots->get($period))
            ->filter()
            ->values();

        $listRows = collect($periods)->map(fn (string $period) => [
            'period' => $period,
            'period_label' => self::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period,
            'plan' => $slots->get($period),
        ])->values();

        return [
            'slots' => $slots,
            'plansOrdered' => $plansOrdered,
            'listRows' => $listRows,
        ];
    }

    /**
     * Plans actifs « Membre Herime » (slugs fixes), triés pour l’affichage communauté / espace client.
     *
     * @return \Illuminate\Support\Collection<int, self>
     */
    public static function activeMemberCommunityPlans(): \Illuminate\Support\Collection
    {
        $ids = [];
        foreach (array_keys(self::MEMBER_COMMUNITY_SLUGS) as $period) {
            $p = self::resolveMemberBundlePlanForPeriod($period);
            if ($p && $p->is_active) {
                $ids[] = (int) $p->id;
            }
        }

        if ($ids === []) {
            return collect();
        }

        return self::query()
            ->whereIn('id', $ids)
            ->with(['content', 'contents'])
            ->get()
            ->sortBy(fn (self $plan) => (int) data_get($plan->metadata, 'community_display_order', 99))
            ->values();
    }
}
