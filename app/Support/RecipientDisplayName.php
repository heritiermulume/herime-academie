<?php

namespace App\Support;

class RecipientDisplayName
{
    public static function resolve(?string $name, ?string $email): string
    {
        $normalizedName = trim((string) $name);
        if ($normalizedName !== '') {
            return $normalizedName;
        }

        $normalizedEmail = trim((string) $email);
        if ($normalizedEmail === '') {
            return '';
        }

        $localPart = explode('@', $normalizedEmail)[0] ?? '';
        $localPart = preg_replace('/[._-]+/', ' ', $localPart) ?? $localPart;
        $localPart = trim(preg_replace('/\s+/', ' ', $localPart) ?? $localPart);

        if ($localPart === '') {
            return $normalizedEmail;
        }

        return mb_convert_case($localPart, MB_CASE_TITLE, 'UTF-8');
    }
}
