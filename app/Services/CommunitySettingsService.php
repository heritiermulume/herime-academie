<?php

namespace App\Services;

use App\Helpers\FileHelper;
use App\Models\Setting;

class CommunitySettingsService
{
    public const DEFAULT_HOME_IMAGE = 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=900&h=700&fit=crop&q=80';

    /**
     * Média bloc communauté (accueil) : image ou vidéo. Image par défaut si URL vide.
     *
     * @return array{type: string, url: string, poster: string}
     */
    public static function homeMedia(): array
    {
        $type = strtolower(trim((string) Setting::get('community_home_media_type', 'image')));
        if (! in_array($type, ['image', 'video'], true)) {
            $type = 'image';
        }

        $url = trim((string) Setting::get('community_home_media_url', ''));
        if ($url === '') {
            return [
                'type' => 'image',
                'url' => self::DEFAULT_HOME_IMAGE,
                'poster' => '',
            ];
        }

        if (
            ! filter_var($url, FILTER_VALIDATE_URL)
            && ! str_starts_with($url, 'http://')
            && ! str_starts_with($url, 'https://')
        ) {
            $url = FileHelper::url($url, 'site/community-home') ?: $url;
        }

        $poster = trim((string) Setting::get('community_home_media_poster_url', ''));
        if (
            $poster !== ''
            && ! filter_var($poster, FILTER_VALIDATE_URL)
            && ! str_starts_with($poster, 'http://')
            && ! str_starts_with($poster, 'https://')
        ) {
            $poster = FileHelper::url($poster, 'site/community-home') ?: $poster;
        }

        return [
            'type' => $type,
            'url' => $url,
            'poster' => $poster,
            'hls_url' => self::communityHomeHlsManifestUrl(),
        ];
    }

    /**
     * URL du master HLS pour la vidéo d’accueil (fichier hébergé), si encodage prêt.
     */
    public static function communityHomeHlsManifestUrl(): string
    {
        if (! config('video.hls.enabled')) {
            return '';
        }

        $st = trim((string) Setting::get('community_home_hls_status', ''));
        $rel = trim((string) Setting::get('community_home_hls_manifest_path', ''));
        if ($st !== 'ready' || $rel === '') {
            return '';
        }

        return route('files.serve', ['type' => 'community-home', 'path' => ltrim($rel, '/')]);
    }

    /**
     * Textes page /communaute/membre-premium : défauts dans la vue (plus de saisie dans Paramètres).
     *
     * @return array<string, string|null>
     */
    public static function premiumPageTexts(): array
    {
        return [
            'kicker' => null,
            'title' => null,
            'lead' => null,
            'second' => null,
            'plans_intro_title' => null,
            'plans_intro_subtitle' => null,
            'premium_guest_card_hint' => null,
        ];
    }
}
