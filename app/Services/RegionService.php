<?php

namespace App\Services;

use App\Cache\RegionCacheKeys;
use App\Models\Region;
use Illuminate\Support\Collection;

class RegionService
{
    /** @var array<int, Collection<int, array{region: Region, cities: Collection<int, Region>}>> */
    private array $areasWithCities = [];

    /** @var array<int, Collection<int, Region>> */
    private array $capitalCities = [];

    public function getAreasWithCities(Region $country): Collection
    {
        if (isset($this->areasWithCities[$country->id])) {
            return $this->areasWithCities[$country->id];
        }

        $key = RegionCacheKeys::countryAreas($country);

        $rawTree = RegionCacheKeys::store()->remember($key, now()->addWeek(), function () use ($country) {
            $areas = Region::query()
                ->where('country_id', $country->id)
                ->where('parent_id', $country->id)
                ->where('is_active', true)
                ->get();

            if ($areas->isEmpty()) {
                return [];
            }

            $cities = Region::query()
                ->where('country_id', $country->id)
                ->whereIn('parent_id', $areas->pluck('id'))
                ->where('is_active', true)
                ->get()
                ->groupBy('parent_id');

            $sortByName = fn ($collection) => $collection->sortBy([
                fn (Region $a, Region $b) => $b->is_capital <=> $a->is_capital,
                fn (Region $a, Region $b) => mb_strtolower($a->name) <=> mb_strtolower($b->name),
            ])->values();

            return $sortByName($areas)->map(fn (Region $area) => [
                'region' => $area->toArray(),
                'cities' => $sortByName($cities[$area->id] ?? [])->map(fn (Region $city) => $city->toArray())->all(),
            ])->all();
        });

        return $this->areasWithCities[$country->id] = collect($rawTree)->map(function ($item) {
            $regionModel = (new Region)->newFromBuilder($item['region']);
            $citiesCollection = collect($item['cities'])->map(
                fn ($cityArray) => (new Region)->newFromBuilder($cityArray),
            );

            return [
                'region' => $regionModel,
                'cities' => $citiesCollection,
            ];
        });
    }

    public function getCapitalCities(Region $country): Collection
    {
        if (isset($this->capitalCities[$country->id])) {
            return $this->capitalCities[$country->id];
        }

        $key = RegionCacheKeys::countryCapitals($country);

        $rawCities = RegionCacheKeys::store()->remember($key, now()->addWeek(), function () use ($country) {
            return Region::query()
                ->select(['id', 'subdomain', 'name', 'name_in', 'name_en'])
                ->where('country_id', $country->id)
                ->where('is_capital', true)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(fn (Region $city) => $city->only(['id', 'subdomain', 'name', 'name_in', 'name_en']))
                ->all();
        });

        return $this->capitalCities[$country->id] = collect($rawCities)->map(
            fn (array $city) => (new Region)->newFromBuilder($city),
        );
    }

    public function forgetCache(): void
    {
        $this->areasWithCities = [];
        $this->capitalCities = [];

        RegionCacheKeys::flush();
    }
}
