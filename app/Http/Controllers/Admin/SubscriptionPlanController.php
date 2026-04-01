<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::with(['content', 'contents'])->latest()->paginate(15);
        $includedPackageIds = $plans->getCollection()
            ->pluck('metadata')
            ->filter(fn ($metadata) => is_array($metadata))
            ->flatMap(fn ($metadata) => collect(data_get($metadata, 'included_package_ids', [])))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $includedPackagesById = ContentPackage::query()
            ->whereIn('id', $includedPackageIds)
            ->get()
            ->keyBy('id');

        return view('admin.subscriptions.plans.index', compact('plans', 'includedPackagesById'));
    }

    public function create()
    {
        $contents = Course::query()->where('is_published', true)->orderBy('title')->limit(300)->get();
        $packages = ContentPackage::query()->where('is_published', true)->orderBy('title')->limit(300)->get();

        return view('admin.subscriptions.plans.create', compact('contents', 'packages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plan_type' => ['required', 'in:recurring,one_time,freemium'],
            'billing_period' => ['nullable', 'in:monthly,yearly'],
            'price' => ['required', 'numeric', 'min:0'],
            'annual_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'content_id' => ['nullable', 'exists:contents,id'],
            'content_ids' => ['nullable', 'array'],
            'content_ids.*' => ['nullable', 'exists:contents,id'],
            'package_ids' => ['nullable', 'array'],
            'package_ids.*' => ['nullable', 'exists:content_packages,id'],
            'is_active' => ['nullable', 'boolean'],
            'auto_renew_default' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        $data['annual_discount_percent'] = $data['annual_discount_percent'] ?? 0;
        $data['trial_days'] = $data['trial_days'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        $data['auto_renew_default'] = $request->boolean('auto_renew_default');

        if ($data['plan_type'] !== 'recurring') {
            $data['billing_period'] = null;
            $data['trial_days'] = 0;
            $data['auto_renew_default'] = false;
        }

        if ($data['plan_type'] !== 'one_time') {
            $data['content_id'] = null;
            $selectedContentIds = [];
            $selectedPackageIds = [];
        } else {
            $selectedContentIds = collect($request->input('content_ids', []))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $selectedPackageIds = collect($request->input('package_ids', []))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $packageContentIds = ContentPackage::query()
                ->whereIn('id', $selectedPackageIds)
                ->with('contents:id')
                ->get()
                ->flatMap(fn (ContentPackage $package) => $package->contents->pluck('id'))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $selectedContentIds = collect($selectedContentIds)
                ->merge($packageContentIds)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $data['content_id'] = $selectedContentIds[0] ?? $data['content_id'] ?? null;
        }
        $data['metadata'] = [
            'included_package_ids' => $selectedPackageIds,
        ];

        $plan = SubscriptionPlan::create($data);
        $plan->contents()->sync($selectedContentIds ?? []);

        return redirect()->route('admin.subscriptions.plans.index')->with('success', 'Plan d\'abonnement créé.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        $contents = Course::query()->where('is_published', true)->orderBy('title')->limit(300)->get();
        $packages = ContentPackage::query()->where('is_published', true)->orderBy('title')->limit(300)->get();

        return view('admin.subscriptions.plans.edit', compact('plan', 'contents', 'packages'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'plan_type' => ['required', 'in:recurring,one_time,freemium'],
            'billing_period' => ['nullable', 'in:monthly,yearly'],
            'price' => ['required', 'numeric', 'min:0'],
            'annual_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'content_id' => ['nullable', 'exists:contents,id'],
            'content_ids' => ['nullable', 'array'],
            'content_ids.*' => ['nullable', 'exists:contents,id'],
            'package_ids' => ['nullable', 'array'],
            'package_ids.*' => ['nullable', 'exists:content_packages,id'],
            'is_active' => ['nullable', 'boolean'],
            'auto_renew_default' => ['nullable', 'boolean'],
        ]);

        $data['annual_discount_percent'] = $data['annual_discount_percent'] ?? 0;
        $data['trial_days'] = $data['trial_days'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        $data['auto_renew_default'] = $request->boolean('auto_renew_default');

        if ($data['plan_type'] !== 'recurring') {
            $data['billing_period'] = null;
            $data['trial_days'] = 0;
            $data['auto_renew_default'] = false;
        }

        if ($data['plan_type'] !== 'one_time') {
            $data['content_id'] = null;
            $selectedContentIds = [];
            $selectedPackageIds = [];
        } else {
            $selectedContentIds = collect($request->input('content_ids', []))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $selectedPackageIds = collect($request->input('package_ids', []))
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $packageContentIds = ContentPackage::query()
                ->whereIn('id', $selectedPackageIds)
                ->with('contents:id')
                ->get()
                ->flatMap(fn (ContentPackage $package) => $package->contents->pluck('id'))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $selectedContentIds = collect($selectedContentIds)
                ->merge($packageContentIds)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            $data['content_id'] = $selectedContentIds[0] ?? $data['content_id'] ?? null;
        }
        $metadata = is_array($plan->metadata) ? $plan->metadata : [];
        $metadata['included_package_ids'] = $selectedPackageIds;
        $data['metadata'] = $metadata;

        $plan->update($data);
        $plan->contents()->sync($selectedContentIds ?? []);

        return redirect()->route('admin.subscriptions.plans.index')->with('success', 'Plan mis à jour.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.subscriptions.plans.index')->with('success', 'Plan supprimé.');
    }
}

