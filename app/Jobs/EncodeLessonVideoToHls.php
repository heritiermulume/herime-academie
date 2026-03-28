<?php

namespace App\Jobs;

use App\Models\CourseLesson;
use App\Services\HlsEncodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EncodeLessonVideoToHls implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $uniqueFor = 7200;

    public function __construct(
        public int $lessonId
    ) {}

    public function uniqueId(): string
    {
        return 'hls-lesson-'.$this->lessonId;
    }

    public function handle(HlsEncodingService $hlsEncoding): void
    {
        if (! config('video.hls.enabled')) {
            return;
        }

        $lesson = CourseLesson::query()->find($this->lessonId);
        if (! $lesson || $lesson->type !== 'video') {
            return;
        }

        $source = $lesson->getInternalStorageVideoPath();
        if (! $source) {
            CourseLesson::query()->whereKey($lesson->id)->update([
                'hls_manifest_path' => null,
                'hls_status' => null,
            ]);

            return;
        }

        CourseLesson::query()->whereKey($lesson->id)->update([
            'hls_status' => 'processing',
        ]);

        try {
            $manifest = $hlsEncoding->encodeLessonVideoToHls($source);
            CourseLesson::query()->whereKey($lesson->id)->update([
                'hls_manifest_path' => $manifest,
                'hls_status' => 'ready',
            ]);
        } catch (\Throwable $e) {
            Log::error('EncodeLessonVideoToHls failed', [
                'lesson_id' => $this->lessonId,
                'message' => $e->getMessage(),
            ]);
            CourseLesson::query()->whereKey($lesson->id)->update([
                'hls_manifest_path' => null,
                'hls_status' => 'failed',
            ]);
        }
    }
}
