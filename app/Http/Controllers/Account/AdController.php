<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Account\Concerns\ResolvesCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AdRequest;
use App\Models\Ad;
use App\Models\Category;
use App\Models\Company;
use App\Models\Region;
use App\Services\AdFilterService;
use App\Services\AdImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AdController extends Controller
{
    use ResolvesCompany;

    public function index(Request $request): Response
    {
        $company = $this->company($request, ['region', 'country.countryData']);
        $country = $this->currentCountry($request, $company);
        $countryId = $company->country_id ?? $company->region?->root()->id;

        $status = $request->string('status')->toString();
        $search = trim((string) $request->input('search', ''));
        $language = $this->language($request, $company);
        $categoryId = $request->integer('category_id') ?: null;
        $areaId = $request->integer('area_id') ?: null;
        $cityId = $request->integer('city_id') ?: null;
        $priceFrom = $request->filled('price_from') && is_numeric($request->input('price_from'))
            ? (float) $request->input('price_from')
            : null;
        $priceTo = $request->filled('price_to') && is_numeric($request->input('price_to'))
            ? (float) $request->input('price_to')
            : null;
        $hasFilters = in_array($request->input('has_filters'), ['with', 'without'], true)
            ? (string) $request->input('has_filters')
            : null;
        $photo = in_array($request->input('photo'), ['with', 'without'], true)
            ? (string) $request->input('photo')
            : null;
        $hasVideo = in_array($request->input('has_video'), ['with', 'without'], true)
            ? (string) $request->input('has_video')
            : null;

        if ($cityId !== null && $areaId === null) {
            $areaId = Region::query()->whereKey($cityId)->value('parent_id');
        }

        if (! in_array($status, Ad::STATUSES, true)) {
            $status = 'all';
        }

        $adsQuery = $company->ads()
            ->with([
                'category.translations',
                'images',
                'filterInts:id,ad_id,filter_id',
                'filterFloats:id,ad_id,filter_id',
                'filterVariants:id,ad_id,filter_id',
            ])
            ->orderByDesc('id');

        if ($search !== '') {
            $adsQuery->where(function ($query) use ($search) {
                $like = '%'.$search.'%';

                $query->where('title', 'like', $like)
                    ->orWhere('article', 'like', $like)
                    ->orWhere('number', 'like', $like);
            });
        }

        if ($categoryId !== null) {
            $adsQuery->where('category_id', $categoryId);
        }

        if ($cityId !== null) {
            $adsQuery->whereHas('regions', fn ($query) => $query->where('regions.id', $cityId));
        } elseif ($areaId !== null) {
            $regionIds = Region::query()
                ->where('parent_id', $areaId)
                ->pluck('id')
                ->push($areaId)
                ->all();

            $adsQuery->whereHas('regions', fn ($query) => $query->whereIn('regions.id', $regionIds));
        }

        if ($priceFrom !== null) {
            $adsQuery->where('price', '>=', $priceFrom);
        }

        if ($priceTo !== null) {
            $adsQuery->where('price', '<=', $priceTo);
        }

        if ($hasFilters === 'with') {
            $adsQuery->where(function ($query) {
                $query->whereHas('filterInts')
                    ->orWhereHas('filterFloats')
                    ->orWhereHas('filterVariants');
            });
        } elseif ($hasFilters === 'without') {
            $adsQuery->whereDoesntHave('filterInts')
                ->whereDoesntHave('filterFloats')
                ->whereDoesntHave('filterVariants');
        }

        if ($photo === 'with') {
            $adsQuery->has('images');
        } elseif ($photo === 'without') {
            $adsQuery->whereDoesntHave('images');
        }

        if ($hasVideo === 'with') {
            $adsQuery->whereNotNull('video')->where('video', '!=', '');
        } elseif ($hasVideo === 'without') {
            $adsQuery->where(function ($query) {
                $query->whereNull('video')->orWhere('video', '');
            });
        }

        match ($status) {
            'active' => $adsQuery->active(),
            'disabled' => $adsQuery->disabled(),
            'moderation' => $adsQuery->moderation(),
            'banned' => $adsQuery->banned(),
            default => null,
        };

        $ads = $adsQuery->get()
            ->map(fn (Ad $ad) => [
                'id' => $ad->id,
                'title' => $ad->title,
                'article' => $ad->article,
                'number' => $ad->number,
                'category_id' => $ad->category_id,
                'category' => $ad->category->displayName(),
                'price' => (float) $ad->price,
                'currency' => $ad->currency,
                'thumb_url' => $ad->images->first()?->thumbnailUrl(100),
                'images_count' => $ad->images->count(),
                'filter_count' => $ad->filledFiltersCount(),
                'published_at' => $ad->published_at?->format('d.m.Y H:i'),
                'is_active' => $ad->is_active,
                'is_moderated' => $ad->is_moderated,
                'is_banned' => $ad->is_banned,
                'created_at' => $ad->created_at?->format('d.m.Y'),
            ]);

        return Inertia::render('Ads/Index', [
            'ads' => $ads,
            'status' => $status,
            'search' => $search,
            'currencyDecimals' => (int) ($country?->countryData?->currency_decimals ?? 2),
            'adFilters' => [
                'category_id' => $categoryId,
                'area_id' => $areaId,
                'city_id' => $cityId,
                'price_from' => $priceFrom,
                'price_to' => $priceTo,
                'has_filters' => $hasFilters,
                'photo' => $photo,
                'has_video' => $hasVideo,
            ],
            'categoryOptions' => $this->categoryOptions($language),
            'regionFilterOptions' => $this->regionFilterOptions($countryId, $language),
        ]);
    }

    public function create(Request $request, AdFilterService $filterService): Response
    {
        $company = $this->company($request, ['region', 'country.countryData']);
        $country = $this->currentCountry($request, $company);
        $countryId = $company->country_id ?? $company->region?->root()->id;
        $language = $this->language($request, $company);
        $decimals = (int) ($country?->countryData?->currency_decimals ?? 2);
        $source = null;
        $filters = [];

        if ($request->integer('copy') > 0) {
            $copy = $company->ads()->findOrFail($request->integer('copy'));
            $filters = $filterService->forCategory($copy->category_id, $copy, $language);
            $source = [
                'category_id' => $copy->category_id,
                'warranty_value' => $copy->warranty_value,
                'warranty_unit' => $copy->warranty_unit,
                'description' => $copy->description ?? '',
                'regions' => $copy->regions()->pluck('regions.id')->map(fn ($id) => (int) $id)->values()->all(),
            ];
        }

        return Inertia::render('Ads/Create', [
            'categories' => Category::selectableTree($language),
            'regions' => Region::cityTree($countryId),
            'defaultRegionId' => $company->region_id,
            'currency' => $country?->countryData?->currency,
            'currencyDecimals' => $decimals,
            'source' => $source,
            'filters' => $filters,
        ]);
    }

    public function store(AdRequest $request, AdImageService $imageService): RedirectResponse
    {
        $company = $this->company($request);

        $ad = DB::transaction(function () use ($request, $company, $imageService) {
            $ad = new Ad($request->adData());
            $ad->company_id = $company->id;
            $ad->currency = (string) $request->currency();
            $ad->is_moderated = false;
            $ad->is_banned = false;
            $ad->published_at = now();
            $ad->save();

            $ad->regions()->sync($request->validated('regions'));
            $ad->syncFilters($request->filterValues());

            $images = $request->imageData();
            $pathsToDelete = $imageService->sync(
                $ad,
                $request->user()->id,
                $request->categorySlug(),
                $images['existing'],
                $images['new'],
                $images['deleted'],
            );

            return [$ad, $pathsToDelete];
        });

        [$ad, $pathsToDelete] = $ad;
        Storage::disk('public')->delete($pathsToDelete);

        return redirect()
            ->route('account.ads.edit', $ad)
            ->with('success', __('Товар/услуга сохранены.'));
    }

    public function edit(Request $request, AdFilterService $filterService): Response
    {
        $company = $this->company($request, ['region', 'country.countryData']);
        $ad = $this->ad($request, $company);
        $country = $this->currentCountry($request, $company);
        $countryId = $company->country_id ?? $company->region?->root()->id;
        $decimals = (int) ($country?->countryData?->currency_decimals ?? 2);

        return Inertia::render('Ads/Edit', [
            'ad' => [
                'id' => $ad->id,
                'title' => $ad->title,
                'number' => $ad->number,
                'article' => $ad->article ?? '',
                'category_id' => $ad->category_id,
                'description' => $ad->description ?? '',
                'price' => number_format((float) $ad->price, $decimals, '.', ''),
                'warranty_value' => $ad->warranty_value,
                'warranty_unit' => $ad->warranty_unit,
                'video' => $ad->video ?? '',
                'is_active' => $ad->is_active,
                'regions' => $ad->regions()->pluck('regions.id')->map(fn ($id) => (int) $id)->values()->all(),
                'images' => $ad->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->url(),
                    'thumb_630_url' => $image->thumbnailUrl(630),
                    'thumb_100_url' => $image->thumbnailUrl(100),
                    'order' => $image->order,
                ])->values()->all(),
            ],
            'categories' => Category::selectableTree($this->language($request, $company)),
            'regions' => Region::cityTree($countryId),
            'currency' => $country?->countryData?->currency,
            'currencyDecimals' => $decimals,
            'filters' => $filterService->forCategory($ad->category_id, $ad, $this->language($request, $company)),
        ]);
    }

    public function updatePrice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:99999999999.9999'],
        ], [
            'price.required' => __('Укажите цену.'),
            'price.numeric' => __('Цена должна быть числом.'),
            'price.min' => __('Цена не может быть отрицательной.'),
        ]);

        $company = $this->company($request);
        $ad = $this->ad($request, $company);

        $ad->update([
            'price' => $data['price'],
            'published_at' => now(),
        ]);

        return response()->json([
            'price' => (float) $ad->price,
        ]);
    }

    public function actualize(Request $request): JsonResponse
    {
        $company = $this->company($request);
        $ad = $this->ad($request, $company);

        $ad->update(['published_at' => now()]);

        return response()->json([
            'published_at' => $ad->published_at?->format('d.m.Y H:i'),
        ]);
    }

    public function update(AdRequest $request, AdImageService $imageService): SymfonyResponse
    {
        $company = $this->company($request);
        $ad = $this->ad($request, $company);

        $pathsToDelete = DB::transaction(function () use ($request, $ad, $imageService) {
            $images = $request->imageData();
            $descriptionChanged = (string) ($ad->description ?? '') !== (string) ($request->input('description') ?? '');
            $videoAdded = filled($request->input('video')) && $request->input('video') !== $ad->video;
            $resetModeration = $ad->title !== $request->input('title')
                || $descriptionChanged
                || $videoAdded
                || count($images['new']) > 0;

            $ad->update([
                ...$request->adData(),
                'currency' => (string) $request->currency(),
                'is_moderated' => $ad->is_moderated && ! $resetModeration,
                'published_at' => now(),
            ]);
            $ad->regions()->sync($request->validated('regions'));
            $ad->syncFilters($request->filterValues());

            return $imageService->sync(
                $ad,
                $request->user()->id,
                $request->categorySlug(),
                $images['existing'],
                $images['new'],
                $images['deleted'],
            );
        });

        Storage::disk('public')->delete($pathsToDelete);

        return Inertia::location(
            redirect()
                ->route('account.ads.edit', $ad)
                ->with('success', __('Товар/услуга сохранены.'))
        );
    }

    public function filters(Request $request, AdFilterService $filterService): JsonResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer'],
        ]);

        $category = Category::query()
            ->whereKey((int) $data['category_id'])
            ->where('is_active', true)
            ->whereDoesntHave('children')
            ->first();

        if ($category === null) {
            return response()->json([
                'message' => __('Выберите категорию из списка.'),
                'errors' => ['category_id' => [__('Выберите категорию из списка.')]],
            ], 422);
        }

        return response()->json([
            'filters' => $filterService->forCategory($category->id, null, app()->getLocale()),
        ]);
    }

    private function ad(Request $request, Company $company): Ad
    {
        $value = $request->route('ad');
        $ad = $value instanceof Ad ? $value : Ad::query()->findOrFail($value);

        abort_unless($ad->company_id === $company->id, 404);

        return $ad->load(['images', 'regions', 'filterInts', 'filterFloats', 'filterVariants']);
    }

    /**
     * @return array<int, array{id: int, label: string}>
     */
    private function categoryOptions(?string $language): array
    {
        $options = [];

        $walk = function (array $nodes, array $path) use (&$walk, &$options): void {
            foreach ($nodes as $node) {
                $next = [...$path, $node['name']];

                if ($node['selectable']) {
                    $options[] = ['id' => $node['id'], 'label' => implode(' / ', $next)];
                } else {
                    $walk($node['children'], $next);
                }
            }
        };

        $walk(Category::selectableTree($language), []);

        return $options;
    }

    /**
     * @return array{areas: array<int, array{id: int, label: string}>, cities: array<int, array{id: int, label: string, area_id: int}>}
     */
    private function regionFilterOptions(?int $countryId, ?string $language): array
    {
        $areas = [];
        $cities = [];
        $isEnglish = $language === 'en';

        foreach (Region::cityTree($countryId) as $country) {
            foreach ($country['children'] as $area) {
                $areaName = $isEnglish ? ($area['name_en'] ?? $area['name']) : $area['name'];
                $areas[] = ['id' => $area['id'], 'label' => $areaName];

                foreach ($area['children'] as $city) {
                    $cities[] = [
                        'id' => $city['id'],
                        'label' => $isEnglish ? ($city['name_en'] ?? $city['name']) : $city['name'],
                        'area_id' => $area['id'],
                    ];
                }
            }
        }

        return ['areas' => $areas, 'cities' => $cities];
    }

    private function currentCountry(Request $request, Company $company): ?Region
    {
        $region = Region::fromHost($request->getHost()) ?? $company->region;

        return $region?->root() ?? $company->country;
    }

    private function language(Request $request, Company $company): ?string
    {
        return (Region::fromHost($request->getHost()) ?? $company->region)?->language();
    }
}
