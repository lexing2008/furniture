<?php

namespace App\Http\Services;

use App\Cache\RegionCacheKeys;
use App\Models\Region;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RegionService
{

    public function getRegionsTreeForPopup(Region $country): Collection
    {
        $key = RegionCacheKeys::countryAreas($country);

        // 1. Извлекаем из кэша (или формируем) чистый массив данных
        $rawTree = Cache::remember($key, now()->addWeek(), function () use ($country) {
            $areas = Region::query()
                ->where('parent_id', $country->id)
                ->where('is_active', true)
                ->get();

            if ($areas->isEmpty()) {
                return [];
            }

            // Получаем ТОЛЬКО активные города для найденных областей
            $cities = Region::query()
                ->whereIn('parent_id', $areas->pluck('id'))
                ->where('is_active', true)
                ->get()
                ->groupBy('parent_id');

            $sortByName = fn ($collection) => $collection->sortBy([
                // 1. Сначала столицы (true идет выше false)
                fn (Region $a, Region $b) => $b->is_capital <=> $a->is_capital,
                // 2. Затем по алфавиту без учета регистра
                fn (Region $a, Region $b) => mb_strtolower($a->name) <=> mb_strtolower($b->name),
            ])->values();

            // В кэш сохраняем только массивы (toArray), никаких незагруженных классов!
            return $sortByName($areas)->map(fn (Region $area) => [
                'region' => $area->toArray(),
                'cities' => $sortByName($cities[$area->id] ?? [])->map(fn (Region $city) => $city->toArray())->all(),
            ])->all();
        });

        // 2. ВОССТАНАВЛИВАЕМ МОДЕЛИ ДЛЯ ШАБЛОНА (Гидрация)
        // Превращаем массивы обратно в коллекции и живые объекты Region
        return collect($rawTree)->map(function ($item) {
            // Создаем экземпляр модели области из сохраненного массива
            $regionModel = (new Region)->newFromBuilder($item['region']);

            // Собираем коллекцию моделей городов
            $citiesCollection = collect($item['cities'])->map(function ($cityArray) {
                return (new Region)->newFromBuilder($cityArray);
            });

            return [
                'region' => $regionModel,
                'cities' => $citiesCollection, // Это снова полноценная Collection
            ];
        });
    }

    public function forgetCacheCountryAreas(Region $region): void
    {
        $country = $region->root();
        $key = RegionCacheKeys::countryAreas($country);
        Cache::forget($key);
    }
}
