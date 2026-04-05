<?php

namespace App\Observers;

use App\Jobs\EncodePackageCoverVideoToHls;
use App\Models\ContentPackage;
use App\Services\HlsEncodingService;

class ContentPackageCoverVideoHlsObserver
{
    public function saved(ContentPackage $package): void
    {
        if (! config('video.hls.enabled')) {
            return;
        }

        if (! $package->wasChanged(['cover_video', 'cover_video_youtube_id'])) {
            return;
        }

        $oldPath = $this->internalStoredVideoPath($package->getOriginal('cover_video'));
        $newPath = $this->internalStoredVideoPath($package->cover_video);

        if ($oldPath && $oldPath !== $newPath) {
            try {
                app(HlsEncodingService::class)->deleteHlsOutputForVideo($oldPath);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('ContentPackageCoverVideoHlsObserver: suppression HLS', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (! empty($package->cover_video_youtube_id)
            || empty($package->cover_video)
            || filter_var($package->cover_video, FILTER_VALIDATE_URL)
            || ! $this->isHlsEncodableExtension($package->cover_video)) {
            ContentPackage::query()->whereKey($package->id)->update([
                'cover_video_hls_manifest_path' => null,
                'cover_video_hls_status' => null,
            ]);

            return;
        }

        ContentPackage::query()->whereKey($package->id)->update([
            'cover_video_hls_manifest_path' => null,
            'cover_video_hls_status' => 'queued',
        ]);

        EncodePackageCoverVideoToHls::dispatch($package->id);
    }

    protected function internalStoredVideoPath(mixed $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return null;
        }

        return ltrim($value, '/');
    }

    protected function isHlsEncodableExtension(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, ['mp4', 'm4v', 'mov'], true);
    }
}
