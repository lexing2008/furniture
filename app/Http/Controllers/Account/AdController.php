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

        $status = $request->string('status')->toString();

        if (! in_array($status, Ad::STATUSES, true)) {
            $status = 'all';
        }

        $adsQuery = $company->ads()
            ->with('category.translations')
            ->orderByDesc('id');

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
                'number' => $ad->number,
                'category' => $ad->category->displayName(),
                'price' => (float) $ad->price,
                'currency' => $ad->currency,
                'created_at' => $ad->created_at?->format('d.m.Y'),
            ]);

        return Inertia::render('Ads/Index', [
            'ads' => $ads,
            'status' => $status,
            'currencyDecimals' => (int) ($country?->countryData?->currency_decimals ?? 2),
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
                'price' => number_format((float) $copy->price, $decimals, '.', ''),
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
