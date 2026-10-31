<?php

namespace App\Http\Controllers;

use App\Http\Services\RegionService;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class RegionPopupController extends Controller
{
    public function show(Region $region, RegionService $regionService): JsonResponse
    {
        $country = $region->root();

        return response()->json([
            'html' => view('layouts.partials._region-popup-content', [
                'region' => $region,
                'country' => $country,
                'areas' => $regionService->getAreasWithCities($country),
            ])->render(),
        ]);
    }
}
