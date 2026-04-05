<?php

namespace App\Observers;

use App\Jobs\EncodeCourseVideoPreviewToHls;
use App\Models\Course;
use App\Services\HlsEncodingService;

class CourseVideoPreviewHlsObserver
{
    public function saved(Course $course): void
    {
        if (! config('video.hls.enabled')) {
            return;
        }

        if (! $course->wasChanged(['video_preview', 'video_preview_youtube_id'])) {
            return;
        }

        $oldPath = $this->internalStoredVideoPath($course->getOriginal('video_preview'));
        $newPath = $this->internalStoredVideoPath($course->video_preview);

        if ($oldPath && $oldPath !== $newPath) {
            try {
                app(HlsEncodingService::class)->deleteHlsOutputForVideo($oldPath);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('CourseVideoPreviewHlsObserver: suppression HLS', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if (! empty($course->video_preview_youtube_id)
            || empty($course->video_preview)
            || filter_var($course->video_preview, FILTER_VALIDATE_URL)
            || ! $this->isHlsEncodableExtension($course->video_preview)) {
            Course::query()->whereKey($course->id)->update([
                'video_preview_hls_manifest_path' => null,
                'video_preview_hls_status' => null,
            ]);

            return;
        }

        Course::query()->whereKey($course->id)->update([
            'video_preview_hls_manifest_path' => null,
            'video_preview_hls_status' => 'queued',
        ]);

        EncodeCourseVideoPreviewToHls::dispatch($course->id);
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
