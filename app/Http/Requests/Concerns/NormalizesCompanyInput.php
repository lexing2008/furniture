<?php

namespace App\Http\Requests\Concerns;

/**
 * Cleaning of the company fields that the account pages have in common.
 */
trait NormalizesCompanyInput
{
    /**
     * Plain text fields never contain HTML tags.
     */
    protected function plainText(mixed $value): string
    {
        return trim(strip_tags((string) $value));
    }

    /**
     * "example.com" -> "https://example.com", an empty value is null.
     */
    protected function normalizeUrl(mixed $value): ?string
    {
        $value = trim(strip_tags((string) $value));

        if ($value === '') {
            return null;
        }

        return preg_match('~^[a-z][a-z0-9+.-]*://~i', $value) ? $value : 'https://'.$value;
    }

    /**
     * @return array<int, array{code: string, number: string, description: ?string}>
     */
    protected function normalizePhones(mixed $phones): array
    {
        return collect(is_array($phones) ? $phones : [])
            ->map(function ($phone) {
                $phone = is_array($phone) ? $phone : [];

                $code = preg_replace('/\s+/', '', strip_tags((string) ($phone['code'] ?? '')));

                return [
                    // "375" and "+375" are the same
                    'code' => $code === '' ? '' : '+'.ltrim($code, '+'),
                    'number' => trim(strip_tags((string) ($phone['number'] ?? ''))),
                    'description' => trim(strip_tags((string) ($phone['description'] ?? ''))) ?: null,
                ];
            })
            // A row that was left blank is not a phone
            ->reject(fn (array $phone) => $phone['code'] === '' && $phone['number'] === '' && $phone['description'] === null)
            ->values()
            ->all();
    }

    /**
     * The contact email of a company, not the login email of a user.
     */
    protected function normalizeEmail(mixed $value): ?string
    {
        return mb_strtolower(trim(strip_tags((string) $value))) ?: null;
    }
}
