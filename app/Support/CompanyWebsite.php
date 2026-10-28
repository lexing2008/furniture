<?php

namespace App\Support;

/**
 * Comparison of an image URL with the website of a company.
 */
final class CompanyWebsite
{
    public static function host(?string $website): ?string
    {
        $website = trim((string) $website);

        if ($website === '') {
            return null;
        }

        if (! preg_match('~^https?://~i', $website)) {
            $website = 'https://'.$website;
        }

        $host = parse_url($website, PHP_URL_HOST);

        return is_string($host) && $host !== ''
            ? strtolower(rtrim($host, '.'))
            : null;
    }

    /**
     * A URL is allowed when its host is the company website or a subdomain of it.
     */
    public static function allows(?string $website, string $url): bool
    {
        $base = self::host($website);
        $host = parse_url($url, PHP_URL_HOST);

        if ($base === null || ! is_string($host) || $host === '') {
            return false;
        }

        $base = preg_replace('/^www\./i', '', $base) ?? $base;
        $host = strtolower(rtrim($host, '.'));

        return $host === $base || str_ends_with($host, '.'.$base);
    }
}
