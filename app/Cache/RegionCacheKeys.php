<?php

namespace App\Cache;

use App\Models\Region;

class RegionCacheKeys
{
    public static function countryAreas(Region $country): string
    {
        return 'regions:country:'. $country->id.':areas';
    }
}
