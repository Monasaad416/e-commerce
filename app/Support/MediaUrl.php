<?php

namespace App\Support;

/**
 * Build absolute URLs for files on the public disk so SPA clients (e.g. Next.js on another origin) can load images.
 */
final class MediaUrl
{
    public static function public(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
