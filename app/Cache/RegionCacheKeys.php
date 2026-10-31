<?php

namespace App\Cache;

use App\Models\Region;
use Illuminate\Support\Facades\Cache;

class RegionCacheKeys
{
    private const VERSION_KEY = 'tag_regions_v';

    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    public static function countryAreas(Region|int $country): string
    {
        $countryId = $country instanceof Region ? $country->id : $country;

        return 'regions:tag_regions_v'.self::version().':country:'.$countryId;
    }

    public static function countryCapitals(Region|int $country): string
    {
        return self::countryAreas($country).':capitals';
    }

    public static function bumpVersion(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }
}
