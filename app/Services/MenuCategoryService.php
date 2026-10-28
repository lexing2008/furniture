<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Active categories shown in the site header and footer.
 *
 * The result depends only on the interface language. URLs are built by the
 * consumer because they depend on the current region.
 */
class MenuCategoryService
{
    private const CACHE_PREFIX = 'menu_categories:v3:';

    /**
     * @return Collection<int, array{id: int, name: string, menu_name: string, slug: string, has_children: bool}>
     */
    public static function forLocale(?string $locale = null): Collection
    {
        $locale ??= app()->getLocale();

        $items = Cache::remember(
            self::cacheKey($locale),
            now()->addDays(3),
            function () use ($locale): array {
                $categories = Category::query()
                    ->with('translations')
                    ->withCount(['children as active_children_count' => function ($query) {
                        $query->where('is_active', true);
                    }])
                    ->where('is_active', true)
                    ->whereHas('translations', function ($query) use ($locale) {
                        $query->where('lang', $locale)
                            ->whereNotNull('menu_name')
                            ->where('menu_name', '!=', '')
                            ->whereNotNull('slug')
                            ->where('slug', '!=', '');
                    })
                    ->get();

                return Category::sortForDisplay($categories)
                    ->map(function (Category $category) use ($locale): array {
                        $translation = $category->translations->firstWhere('lang', $locale);

                        return [
                            'id' => $category->id,
                            'name' => $translation?->name ?? $category->displayName(),
                            'menu_name' => $translation?->menu_name ?? $category->menuName(),
                            'slug' => $translation?->slug ?? $category->slugIn($locale),
                            'has_children' => (int) $category->active_children_count > 0,
                        ];
                    })
                    ->values()
                    ->all();
            },
        );

        // File cache is not allowed to unserialize classes; keep arrays in the cache.
        return collect($items);
    }

    /**
     * Invalidate the menu of every supported language.
     */
    public static function clear(): void
    {
        foreach (Language::query()->pluck('code') as $locale) {
            Cache::forget(self::cacheKey((string) $locale));
        }
    }

    private static function cacheKey(string $locale): string
    {
        return self::CACHE_PREFIX.$locale;
    }
}
