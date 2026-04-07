<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentPackage;
use App\Models\Course;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentPackageController extends Controller
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    public function index()
    {
        $packages = ContentPackage::query()
            ->withCount('contents')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.packages.index', compact('packages'));
    }

    public function show(ContentPackage $package)
    {
        $package->load(['contents' => fn ($q) => $q->orderByPivot('sort_order')]);

        return view('admin.packages.show', compact('package'));
    }

    public function create()
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        return view('admin.packages.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedPackageData($request);
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        $this->applyPackageThumbnail($request, $data);

        $this->handleCoverVideoUpload($request, $data);

        if (! empty($data['cover_video_youtube_id'])) {
            $data['cover_video'] = null;
        }

        $package = ContentPackage::create($data);
        $this->syncContents($package, $request);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Pack créé avec succès.');
    }

    public function edit(ContentPackage $package)
    {
        $package->load('contents');
        $courses = Course::query()
            ->where('is_published', true)
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        return view('admin.packages.edit', compact('package', 'courses'));
    }

    public function update(Request $request, ContentPackage $package)
    {
        $data = $this->validatedPackageData($request);
        if ($request->filled('slug')) {
            $request->validate([
                'slug' => 'required|string|max:255',
            ]);
            $data['slug'] = $this->uniqueSlug(Str::slug($request->input('slug')), $package->id);
        }

        $this->applyPackageThumbnail($request, $data, $package);

        $this->handleCoverVideoUpload($request, $data, $package);

        if (! empty($data['cover_video_youtube_id'])) {
            $data['cover_video'] = null;
        }

        $package->update($data);
        $this->syncContents($package, $request);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Pack mis à jour.');
    }

    public function destroy(ContentPackage $package)
    {
        if ($package->thumbnail) {
            $this->fileUploadService->deleteFile($package->thumbnail);
        }
        if ($package->cover_video && ! filter_var($package->cover_video, FILTER_VALIDATE_URL)) {
            $this->fileUploadService->deleteFile($package->cover_video);
        }
        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Pack supprimé.');
    }

    private function validatedPackageData(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'short_description' => 'nullable|string|max:2000',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail_chunk_path' => 'nullable|string|max:2048',
            'thumbnail_chunk_name' => 'nullable|string|max:512',
            'thumbnail_chunk_size' => 'nullable|integer|min:0|max:2147483647',
            'cover_video_file' => 'nullable|file|mimetypes:video/mp4,video/quicktime,video/webm,video/ogg|max:1048576',
            'cover_video_path' => 'nullable|string|max:2048',
            'cover_video_name' => 'nullable|string|max:255',
            'cover_video_size' => 'nullable|integer|min:0|max:2147483647',
            'cover_video_youtube_id' => 'nullable|string|max:255',
            'cover_video_is_unlisted' => 'nullable|boolean',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'sale_start_at' => 'nullable|date',
            'sale_end_at' => 'nullable|date|after_or_equal:sale_start_at',
            'use_fake_promo_countdown' => 'nullable|boolean',
            'fake_promo_duration_days' => 'nullable|integer|min:1|max:365',
            'is_sale_enabled' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'marketing_headline' => 'nullable|string|max:500',
            'cta_label' => 'nullable|string|max:120',
            'sort_order' => 'nullable|integer|min:0|max:999999',
            'content_ids' => 'required|array|min:1',
            'content_ids.*' => 'required|integer|exists:contents,id',
            'marketing_highlights' => 'nullable|array',
            'marketing_highlights.*' => 'nullable|string|max:500',
            'marketing_benefits' => 'nullable|array',
            'marketing_benefits.*' => 'nullable|string|max:500',
        ], [
            'sale_price.lt' => 'Le prix promotionnel doit être inférieur au prix normal.',
            'content_ids.required' => 'Sélectionnez au moins un contenu dans le pack.',
            'content_ids.min' => 'Sélectionnez au moins un contenu dans le pack.',
        ]);

        $validated['is_sale_enabled'] = $request->boolean('is_sale_enabled', true);
        $validated['use_fake_promo_countdown'] = $request->boolean('use_fake_promo_countdown', false);
        $validated['fake_promo_duration_days'] = $request->filled('fake_promo_duration_days')
            ? (int) $request->input('fake_promo_duration_days')
            : null;
        $validated['is_published'] = $request->boolean('is_published', false);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['cover_video_is_unlisted'] = $request->boolean('cover_video_is_unlisted', false);
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $validated['marketing_highlights'] = $this->filterLines($request->input('marketing_highlights', []));
        $validated['marketing_benefits'] = $this->filterLines($request->input('marketing_benefits', []));

        if (! $validated['use_fake_promo_countdown']) {
            $validated['fake_promo_duration_days'] = null;
        }

        if (! empty($validated['cover_video_youtube_id'])) {
            $validated['cover_video_youtube_id'] = $this->extractYouTubeVideoId($validated['cover_video_youtube_id']);
        }

        unset(
            $validated['content_ids'],
            $validated['thumbnail'],
            $validated['cover_video_file'],
            $validated['cover_video_path'],
            $validated['cover_video_name'],
            $validated['cover_video_size'],
            $validated['thumbnail_chunk_path'],
            $validated['thumbnail_chunk_name'],
            $validated['thumbnail_chunk_size'],
        );

        return $validated;
    }

    private function applyPackageThumbnail(Request $request, array &$data, ?ContentPackage $existing = null): void
    {
        if ($request->hasFile('thumbnail')) {
            $result = $this->fileUploadService->uploadImage(
                $request->file('thumbnail'),
                'packages/thumbnails',
                $existing?->thumbnail,
                1920
            );
            $data['thumbnail'] = $result['path'];

            return;
        }

        if ($request->filled('thumbnail_chunk_path')) {
            $chunkPath = $this->sanitizeUploadedPath($request->input('thumbnail_chunk_path'));
            if ($chunkPath) {
                $newPath = $this->fileUploadService->promoteTemporaryFile($chunkPath, 'packages/thumbnails');
                if ($existing?->thumbnail && $existing->thumbnail !== $newPath) {
                    $this->fileUploadService->deleteFile($existing->thumbnail);
                }
                $data['thumbnail'] = $newPath;
            }
        }
    }

    private function sanitizeUploadedPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $clean = trim($path);
        if ($clean === '') {
            return null;
        }

        $clean = str_replace('..', '', $clean);
        $clean = ltrim($clean, '/');

        if (str_starts_with($clean, 'storage/')) {
            $clean = ltrim(substr($clean, strlen('storage/')), '/');
        }

        $allowedPrefixes = [
            FileUploadService::TEMPORARY_BASE_PATH,
            'courses/thumbnails',
            'courses/previews',
            'courses/lessons',
            'courses/downloads',
            'packages/thumbnails',
            'packages/covers',
        ];

        foreach ($allowedPrefixes as $prefix) {
            $normalized = rtrim($prefix, '/');
            if ($clean === $normalized || str_starts_with($clean, $normalized.'/')) {
                return $clean;
            }
        }

        return null;
    }

    private function handleCoverVideoUpload(Request $request, array &$data, ?ContentPackage $existing = null): void
    {
        if ($request->hasFile('cover_video_file')) {
            $oldCover = $existing?->cover_video && ! filter_var($existing->cover_video, FILTER_VALIDATE_URL)
                ? $existing->cover_video
                : null;
            $result = $this->fileUploadService->uploadVideo(
                $request->file('cover_video_file'),
                'packages/covers',
                $oldCover
            );
            $data['cover_video'] = $result['path'];
            $data['cover_video_youtube_id'] = null;

            return;
        }

        if ($request->filled('cover_video_path')) {
            $sanitizedPath = $this->sanitizeUploadedPath($request->input('cover_video_path'));
            if ($sanitizedPath) {
                $currentPath = $existing?->cover_video && ! filter_var($existing->cover_video, FILTER_VALIDATE_URL)
                    ? $existing->cover_video
                    : null;
                $finalPath = $this->fileUploadService->promoteTemporaryFile($sanitizedPath, 'packages/covers');
                if ($currentPath && $currentPath !== $finalPath) {
                    $this->fileUploadService->deleteFile($currentPath);
                }
                $data['cover_video'] = $finalPath;
                $data['cover_video_youtube_id'] = null;
            }
        }
    }

    private function syncContents(ContentPackage $package, Request $request): void
    {
        $ids = array_values(array_unique(array_map('intval', $request->input('content_ids', []))));
        $sync = [];
        foreach ($ids as $i => $contentId) {
            $sync[$contentId] = ['sort_order' => $i];
        }
        $package->contents()->sync($sync);
    }

    private function filterLines(array $lines): ?array
    {
        $out = collect($lines)
            ->map(fn ($l) => is_string($l) ? trim($l) : '')
            ->filter(fn ($l) => $l !== '')
            ->values()
            ->all();

        return $out === [] ? null : $out;
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base ?: 'pack';
        $original = $slug;
        $n = 1;
        while (ContentPackage::query()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $original . '-' . $n;
            $n++;
        }

        return $slug;
    }

    private function extractYouTubeVideoId(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }
        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }
        $patterns = [
            '/[?&]v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/v\/([a-zA-Z0-9_-]{11})/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        return null;
    }
}
