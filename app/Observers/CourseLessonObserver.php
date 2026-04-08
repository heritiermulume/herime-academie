<?php

namespace App\Observers;

use App\Jobs\EncodeLessonVideoToHls;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\HlsEncodingService;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseLessonObserver
{
    public function saved(CourseLesson $lesson): void
    {
        if (! config('video.hls.enabled')) {
            return;
        }

        if ($lesson->type !== 'video') {
            return;
        }

        if (! empty($lesson->youtube_video_id)) {
            if ($lesson->hls_manifest_path
                || $lesson->wasChanged(['youtube_video_id', 'file_path', 'content_url'])) {
                $oldInternal = $this->previousInternalVideoPath($lesson);
                if ($oldInternal) {
                    app(HlsEncodingService::class)->deleteHlsOutputForVideo($oldInternal);
                }
                DB::table('content_lessons')->where('id', $lesson->id)->update([
                    'hls_manifest_path' => null,
                    'hls_status' => null,
                    'updated_at' => now(),
                ]);
            }

            return;
        }

        $internal = $lesson->getInternalStorageVideoPath();
        $oldInternal = $this->previousInternalVideoPath($lesson);

        if (! $internal) {
            if ($oldInternal) {
                app(HlsEncodingService::class)->deleteHlsOutputForVideo($oldInternal);
            }
            DB::table('content_lessons')->where('id', $lesson->id)->update([
                'hls_manifest_path' => null,
                'hls_status' => null,
                'updated_at' => now(),
            ]);

            return;
        }

        if (! $lesson->wasChanged(['file_path', 'content_url', 'type'])) {
            return;
        }

        if ($oldInternal && $oldInternal !== $internal) {
            app(HlsEncodingService::class)->deleteHlsOutputForVideo($oldInternal);
        }

        DB::table('content_lessons')->where('id', $lesson->id)->update([
            'hls_manifest_path' => null,
            'hls_status' => 'queued',
            'updated_at' => now(),
        ]);

        EncodeLessonVideoToHls::dispatch($lesson->id);
    }

    public function created(CourseLesson $lesson): void
    {
        $this->syncCommunityAccessForParentCourse($lesson);
    }

    public function restored(CourseLesson $lesson): void
    {
        $this->syncCommunityAccessForParentCourse($lesson);
    }

    protected function syncCommunityAccessForParentCourse(CourseLesson $lesson): void
    {
        if (! $lesson->content_id) {
            return;
        }

        $course = Course::query()->find($lesson->content_id);
        if (! $course) {
            return;
        }

        try {
            app(SubscriptionService::class)->syncCommunityMembersAccessToCourse($course);
        } catch (\Throwable $e) {
            Log::warning('syncCommunityMembersAccessToCourse (lesson observer): '.$e->getMessage(), [
                'content_id' => $course->id,
                'lesson_id' => $lesson->id,
            ]);
        }
    }

    protected function previousInternalVideoPath(CourseLesson $lesson): ?string
    {
        $origFile = $lesson->getOriginal('file_path');
        $origContent = $lesson->getOriginal('content_url');

        foreach ([$origFile, $origContent] as $p) {
            if (empty($p) || filter_var($p, FILTER_VALIDATE_URL)) {
                continue;
            }
            $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'm4v', 'mov', 'webm', 'mkv'], true)) {
                return $p;
            }
        }

        return null;
    }
}
