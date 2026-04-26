<?php

namespace App\Support;

use App\Services\FileUploadService;
use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Support\Str;

class RichText
{
    /**
     * Enveloppe un fragment HTML pour loadHTML : sans déclaration UTF-8 explicite,
     * libxml interprète souvent les octets en ISO-8859-1 et corrompt les accents (mojibake).
     */
    private static function wrapFragmentForLibxml(string $innerHtml): string
    {
        return '<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head><body><div id="rich-text-root">'.$innerHtml.'</div></body></html>';
    }

    /**
     * Convertit un contenu texte/HTML en HTML affichable et sécurisé.
     */
    public static function toHtml(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        // Ancien contenu en texte brut: conserver les retours ligne.
        if (! Str::contains($value, '<')) {
            return self::injectEmbeds(nl2br(e($value)));
        }

        return self::injectEmbeds(self::sanitize($value));
    }

    /**
     * Nettoie un fragment HTML pour conserver uniquement un sous-ensemble sûr.
     */
    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML(
            self::wrapFragmentForLibxml($html),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $root = $document->getElementById('rich-text-root');
        if (! $root) {
            libxml_clear_errors();

            return '';
        }

        self::sanitizeNode($root);

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $document->saveHTML($child);
        }

        libxml_clear_errors();

        return trim((string) $output);
    }

    /**
     * Promeut les images temporaires insérées dans un HTML vers un dossier final.
     */
    public static function promoteTemporaryImageSources(
        string $html,
        FileUploadService $fileUploadService,
        string $finalFolder
    ): string {
        $html = trim($html);
        if ($html === '' || ! Str::contains($html, '<img')) {
            return $html;
        }

        libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $document->loadHTML(
            self::wrapFragmentForLibxml($html),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $root = $document->getElementById('rich-text-root');
        if (! $root) {
            libxml_clear_errors();

            return $html;
        }

        foreach (iterator_to_array($root->getElementsByTagName('img')) as $img) {
            if (! $img instanceof DOMElement) {
                continue;
            }

            $src = trim((string) $img->getAttribute('src'));
            if ($src === '') {
                continue;
            }

            $temporaryPath = self::extractTemporaryPathFromSrc($src);
            if (! $temporaryPath) {
                continue;
            }

            try {
                $finalPath = $fileUploadService->promoteTemporaryFile($temporaryPath, $finalFolder);
                $fileUploadService->resizeStoredImageIfNeeded($finalPath, 1920, 85);
                $img->setAttribute('src', $fileUploadService->getUrl($finalPath, $finalFolder));
            } catch (\Throwable $e) {
                // En cas d'échec de promotion, on conserve le src initial.
            }
        }

        $output = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $output .= $document->saveHTML($child);
        }

        libxml_clear_errors();

        return trim((string) $output);
    }

    private static function sanitizeNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (! self::isAllowedTag($child->tagName)) {
                    self::unwrapElement($child);

                    continue;
                }
                self::sanitizeElementAttributes($child);
            }

            if ($child->hasChildNodes()) {
                self::sanitizeNode($child);
            }
        }
    }

    private static function isAllowedTag(string $tag): bool
    {
        return in_array(strtolower($tag), [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's',
            'h1', 'h2', 'h3', 'h4',
            'ul', 'ol', 'li',
            'blockquote', 'code', 'pre',
            'span', 'div',
            'a', 'img',
        ], true);
    }

    private static function sanitizeElementAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);
        $allowed = ['style'];

        if ($tag === 'a') {
            $allowed = array_merge($allowed, ['href', 'target', 'rel']);
        }

        if ($tag === 'img') {
            $allowed = array_merge($allowed, ['src', 'alt', 'title', 'width', 'height']);
        }

        foreach (iterator_to_array($element->attributes) as $attr) {
            $name = strtolower($attr->name);
            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attr->name);

                continue;
            }

            if ($name === 'href' && ! self::isSafeUrl($attr->value, true)) {
                $element->removeAttribute('href');
            }

            if ($name === 'src' && ! self::isSafeUrl($attr->value, false, false)) {
                $element->removeAttribute('src');
            }

            if ($name === 'target') {
                $target = strtolower(trim($attr->value));
                if (! in_array($target, ['_blank', '_self'], true)) {
                    $element->setAttribute('target', '_self');
                }
                if (strtolower($element->getAttribute('target')) === '_blank') {
                    $rel = trim((string) $element->getAttribute('rel'));
                    $tokens = preg_split('/\s+/', $rel) ?: [];
                    $tokens = array_filter(array_unique(array_merge($tokens, ['noopener', 'noreferrer'])));
                    $element->setAttribute('rel', implode(' ', $tokens));
                }
            }

            if ($name === 'style') {
                $safeStyle = self::sanitizeInlineStyle($attr->value);
                if ($safeStyle === '') {
                    $element->removeAttribute('style');
                } else {
                    $element->setAttribute('style', $safeStyle);
                }
            }
        }
    }

    private static function sanitizeInlineStyle(string $style): string
    {
        $allowedProperties = [
            'font-size',
            'font-weight',
            'font-style',
            'text-decoration',
            'text-align',
            'color',
            'background-color',
            'margin-left',
            'width',
            'height',
            'max-width',
        ];

        $safeDeclarations = [];
        foreach (explode(';', $style) as $declaration) {
            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $property = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            if (! in_array($property, $allowedProperties, true)) {
                continue;
            }

            if ($value === '' || preg_match('/expression\s*\(|url\s*\(/i', $value)) {
                continue;
            }

            $safeDeclarations[] = $property.': '.$value;
        }

        return implode('; ', $safeDeclarations);
    }

    private static function isSafeUrl(string $url, bool $allowAnchor, bool $allowDataImage = false): bool
    {
        $url = trim($url);
        if ($url === '') {
            return false;
        }

        if ($allowAnchor && str_starts_with($url, '#')) {
            return true;
        }

        if (str_starts_with($url, '/')) {
            return true;
        }

        if (preg_match('/^(https?:|mailto:|tel:)/i', $url)) {
            return true;
        }

        if ($allowDataImage && preg_match('/^data:image\/(?:png|jpe?g|gif|webp);base64,[a-z0-9+\/=\s]+$/i', $url)) {
            return true;
        }

        return false;
    }

    private static function unwrapElement(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent) {
            return;
        }

        while ($element->firstChild) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }

    /**
     * Transforme [[embed:https://...]] en iframe responsive.
     */
    private static function injectEmbeds(string $html): string
    {
        return preg_replace_callback(
            '/\[\[embed:(https?:\/\/[^\s\]]+)\]\]/i',
            static function (array $matches): string {
                $url = trim((string) ($matches[1] ?? ''));
                if ($url === '' || ! preg_match('/^https?:\/\//i', $url)) {
                    return '';
                }

                $escaped = e($url);

                return '<div class="rich-embed-container"><iframe src="'.$escaped.'" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe><div class="rich-embed-fallback"><a href="'.$escaped.'" target="_blank" rel="noopener noreferrer">Ouvrir le lien dans un nouvel onglet</a></div></div>';
            },
            $html
        ) ?? $html;
    }

    private static function extractTemporaryPathFromSrc(string $src): ?string
    {
        $src = trim($src);
        if ($src === '') {
            return null;
        }

        $temporaryRoot = FileUploadService::TEMPORARY_BASE_PATH.'/';

        $asPath = ltrim((string) parse_url($src, PHP_URL_PATH), '/');
        if (str_starts_with($asPath, $temporaryRoot)) {
            return $asPath;
        }

        if (preg_match('#/files/temporary/(.+)$#', $asPath, $matches)) {
            $relative = urldecode((string) ($matches[1] ?? ''));
            $relative = ltrim(str_replace('..', '', $relative), '/');
            if ($relative !== '') {
                return $temporaryRoot.$relative;
            }
        }

        return null;
    }
}
