<?php

namespace App\Jobs;

use App\Models\Setting;
use App\Services\HlsEncodingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EncodeCommunityHomeVideoToHls implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;

    public int $uniqueFor = 7200;

    public function uniqueId(): string
    {
        return 'hls-community-home';
    }

    public function handle(HlsEncodingService $hlsEncoding): void
    {
        if (! config('video.hls.enabled')) {
            return;
        }

        $type = strtolower(trim((string) Setting::get('community_home_media_type', 'image')));
        $path = ltrim(trim((string) Setting::get('community_home_media_url', '')), '/');

        if ($type !== 'video' || $path === '' || filter_var($path, FILTER_VALIDATE_URL)) {
            Setting::set('community_home_hls_manifest_path', '', 'string', 'Bloc accueil : chemin manifeste HLS (relatif)');
            Setting::set('community_home_hls_status', '', 'string', 'Bloc accueil : statut encodage HLS');

            return;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (! in_array($ext, ['mp4', 'm4v', 'mov'], true)) {
            Setting::set('community_home_hls_manifest_path', '', 'string', 'Bloc accueil : chemin manifeste HLS (relatif)');
            Setting::set('community_home_hls_status', '', 'string', 'Bloc accueil : statut encodage HLS');

            return;
        }

        if (! str_starts_with($path, 'site/community-home/')) {
            $path = 'site/community-home/'.$path;
        }

        Setting::set('community_home_hls_status', 'processing', 'string', 'Bloc accueil : statut encodage HLS');

        try {
            $manifest = $hlsEncoding->encodeVideoToHls($path);
            Setting::set('community_home_hls_manifest_path', $manifest, 'string', 'Bloc accueil : chemin manifeste HLS (relatif)');
            Setting::set('community_home_hls_status', 'ready', 'string', 'Bloc accueil : statut encodage HLS');
        } catch (\Throwable $e) {
            Log::error('EncodeCommunityHomeVideoToHls failed', [
                'message' => $e->getMessage(),
            ]);
            Setting::set('community_home_hls_manifest_path', '', 'string', 'Bloc accueil : chemin manifeste HLS (relatif)');
            Setting::set('community_home_hls_status', 'failed', 'string', 'Bloc accueil : statut encodage HLS');
        }
    }
}
