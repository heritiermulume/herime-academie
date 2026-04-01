@csrf

@php
    $selectedContents = $selectedContents ?? [];
    $packages = $packages ?? collect();
@endphp

<div class="admin-form-grid admin-form-grid--two">
    <div>
        <label class="form-label fw-semibold">Nom du plan</label>
        <input type="text" name="name" class="form-control" required value="{{ old('name', $plan->name ?? '') }}">
    </div>
    <div>
        <label class="form-label fw-semibold">Type</label>
        <select name="plan_type" class="form-select" required>
            @php
                $selectedType = old('plan_type', $plan->plan_type ?? 'recurring');
            @endphp
            <option value="recurring" @selected($selectedType === 'recurring')>Abonnement récurrent</option>
            <option value="one_time" @selected($selectedType === 'one_time')>Achat unique (formation)</option>
            <option value="freemium" @selected($selectedType === 'freemium')>Freemium</option>
        </select>
    </div>
    <div>
        <label class="form-label fw-semibold">Période de facturation</label>
        @php
            $selectedPeriod = old('billing_period', $plan->billing_period ?? 'monthly');
        @endphp
        <select name="billing_period" class="form-select">
            <option value="monthly" @selected($selectedPeriod === 'monthly')>Mensuel</option>
            <option value="yearly" @selected($selectedPeriod === 'yearly')>Annuel</option>
        </select>
    </div>
    <div>
        <label class="form-label fw-semibold">Prix</label>
        <input type="number" step="0.01" min="0" name="price" class="form-control" required value="{{ old('price', $plan->price ?? 0) }}">
    </div>
    <div>
        <label class="form-label fw-semibold">Réduction annuelle (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="annual_discount_percent" class="form-control" value="{{ old('annual_discount_percent', $plan->annual_discount_percent ?? 0) }}">
    </div>
    <div>
        <label class="form-label fw-semibold">Essai gratuit (jours)</label>
        <input type="number" min="0" max="365" name="trial_days" class="form-control" value="{{ old('trial_days', $plan->trial_days ?? 0) }}">
    </div>
    <div>
        <label class="form-label fw-semibold">Formations incluses (achat unique)</label>
        @php
            $selectedContents = collect(old('content_ids', isset($plan) ? ($plan->contents()->pluck('contents.id')->all() ?: array_filter([$plan->content_id])) : []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        @endphp
        <select name="content_ids[]" class="form-select" multiple size="6">
            @foreach($contents as $content)
                <option value="{{ $content->id }}" @selected(in_array($content->id, ($selectedContents ?? []), true))>{{ $content->title }}</option>
            @endforeach
        </select>
        <small class="text-muted">Maintenez Ctrl (Windows) / Cmd (Mac) pour sélectionner plusieurs formations.</small>
    </div>
    <div>
        <label class="form-label fw-semibold">Packs inclus (achat unique)</label>
        @php
            $selectedPackageIds = collect(old('package_ids', isset($plan) ? data_get($plan->metadata, 'included_package_ids', []) : []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        @endphp
        <select name="package_ids[]" class="form-select" multiple size="6">
            @foreach($packages as $package)
                <option value="{{ $package->id }}" @selected(in_array($package->id, ($selectedPackageIds ?? []), true))>{{ $package->title }}</option>
            @endforeach
        </select>
        <small class="text-muted">Maintenez Ctrl (Windows) / Cmd (Mac) pour sélectionner plusieurs packs.</small>
    </div>
    <div class="d-flex align-items-center gap-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                   @checked((bool) old('is_active', $plan->is_active ?? true))>
            <label class="form-check-label" for="is_active">Plan actif</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="auto_renew_default" id="auto_renew_default" value="1"
                   @checked((bool) old('auto_renew_default', $plan->auto_renew_default ?? true))>
            <label class="form-check-label" for="auto_renew_default">Renouvellement auto par défaut</label>
        </div>
    </div>
</div>

<div class="mt-3">
    <label class="form-label fw-semibold">Description</label>
    <textarea name="description" rows="4" class="form-control">{{ old('description', $plan->description ?? '') }}</textarea>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary">
        <i class="fas fa-save me-1"></i>Enregistrer
    </button>
    <a href="{{ route('admin.subscriptions.plans.index') }}" class="btn btn-light border">Annuler</a>
</div>

