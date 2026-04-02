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
        ];
    }

    /**
     * Textes page /communaute/membre-premium (chaîne vide = la vue garde le texte par défaut codé en dur).
     *
     * @return array<string, string|null>
     */
    public static function premiumPageTexts(): array
    {
        $defaults = [
            'kicker' => null,
            'title' => null,
            'lead' => null,
            'second' => null,
            'plans_intro_title' => null,
            'plans_intro_subtitle' => null,
            'guest_box_title' => null,
            'guest_box_text' => null,
        ];

        $stored = Setting::get('community_premium_page_texts', []);
        if (! is_array($stored)) {
            return $defaults;
        }

        return array_merge($defaults, array_intersect_key($stored, $defaults));
    }

    /**
     * Sous-textes optionnels par slug de plan (affichés sous le prix).
     *
     * @return array<string, string>
     */
    public static function premiumPlanHighlights(): array
    {
        $stored = Setting::get('community_premium_plan_highlights', []);
        if (! is_array($stored)) {
            return [];
        }

        $out = [];
        foreach ($stored as $slug => $text) {
            if (is_string($slug) && is_string($text) && $text !== '') {
                $out[$slug] = $text;
            }
        }

        return $out;
    }
}
