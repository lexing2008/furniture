<?php

namespace App\Http\Controllers;

use App\Http\Services\RegionService;
use App\Models\Region;
use Illuminate\View\View;

class RegionPageController extends Controller
{
    public function show(Region $region, RegionService $regionService): View
    {
        // todo: move to middleware
        abort_unless($region?->isAvailable(), 404);

        // страна
        if ($region->parent_id === null) {
            return view('region.country.show', [
                'seo' => $region->seo(),
                'areas' => $regionService->getAreasWithCities($region),
            ]);
        }

        // область
        if ($region->children->isNotEmpty()) {
            return view('region.region.show', [
                'seo' => $region->seo(),
            ]);
        }

        return view('region.show', [
            'seo' => $region->seo(),
        ]);
    }
}
