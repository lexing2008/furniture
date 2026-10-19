<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RegionRequest;
use App\Http\Services\RegionService;
use App\Models\Language;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegionController extends Controller
{
    public function __construct(
        private RegionService $regionService
    )
    {

    }
    public function index(): Response
    {
        // The tree is built in memory: regions are few, and a tree can't be paginated by SQL
        $regions = Region::tree()->map(fn (Region $region) => [
            'id' => $region->id,
            'name' => $region->name,
            'name_en' => $region->name_en,
            'subdomain' => $region->subdomain,
            'url' => $region->url(),
            'depth' => $region->depth,
            'lang' => $region->parent_id === null ? $region->lang : null,
            'is_capital' => $region->is_capital,
            'is_active' => $region->is_active,
        ]);

        return Inertia::render('regions/IndexRegions', [
            'regions' => $regions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('regions/CreateRegion', [
            'languages' => $this->languages(),
            'parents' => $this->parentOptions(),
            'domain' => config('app.domain'),
        ]);
    }

    public function store(RegionRequest $request): RedirectResponse
    {
        $region = Region::create($request->validated());
        $this->regionService->forgetCacheCountryAreas($region);

        return redirect()
            ->route('admin.regions.index')
            ->with('success', 'Регион добавлен.');
    }

    public function edit(Region $region): Response
    {
        return Inertia::render('regions/EditRegion', [
            'region' => $region->only([
                'id', 'parent_id', 'name', 'name_en', 'name_in', 'subdomain', 'lang', 'is_capital',
                'seo_title', 'seo_description', 'seo_block', 'is_active',
            ]),
            'languages' => $this->languages(),
            'parents' => $this->parentOptions($region),
            'domain' => config('app.domain'),
        ]);
    }

    public function update(RegionRequest $request, Region $region): RedirectResponse
    {
        $data = $request->validated();
        $this->regionService->forgetCacheCountryAreas($region);
        $region->update($data);
        $this->regionService->forgetCacheCountryAreas($region->fresh());

        return redirect()
            ->route('admin.regions.index')
            ->with('success', 'Регион сохранён.');
    }

    public function toggle(Region $region): RedirectResponse
    {
        $region->update(['is_active' => ! $region->is_active]);
        $this->regionService->forgetCacheCountryAreas($region);

        return back()->with('success', $region->is_active ? 'Регион активирован.' : 'Регион деактивирован.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: int, code: string, name: string}>
     */
    private function languages()
    {
        return Language::query()->orderBy('id')->get(['id', 'code', 'name']);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: int, name: string, depth: int}>
     */
    private function parentOptions(?Region $region = null)
    {
        return Region::allowedParents($region)->map(fn (Region $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'depth' => $r->depth,
        ]);
    }
}
