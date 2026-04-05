<?php

namespace App\Jobs;

use App\Models\ContentPackage;
use App\Services\HlsEncodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EncodePackageCoverVideoToHls implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $uniqueFor = 7200;

    public function __construct(
        public int $packageId
    ) {}

    public function uniqueId(): string
    {
        return 'hls-package-cover-'.$this->packageId;
    }

    public function handle(HlsEncodingService $hlsEncoding): void
    {
        if (! config('video.hls.enabled')) {
            return;
        }

        $package = ContentPackage::query()->find($this->packageId);
        if (! $package) {
            return;
        }

        $path = ltrim((string) $package->cover_video, '/');
        if ($path === '' || filter_var($path, FILTER_VALIDATE_URL) || ! empty($package->cover_video_youtube_id)) {
            ContentPackage::query()->whereKey($package->id)->update([
                'cover_video_hls_manifest_path' => null,
                'cover_video_hls_status' => null,
            ]);

            return;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($ext, ['mp4', 'm4v', 'mov'], true)) {
            ContentPackage::query()->whereKey($package->id)->update([
                'cover_video_hls_manifest_path' => null,
                'cover_video_hls_status' => null,
            ]);

            return;
        }

        if (! str_starts_with($path, 'packages/covers/')) {
            $path = 'packages/covers/'.$path;
        }

        ContentPackage::query()->whereKey($package->id)->update([
            'cover_video_hls_status' => 'processing',
        ]);

        try {
            $manifest = $hlsEncoding->encodeVideoToHls($path);
            ContentPackage::query()->whereKey($package->id)->update([
                'cover_video_hls_manifest_path' => $manifest,
                'cover_video_hls_status' => 'ready',
            ]);
        } catch (\Throwable $e) {
            Log::error('EncodePackageCoverVideoToHls failed', [
                'package_id' => $this->packageId,
                'message' => $e->getMessage(),
            ]);
            ContentPackage::query()->whereKey($package->id)->update([
                'cover_video_hls_manifest_path' => null,
                'cover_video_hls_status' => 'failed',
            ]);
        }
    }
}
