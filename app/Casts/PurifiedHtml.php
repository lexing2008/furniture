<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Facades\Purify;

/**
 * HTML written by users or staff is cleaned on the way into the database:
 * only the tags of the given Purify profile (config/purify.php) stay, scripts and attributes like onclick never do.
 *
 * Usage in a model: 'seo_block' => PurifiedHtml::class.':seo'
 *
 * @implements CastsAttributes<string|null, string|null>
 */
class PurifiedHtml implements CastsAttributes
{
    public function __construct(private string $profile = 'seo') {}

    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // "HTML.Allowed" has a dot inside, so it cannot be read with config('purify.configs.seo.HTML.Allowed')
        $allowed = (string) (config("purify.configs.{$this->profile}")['HTML.Allowed'] ?? '');

        // Editors write <strong> and <em>; profiles without them get <b> and <i> instead of losing the formatting
        if (! str_contains($allowed, 'strong')) {
            $value = str_ireplace(['<strong>', '</strong>', '<em>', '</em>'], ['<b>', '</b>', '<i>', '</i>'], (string) $value);
        }

        $clean = trim(Purify::config($this->profile)->clean((string) $value));

        // An empty editor gives "<p></p>"
        if (trim(strip_tags($clean)) === '' && ! str_contains($clean, '<table')) {
            return null;
        }

        return $clean;
    }
}
