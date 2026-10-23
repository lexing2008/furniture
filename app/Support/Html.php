<?php

namespace App\Support;

use Stevebauman\Purify\Facades\Purify;

class Html
{
    /**
     * SEO block ready for the page. Texts written before the editor appeared are plain, with line breaks;
     * everything else is cleaned once more, so the page is safe even for rows that were not saved through the models.
     */
    public static function seoBlock(?string $value): string
    {
        $value = (string) $value;

        if ($value === strip_tags($value)) {
            return nl2br(e($value));
        }

        return Purify::config('seo')->clean($value);
    }
}
