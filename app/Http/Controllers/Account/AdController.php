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
        $regionId = $request->integer('region_id') ?: null;
        $priceFrom = $request->filled('price_from') && is_numeric($request->input('price_from'))
            ? (float) $request->input('price_from')
            : null;
        $priceTo = $request->filled('price_to') && is_numeric($request->input('price_to'))
            ? (float) $request->input('price_to')
            : null;
        $hasFilters = $request->boolean('has_filters');
        $photo = in_array($request->input('photo'), ['with', 'without'], true)
            ? (string) $request->input('photo')
            : null;
        $hasVideo = $request->boolean('has_video');

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

        if ($regionId !== null) {
            $adsQuery->whereHas('regions', fn ($query) => $query->where('regions.id', $regionId));
        }

        if ($priceFrom !== null) {
            $adsQuery->where('price', '>=', $priceFrom);
        }

        if ($priceTo !== null) {
            $adsQuery->where('price', '<=', $priceTo);
        }

        if ($hasFilters) {
            $adsQuery->where(function ($query) {
                $query->whereHas('filterInts')
                    ->orWhereHas('filterFloats')
                    ->orWhereHas('filterVariants');
            });
        }

        if ($photo === 'with') {
            $adsQuery->has('images');
        } elseif ($photo === 'without') {
            $adsQuery->whereDoesntHave('images');
        }

        if ($hasVideo) {
            $adsQuery->whereNotNull('video')->where('video', '!=', '');
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
                'category' => $ad->category->displayName(),
                'price' => (float) $ad->price,
                'currency' => $ad->currency,
                'thumb_url' => $ad->images->first()?->thumbnailUrl(100),
                'filter_count' => $ad->filledFiltersCount(),
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
                'region_id' => $regionId,
                'price_from' => $priceFrom,
                'price_to' => $priceTo,
                'has_filters' => $hasFilters,
                'photo' => $photo,
                'has_video' => $hasVideo,
            ],
            'categoryOptions' => $this->categoryOptions($language),
            'regionOptions' => $this->regionOptions($countryId, $language),
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
            'companyWebsite' => $company->website,
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
            'companyWebsite' => $company->website,
            'filters' => $filterService->forCategory($ad->category_id, $ad, $this->language($request, $company)),
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
     * @return array<int, array{id: int, label: string}>
     */
    private function regionOptions(?int $countryId, ?string $language): array
    {
        $options = [];
        $isEnglish = $language === 'en';

        $walk = function (array $nodes, array $path) use (&$walk, &$options, $isEnglish): void {
            foreach ($nodes as $node) {
                $name = $isEnglish ? ($node['name_en'] ?? $node['name']) : $node['name'];
                $next = [...$path, $name];

                if ($node['selectable']) {
                    $options[] = ['id' => $node['id'], 'label' => implode(' / ', array_slice($next, -2))];
                } else {
                    $walk($node['children'], $next);
                }
            }
        };

        $walk(Region::cityTree($countryId), []);

        return $options;
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
