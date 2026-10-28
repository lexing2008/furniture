<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Placeholder category page and lazy-loaded category menu.
 */
class RegionCategoryController extends Controller
{
    public function show(Region $region, string $categorySlug): View
    {
        return view('region.category.show', [
            'categorySlug' => $categorySlug,
        ]);
    }

    public function children(Region $region, Category $category): JsonResponse
    {
        abort_unless($category->is_active, 404);

        $locale = app()->getLocale();

        $children = $category->children()
            ->with('translations')
            ->withCount(['children as active_children_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->where('is_active', true)
            ->get();

        $children = Category::sortForDisplay($children)
            ->map(function (Category $child) use ($locale, $region): ?array {
                $translation = $child->translations->firstWhere('lang', $locale);
                $slug = $translation?->slug ?? $child->slugIn($locale);

                if ($slug === '') {
                    return null;
                }

                return [
                    'id' => $child->id,
                    'name' => $translation?->name ?? $child->displayName(),
                    'slug' => $slug,
                    'has_children' => (int) $child->active_children_count > 0,
                    'url' => route('region.category', [
                        'region' => $region->subdomain,
                        'categorySlug' => $slug,
                    ]),
                    'children_url' => route('region.category.children', [
                        'region' => $region->subdomain,
                        'category' => $child->id,
                    ]),
                ];
            })
            ->filter()
            ->values();

        return response()->json(['categories' => $children]);
    }
}
