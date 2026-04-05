<?php

namespace App\Jobs;

use App\Models\Course;
use App\Services\HlsEncodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EncodeCourseVideoPreviewToHls implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $uniqueFor = 7200;

    public function __construct(
        public int $courseId
    ) {}

    public function uniqueId(): string
    {
        return 'hls-course-preview-'.$this->courseId;
    }

    public function handle(HlsEncodingService $hlsEncoding): void
    {
        if (! config('video.hls.enabled')) {
            return;
        }

        $course = Course::query()->find($this->courseId);
        if (! $course) {
            return;
        }

        $path = ltrim((string) $course->video_preview, '/');
        if ($path === '' || filter_var($path, FILTER_VALIDATE_URL) || ! empty($course->video_preview_youtube_id)) {
            Course::query()->whereKey($course->id)->update([
                'video_preview_hls_manifest_path' => null,
                'video_preview_hls_status' => null,
            ]);

            return;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($ext, ['mp4', 'm4v', 'mov'], true)) {
            Course::query()->whereKey($course->id)->update([
                'video_preview_hls_manifest_path' => null,
                'video_preview_hls_status' => null,
            ]);

            return;
        }

        if (! str_starts_with($path, 'courses/previews/')) {
            $path = 'courses/previews/'.$path;
        }

        Course::query()->whereKey($course->id)->update([
            'video_preview_hls_status' => 'processing',
        ]);

        try {
            $manifest = $hlsEncoding->encodeVideoToHls($path);
            Course::query()->whereKey($course->id)->update([
                'video_preview_hls_manifest_path' => $manifest,
                'video_preview_hls_status' => 'ready',
            ]);
        } catch (\Throwable $e) {
            Log::error('EncodeCourseVideoPreviewToHls failed', [
                'course_id' => $this->courseId,
                'message' => $e->getMessage(),
            ]);
            Course::query()->whereKey($course->id)->update([
                'video_preview_hls_manifest_path' => null,
                'video_preview_hls_status' => 'failed',
            ]);
        }
    }
}
