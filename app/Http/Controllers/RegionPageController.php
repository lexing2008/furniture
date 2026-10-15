<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class RegionPageController extends Controller
{
    public function show(string $subdomain): View
    {
        $region = Region::query()->where('subdomain', $subdomain)->first();

        abort_unless($region?->isAvailable(), 404);

        $country = $region->root();

        $language = $country->lang;

        if ($language !== null) {
            App::setLocale($language);
        }

        $active = Region::query()->where('is_active', true)->get()->groupBy('parent_id');

        $byName = fn ($regions) => collect($regions)->sortBy(fn (Region $r) => mb_strtolower($r->name));

        // Areas of the country with their cities, only the active ones
        $areas = $byName($active[$country->id] ?? [])->map(fn (Region $area) => [
            'region' => $area,
            'cities' => $byName($active[$area->id] ?? []),
        ]);

        return view('region.show', [
            'region' => $region,
            'country' => $country,
            'language' => $language ?? config('app.locale'),
            'seo' => $region->seo(),
            'areas' => $areas,
        ]);
    }
}
