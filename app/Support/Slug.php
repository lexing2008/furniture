<?php

namespace App\Support;

use Illuminate\Support\Str;

class Slug
{
    /**
     * URL part from a name. The Russian table is used for every language:
     * it transliterates "Хороший" to "horoshiy" ("xorosii" with the default one) and keeps Latin as is.
     */
    public static function make(string $value): string
    {
        return Str::slug($value, '-', 'ru');
    }
}
