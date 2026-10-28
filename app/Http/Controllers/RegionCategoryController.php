<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Category;
use App\Models\FilterVariant;
use App\Models\Region;
use App\Services\AdFilterService;
use App\Services\CategorySeoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Category page and lazy-loaded category menu.
 */
class RegionCategoryController extends Controller
{
    public function show(Request $request, AdFilterService $filterService, CategorySeoService $seoService, Region $region, string $categorySlug, ?string $filterSlugs = null): View
    {
        $locale = app()->getLocale();

        $category = Category::query()
            ->with(['translations', 'parent'])
            ->where('is_active', true)
            ->whereHas('translations', function ($query) use ($categorySlug, $locale) {
                $query->where('lang', $locale)->where('slug', $categorySlug);
            })
            ->firstOrFail();

        $root = $category;

        while ($root->parent_id !== null) {
            $root->loadMissing('parent');
            $root = $root->parent;
        }

        $sidebarCategories = Category::sortForDisplay(
            $root->children()
                ->with('translations')
                ->where('is_active', true)
                ->get()
        )->filter(fn (Category $item) => $item->slugIn($locale) !== '')->values();

        $categoryIds = $this->categoryIdsWithDescendants($category);
        $filters = $filterService->forCategory($category->id, null, $locale);
        $filterValues = $request->input('filters', []);
        $filterValues = is_array($filterValues) ? $filterValues : [];
        $priceFrom = $request->filled('price_from') && is_numeric($request->input('price_from'))
            ? max(0, (float) $request->input('price_from'))
            : null;
        $priceTo = $request->filled('price_to') && is_numeric($request->input('price_to'))
            ? max(0, (float) $request->input('price_to'))
            : null;
        $requestedPerPage = $request->integer('per_page');
        $perPage = in_array($requestedPerPage, [10, 20, 50, 100], true)
            ? $requestedPerPage
            : (int) $request->session()->get('category_per_page', 20);

        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        if (in_array($requestedPerPage, [10, 20, 50, 100], true)) {
            $request->session()->put('category_per_page', $perPage);
        }
        $categoryUrl = route('region.category', [
            'region' => $region->subdomain,
            'categorySlug' => $categorySlug,
            'filterSlugs' => null,
        ]);

        $selectedVariantSlugs = $filterSlugs === null
            ? []
            : array_values(array_filter(array_unique(explode(';', $filterSlugs))));
        $selectedVariant = null;
        $selectedVariantNames = [];

        if ($selectedVariantSlugs !== []) {
            $variants = FilterVariant::query()
                ->with(['translations', 'filter'])
                ->whereHas('translations', function (Builder $query) use ($locale, $selectedVariantSlugs) {
                    $query->where('lang', $locale)
                        ->whereIn('slug', $selectedVariantSlugs)
                        ->where('is_active', true);
                })
                ->whereHas('filter', function (Builder $query) use ($category) {
                    $query->where('category_id', $category->id)->where('is_active', true);
                })
                ->get();

            if ($variants->count() !== count($selectedVariantSlugs)) {
                abort(404);
            }

            if ($variants->count() === 1) {
                $selectedVariant = $variants->first();
            }

            $variantsBySlug = [];

            foreach ($variants as $variant) {
                foreach ($variant->translations as $variantTranslation) {
                    if ($variantTranslation->lang === $locale && in_array($variantTranslation->slug, $selectedVariantSlugs, true)) {
                        $variantsBySlug[$variantTranslation->slug] = $variant;
                    }
                }
            }

            foreach ($selectedVariantSlugs as $selectedSlug) {
                $variant = $variantsBySlug[$selectedSlug] ?? null;
                $variantTranslation = $variant?->translations->firstWhere('lang', $locale);
                $selectedVariantNames[] = $variantTranslation?->name_full ?: $variant?->nameIn($locale);
            }

            $selectedVariantNames = array_values(array_filter($selectedVariantNames));

            foreach ($variants->groupBy('filter_id') as $filterId => $group) {
                $type = $group->first()->filter->type->value;
                $ids = $group->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();

                $filterValues[$filterId] = [
                    'values' => $ids->all(),
                ];
            }
        }

        $adsQuery = Ad::query()
            ->with(['images', 'company.phones', 'company.region', 'category.translations'])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->where('is_banned', false);

        foreach ($filters as $filter) {
            $this->applyFilter($adsQuery, $filter, $filterValues[$filter['id']] ?? null);
        }

        if ($priceFrom !== null) {
            $adsQuery->where('price', '>=', $priceFrom);
        }

        if ($priceTo !== null) {
            $adsQuery->where('price', '<=', $priceTo);
        }

        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['published', 'price_asc', 'price_desc'], true) ? $sort : 'published';

        match ($sort) {
            'price_asc' => $adsQuery->orderBy('price')->orderByDesc('id'),
            'price_desc' => $adsQuery->orderByDesc('price')->orderByDesc('id'),
            default => $adsQuery->orderByDesc('published_at')->orderByDesc('id'),
        };

        $ads = $adsQuery->paginate($perPage)->withQueryString();

        $perPageUrls = [];

        foreach ([10, 20, 50, 100] as $perPageOption) {
            $query = array_merge($request->except(['page', 'per_page']), ['per_page' => $perPageOption]);
            $perPageUrls[$perPageOption] = url()->current().'?'.http_build_query($query);
        }

        $sortUrls = [];
        $sortOptions = [
            'published' => __('Сначала актуальные'),
            'price_asc' => __('Цена: по возрастанию'),
            'price_desc' => __('Цена: по убыванию'),
        ];

        foreach (array_keys($sortOptions) as $sortKey) {
            $query = array_merge($request->except(['sort', 'page']), ['sort' => $sortKey]);
            $sortUrls[$sortKey] = url()->current().'?'.http_build_query($query);
        }

        $translation = $category->translations->firstWhere('lang', $locale);
        $seo = $selectedVariant === null
            ? $seoService->forCategory($category, $translation, $region)
            : $seoService->forVariant(
                $selectedVariant,
                $selectedVariant->translations->firstWhere('lang', $locale),
                $category,
                $translation,
                $region,
            );
        $categoryName = $category->nameIn($locale);
        $regionNameIn = $locale === 'en' ? $region->name_en : $region->name_in;
        $pageHeading = $selectedVariantNames === []
            ? __(':category в :region', ['category' => $categoryName, 'region' => $regionNameIn])
            : __(':names в :region', ['names' => implode(', ', $selectedVariantNames), 'region' => $regionNameIn]);

        $pageNumber = $ads->currentPage();
        $pageSuffix = $pageNumber > 1
            ? __(' — Страница :page', ['page' => $pageNumber])
            : '';
        $pageTitle = ($seo['seo_title'] ?: $categoryName).$pageSuffix;
        $pageDescription = (string) ($seo['seo_description'] ?? '');

        if ($pageNumber > 1 && filled($pageDescription)) {
            $pageDescription = rtrim($pageDescription).__(' , страница :page', ['page' => $pageNumber]);
        }

        $pageHeading .= $pageSuffix;
        $filterPageUrl = url()->current()
            .($request->except('page') !== [] ? '?'.http_build_query($request->except('page')) : '');

        return view('region.category.show', [
            'category' => $category,
            'categoryName' => $categoryName,
            'pageHeading' => $pageHeading,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageNumber' => $pageNumber,
            'filterPageUrl' => $filterPageUrl,
            'selectedVariantNames' => $selectedVariantNames,
            'root' => $root,
            'rootName' => $root->nameIn($locale),
            'sidebarCategories' => $sidebarCategories,
            'filters' => $filters,
            'filterValues' => $filterValues,
            'priceFrom' => $priceFrom,
            'priceTo' => $priceTo,
            'categoryUrl' => $categoryUrl,
            'ads' => $ads,
            'seo' => $seo,
            'sort' => $sort,
            'sortOptions' => $sortOptions,
            'sortUrls' => $sortUrls,
            'perPage' => $perPage,
            'perPageUrls' => $perPageUrls,
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

    /**
     * The category itself and all active descendants, so a top-level category
     * can show the goods of its subcategories.
     *
     * @return array<int, int>
     */
    private function categoryIdsWithDescendants(Category $category): array
    {
        $ids = [$category->id];
        $frontier = [$category->id];

        while ($frontier !== []) {
            $frontier = Category::query()
                ->whereIn('parent_id', $frontier)
                ->where('is_active', true)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $ids = [...$ids, ...$frontier];
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array{id: int, name: string, type: string, variants: array<int, array{id: int, name: string}>, unit: ?string}  $filter
     */
    private function applyFilter(Builder $query, array $filter, mixed $value): void
    {
        if (! is_array($value)) {
            $value = [];
        }

        if (in_array($filter['type'], ['checkbox', 'radio'], true)) {
            $rawValues = $value['values'] ?? [];
            $variantIds = collect(is_array($rawValues) ? $rawValues : [$rawValues])
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if ($variantIds === []) {
                return;
            }

            $query->whereHas('filterVariants', function (Builder $variantQuery) use ($filter, $variantIds) {
                $variantQuery->where('filter_id', $filter['id'])
                    ->whereIn('filter_variant_id', $variantIds);
            });

            return;
        }

        $min = $value['min'] ?? null;
        $max = $value['max'] ?? null;
        $min = is_numeric($min) ? (float) $min : null;
        $max = is_numeric($max) ? (float) $max : null;

        if ($min === null && $max === null) {
            return;
        }

        $relation = $filter['type'] === 'input_int' ? 'filterInts' : 'filterFloats';

        $query->whereHas($relation, function (Builder $valueQuery) use ($filter, $min, $max) {
            $valueQuery->where('filter_id', $filter['id']);

            if ($min !== null) {
                $valueQuery->where('value', '>=', $min);
            }

            if ($max !== null) {
                $valueQuery->where('value', '<=', $max);
            }
        });
    }
}
