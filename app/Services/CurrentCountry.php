<?php

namespace App\Services;

use App\Models\Region;

/**
 * The country (root region) of the current route.
 */
final class CurrentCountry
{
    public function get(): ?Region
    {
        $region = request()->route('region');

        if (! $region instanceof Region) {
            return null;
        }

        return $region->root()->loadMissing('countryData');
    }
}
