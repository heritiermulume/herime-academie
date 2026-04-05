<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPackage;
use App\Models\SubscriptionPlan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $page = max(1, (int) request()->get('page', 1));
        $perPage = 15;

        $ctx = SubscriptionPlan::adminMemberBundleContext();
        $hasAnyMemberPlan = $ctx['slots']->filter()->isNotEmpty();
        $listRows = $hasAnyMemberPlan ? $ctx['listRows'] : collect();

        $plans = new LengthAwarePaginator(
            $listRows->forPage($page, $perPage)->values(),
            $listRows->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $includedPackageIds = $ctx['slots']->filter()
            ->flatMap(function (SubscriptionPlan $plan) {
                $m = $plan->metadata;
                if (! is_array($m)) {
                    return [];
                }

                return collect(data_get($m, 'included_package_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->all();
            })
            ->unique()
            ->values()
            ->all();
        $includedPackagesById = ContentPackage::query()
            ->whereIn('id', $includedPackageIds)
            ->get()
            ->keyBy('id');

        return view('admin.subscriptions.plans.index', compact(
            'plans',
            'includedPackagesById',
            'hasAnyMemberPlan'
        ));
    }

    public function create()
    {
        if ($this->memberBundleHasAnyResolvedPlan()) {
            return redirect()->route('admin.subscriptions.plans.index')
                ->with('info', 'L’offre Membre Herime existe déjà. Utilisez « Modifier » pour la mettre à jour.');
        }

        return view('admin.subscriptions.plans.create');
    }

    public function store(Request $request)
    {
        return $this->storeMemberCommunityBundle($request);
    }

    /**
     * Édition unique du bundle (mêmes données que la liste, sans dépendre d’un id de ligne).
     */
    public function editMembreHerime()
    {
        $ctx = SubscriptionPlan::adminMemberBundleContext();
        if ($ctx['plansOrdered']->isEmpty()) {
            return redirect()->route('admin.subscriptions.plans.index')
                ->with('error', 'Aucune formule Membre Herime à modifier. Créez l’offre d’abord.');
        }

        $memberBundlePlans = collect();
        foreach (SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $slug) {
            if ($p = $ctx['slots']->get($period)) {
                $memberBundlePlans->put($slug, $p);
            }
        }

        return view('admin.subscriptions.plans.edit', compact('memberBundlePlans'));
    }

    public function updateMembreHerime(Request $request)
    {
        if (SubscriptionPlan::memberBundlePlanIds() === []) {
            return redirect()->route('admin.subscriptions.plans.index')
                ->with('error', 'Aucune formule Membre Herime à mettre à jour.');
        }

        return $this->updateMemberCommunityBundle($request);
    }

    public function edit(SubscriptionPlan $plan)
    {
        if (SubscriptionPlan::allowsAdminMemberBundleManagement($plan)) {
            return redirect()->route('admin.subscriptions.plans.membre.edit');
        }

        return redirect()->route('admin.subscriptions.plans.index')
            ->with('error', 'Seuls les plans Membre Herime peuvent être modifiés.');
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        if (SubscriptionPlan::allowsAdminMemberBundleManagement($plan)) {
            return $this->updateMemberCommunityBundle($request);
        }

        return redirect()->route('admin.subscriptions.plans.index')
            ->with('error', 'Seuls les plans Membre Herime peuvent être modifiés.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if (! SubscriptionPlan::allowsAdminMemberBundleManagement($plan)) {
            return redirect()->route('admin.subscriptions.plans.index')
                ->with('error', 'Seuls les plans Membre Herime sont gérés ici.');
        }

        $ids = SubscriptionPlan::memberBundlePlanIds();
        if ($ids !== []) {
            SubscriptionPlan::query()->whereIn('id', $ids)->delete();
        }

        return redirect()->route('admin.subscriptions.plans.index')
            ->with('success', 'Les trois formules Membre Herime ont été supprimées.');
    }

    /**
     * Au moins une période du bundle est déjà présente en base (slug canonique ou résolution legacy).
     */
    private function memberBundleHasAnyResolvedPlan(): bool
    {
        foreach (array_keys(SubscriptionPlan::MEMBER_COMMUNITY_SLUGS) as $period) {
            if (SubscriptionPlan::resolveMemberBundlePlanForPeriod($period)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ligne à mettre à jour : slug canonique d’abord (évite d’écrire 3× sur la même ligne si les ids cachés sont dupliqués),
     * puis id du formulaire (slug ou période + communauté), puis résolution legacy.
     */
    private function memberBundlePlanRowForUpdate(Request $request, string $period, string $canonicalSlug): ?SubscriptionPlan
    {
        $bySlug = SubscriptionPlan::query()->where('slug', $canonicalSlug)->orderBy('id')->first();
        if ($bySlug) {
            return $bySlug;
        }

        $expectedBilling = match ($period) {
            'quarterly' => 'quarterly',
            'semiannual' => 'semiannual',
            'yearly' => 'yearly',
            default => null,
        };

        $raw = $request->input('membre_plan_ids');
        if (is_array($raw) && array_key_exists($period, $raw)) {
            $id = (int) $raw[$period];
            if ($id > 0) {
                $plan = SubscriptionPlan::query()->find($id);
                if ($plan) {
                    if ((string) $plan->slug === $canonicalSlug) {
                        return $plan;
                    }
                    if ($expectedBilling
                        && (string) $plan->billing_period === $expectedBilling
                        && $plan->isCommunityPremiumPlan()) {
                        return $plan;
                    }
                }
            }
        }

        return SubscriptionPlan::resolveMemberBundlePlanForPeriod($period);
    }

    private function normalizeMemberBundlePriceInputs(Request $request): void
    {
        foreach (['membre_price_quarterly', 'membre_price_semiannual', 'membre_price_yearly'] as $key) {
            $v = $request->input($key);
            if ($v === null || $v === '') {
                continue;
            }
            if (is_string($v)) {
                $normalized = str_replace(["\xc2\xa0", ' '], '', $v);
                $normalized = str_replace(',', '.', $normalized);
                $request->merge([$key => $normalized]);
            }
        }
    }

    private function normalizeMemberBundleTrialInputs(Request $request): void
    {
        $trial = $request->input('membre_trial_days');
        if (! is_array($trial)) {
            return;
        }
        foreach (array_keys(SubscriptionPlan::MEMBER_COMMUNITY_SLUGS) as $period) {
            $v = $trial[$period] ?? null;
            if ($v === null || $v === '') {
                $trial[$period] = '0';
            }
        }
        $request->merge(['membre_trial_days' => $trial]);
    }

    private function storeMemberCommunityBundle(Request $request)
    {
        $this->normalizeMemberBundlePriceInputs($request);
        $this->normalizeMemberBundleTrialInputs($request);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'membre_price_quarterly' => ['required', 'numeric', 'min:0'],
            'membre_price_semiannual' => ['required', 'numeric', 'min:0'],
            'membre_price_yearly' => ['required', 'numeric', 'min:0'],
            'membre_highlight_quarterly' => ['nullable', 'string', 'max:2000'],
            'membre_highlight_semiannual' => ['nullable', 'string', 'max:2000'],
            'membre_highlight_yearly' => ['nullable', 'string', 'max:2000'],
            'membre_popular_period' => ['nullable', 'in:quarterly,semiannual,yearly'],
            'membre_trial_days' => ['required', 'array'],
            'membre_trial_days.quarterly' => ['nullable', 'integer', 'min:0', 'max:365'],
            'membre_trial_days.semiannual' => ['nullable', 'integer', 'min:0', 'max:365'],
            'membre_trial_days.yearly' => ['nullable', 'integer', 'min:0', 'max:365'],
            'is_active' => ['nullable', 'boolean'],
            'auto_renew_default' => ['nullable', 'boolean'],
        ]);

        foreach (array_keys(SubscriptionPlan::MEMBER_COMMUNITY_SLUGS) as $period) {
            if (SubscriptionPlan::resolveMemberBundlePlanForPeriod($period)) {
                return redirect()->route('admin.subscriptions.plans.index')
                    ->with('error', 'Les formules Membre Herime existent déjà. Modifiez-les depuis la liste (n’importe quelle ligne « Membre »).');
            }
        }

        $baseName = trim((string) $request->input('name'));
        $description = $request->input('description');
        $trialMap = $this->memberBundleTrialMapFromRequest($request);
        $isActive = $request->boolean('is_active');
        $autoRenew = $request->boolean('auto_renew_default');

        $priceMap = [
            'quarterly' => (float) $request->input('membre_price_quarterly'),
            'semiannual' => (float) $request->input('membre_price_semiannual'),
            'yearly' => (float) $request->input('membre_price_yearly'),
        ];
        $highlightMap = [
            'quarterly' => trim((string) $request->input('membre_highlight_quarterly', '')),
            'semiannual' => trim((string) $request->input('membre_highlight_semiannual', '')),
            'yearly' => trim((string) $request->input('membre_highlight_yearly', '')),
        ];
        $billingMap = [
            'quarterly' => 'quarterly',
            'semiannual' => 'semiannual',
            'yearly' => 'yearly',
        ];
        $orderMap = [
            'quarterly' => 0,
            'semiannual' => 1,
            'yearly' => 2,
        ];
        $popularPeriod = $this->resolveMembrePopularPeriod($request);

        foreach (SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $slug) {
            SubscriptionPlan::query()->create(
                $this->memberBundlePlanAttributesForPeriod(
                    $period,
                    $slug,
                    $baseName,
                    $description,
                    $trialMap,
                    $isActive,
                    $autoRenew,
                    $priceMap,
                    $highlightMap,
                    $billingMap,
                    $orderMap,
                    $popularPeriod
                )
            );
        }

        return redirect()->route('admin.subscriptions.plans.index')
            ->with('success', 'Offre Membre Herime créée (trimestre, semestre, année).');
    }

    private function updateMemberCommunityBundle(Request $request)
    {
        $this->normalizeMemberBundlePriceInputs($request);
        $this->normalizeMemberBundleTrialInputs($request);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'membre_price_quarterly' => ['required', 'numeric', 'min:0'],
            'membre_price_semiannual' => ['required', 'numeric', 'min:0'],
            'membre_price_yearly' => ['required', 'numeric', 'min:0'],
            'membre_highlight_quarterly' => ['nullable', 'string', 'max:2000'],
            'membre_highlight_semiannual' => ['nullable', 'string', 'max:2000'],
            'membre_highlight_yearly' => ['nullable', 'string', 'max:2000'],
            'membre_popular_period' => ['nullable', 'in:quarterly,semiannual,yearly'],
            'membre_trial_days' => ['required', 'array'],
            'membre_trial_days.quarterly' => ['nullable', 'integer', 'min:0', 'max:365'],
            'membre_trial_days.semiannual' => ['nullable', 'integer', 'min:0', 'max:365'],
            'membre_trial_days.yearly' => ['nullable', 'integer', 'min:0', 'max:365'],
            'is_active' => ['nullable', 'boolean'],
            'auto_renew_default' => ['nullable', 'boolean'],
        ]);

        $baseName = trim((string) $request->input('name'));
        $description = $request->input('description');
        $trialMap = $this->memberBundleTrialMapFromRequest($request);
        $isActive = $request->boolean('is_active');
        $autoRenew = $request->boolean('auto_renew_default');
        $popularPeriod = $this->resolveMembrePopularPeriod($request);

        $priceMap = [
            'quarterly' => (float) $request->input('membre_price_quarterly'),
            'semiannual' => (float) $request->input('membre_price_semiannual'),
            'yearly' => (float) $request->input('membre_price_yearly'),
        ];
        $highlightMap = [
            'quarterly' => trim((string) $request->input('membre_highlight_quarterly', '')),
            'semiannual' => trim((string) $request->input('membre_highlight_semiannual', '')),
            'yearly' => trim((string) $request->input('membre_highlight_yearly', '')),
        ];

        $billingMap = [
            'quarterly' => 'quarterly',
            'semiannual' => 'semiannual',
            'yearly' => 'yearly',
        ];
        $orderMap = [
            'quarterly' => 0,
            'semiannual' => 1,
            'yearly' => 2,
        ];

        $updatedCount = 0;
        try {
            foreach (SubscriptionPlan::MEMBER_COMMUNITY_SLUGS as $period => $canonicalSlug) {
                $p = $this->memberBundlePlanRowForUpdate($request, $period, $canonicalSlug);
                if (! $p) {
                    $p = SubscriptionPlan::query()->create(
                        $this->memberBundlePlanAttributesForPeriod(
                            $period,
                            $canonicalSlug,
                            $baseName,
                            $description,
                            $trialMap,
                            $isActive,
                            $autoRenew,
                            $priceMap,
                            $highlightMap,
                            $billingMap,
                            $orderMap,
                            $popularPeriod
                        )
                    );
                    $p->contents()->sync([]);
                    $updatedCount++;

                    continue;
                }

                $label = SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period;
                $meta = is_array($p->metadata) ? $p->metadata : [];
                $meta['community_premium'] = true;
                $meta['community_display_order'] = $orderMap[$period] ?? 99;
                $meta['label'] = $label;
                if ($highlightMap[$period] !== '') {
                    $meta['community_card_highlight'] = $highlightMap[$period];
                } else {
                    unset($meta['community_card_highlight']);
                }
                $meta['community_card_popular'] = $period === $popularPeriod;

                $payload = [
                    'name' => $baseName.' — '.$label,
                    'description' => $description,
                    'plan_type' => $this->memberPlanTypeForStorage(),
                    'billing_period' => $billingMap[$period],
                    'price' => $priceMap[$period],
                    'annual_discount_percent' => 0,
                    'trial_days' => $trialMap[$period] ?? 0,
                    'is_active' => $isActive,
                    'auto_renew_default' => $autoRenew,
                    'content_id' => null,
                    'metadata' => $meta,
                ];

                if ($p->slug !== $canonicalSlug
                    && ! SubscriptionPlan::query()->where('slug', $canonicalSlug)->whereKeyNot($p->getKey())->exists()) {
                    $payload['slug'] = $canonicalSlug;
                }

                $p->fill($payload);
                $p->save();
                $p->contents()->sync([]);
                $updatedCount++;
            }

            if ($updatedCount === 0) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Aucune ligne de plan n’a pu être mise à jour. Vérifiez que les slugs membre-herime-trimestriel, membre-herime-semestriel et membre-herime-annuel existent en base.');
            }
        } catch (QueryException $e) {
            Log::error('subscription_plans.member_bundle_update_failed', [
                'message' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? null,
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Enregistrement impossible (contrainte base de données). Exécutez les migrations à jour, ou consultez storage/logs/laravel.log.');
        }

        $successMessage = 'Offre Membre Herime mise à jour.';
        if ($updatedCount < 3) {
            $successMessage .= ' Attention : seulement '.$updatedCount.' formule(s) sur 3 — vérifiez que les slugs membre-herime-trimestriel, membre-herime-semestriel et membre-herime-annuel ont chacun une ligne en base.';
        }

        return redirect()->route('admin.subscriptions.plans.index')
            ->with('success', $successMessage);
    }

    /**
     * @param  array<string, int>  $trialMap
     * @param  array<string, float>  $priceMap
     * @param  array<string, string>  $highlightMap
     * @param  array<string, string>  $billingMap
     * @param  array<string, int>  $orderMap
     * @return array<string, mixed>
     */
    private function memberBundlePlanAttributesForPeriod(
        string $period,
        string $canonicalSlug,
        string $baseName,
        mixed $description,
        array $trialMap,
        bool $isActive,
        bool $autoRenew,
        array $priceMap,
        array $highlightMap,
        array $billingMap,
        array $orderMap,
        string $popularPeriod,
    ): array {
        $label = SubscriptionPlan::MEMBER_COMMUNITY_PERIOD_LABELS[$period] ?? $period;
        $meta = [
            'community_premium' => true,
            'community_display_order' => $orderMap[$period] ?? 99,
            'label' => $label,
            'community_card_popular' => $period === $popularPeriod,
        ];
        if (($highlightMap[$period] ?? '') !== '') {
            $meta['community_card_highlight'] = $highlightMap[$period];
        }

        return [
            'name' => $baseName.' — '.$label,
            'slug' => $canonicalSlug,
            'description' => $description,
            'plan_type' => $this->memberPlanTypeForStorage(),
            'billing_period' => $billingMap[$period],
            'price' => $priceMap[$period],
            'annual_discount_percent' => 0,
            'trial_days' => (int) ($trialMap[$period] ?? 0),
            'is_active' => $isActive,
            'auto_renew_default' => $autoRenew,
            'content_id' => null,
            'metadata' => $meta,
        ];
    }

    /**
     * @return array<string, int>
     */
    private function memberBundleTrialMapFromRequest(Request $request): array
    {
        $map = [];
        foreach (array_keys(SubscriptionPlan::MEMBER_COMMUNITY_SLUGS) as $period) {
            $raw = $request->input("membre_trial_days.{$period}");
            $map[$period] = max(0, min(365, (int) ($raw ?? 0)));
        }

        return $map;
    }

    /**
     * Période affichée comme « populaire » sur la carte page adhésion (une seule).
     */
    private function resolveMembrePopularPeriod(Request $request): string
    {
        $p = $request->input('membre_popular_period');
        if (in_array($p, ['quarterly', 'semiannual', 'yearly'], true)) {
            return $p;
        }

        return 'yearly';
    }

    /**
     * MySQL : ENUM étendu avec « membre » (migration). SQLite / tests : CHECK héritée sans « membre » → « recurring » + slug membre-herime-*.
     */
    private function memberPlanTypeForStorage(): string
    {
        return Schema::getConnection()->getDriverName() === 'mysql' ? 'membre' : 'recurring';
    }
}
