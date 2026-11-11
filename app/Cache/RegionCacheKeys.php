<?php

namespace App\Cache;

use App\Models\Region;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

class RegionCacheKeys
{
    public const TAG = 'regions';

    private const VERSION_KEY = 'tag_regions_v';

    private const MODEL_PREFIX = 'region:';

    private const DOMAIN_PREFIX = 'region:domain:';

    public static function version(): int
    {
        return (int) Cache::get(self::VERSION_KEY, 1);
    }

    /**
     * Cache key of the region model: "region:12".
     */
    public static function model(Region|int $region): string
    {
        $id = $region instanceof Region ? $region->getKey() : $region;

        return self::MODEL_PREFIX.$id;
    }

    /**
     * Cache key that stores the model cache key of a region subdomain: "region:domain:brest" => "region:12".
     */
    public static function domain(string $subdomain): string
    {
        return self::DOMAIN_PREFIX.$subdomain;
    }

    public static function idFromModelKey(string $key): int
    {
        return (int) substr($key, strlen(self::MODEL_PREFIX));
    }

    /**
     * Drops the cached region and its subdomain key. Tagged stores drop every region cache at once.
     */
    public static function forgetRegion(Region $region, ?string $oldSubdomain = null): void
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags([self::TAG])->flush();

            return;
        }

        Cache::forget(self::model($region));

        if (filled($region->subdomain)) {
            Cache::forget(self::domain($region->subdomain));
        }

        if (filled($oldSubdomain) && $oldSubdomain !== $region->subdomain) {
            Cache::forget(self::domain($oldSubdomain));
        }
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

    public static function countryData(Region|int $country): string
    {
        return self::countryAreas($country).':data';
    }

    public static function forgetCountryData(Region|int $country): void
    {
        $countryId = $country instanceof Region ? $country->id : $country;

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(['regions', "region.{$countryId}"])->flush();
        }

        self::bumpVersion();
    }

    public static function bumpVersion(): void
    {
        Cache::forever(self::VERSION_KEY, self::version() + 1);
    }
}
