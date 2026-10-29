<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\Region;
use App\Services\FavoriteService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Region $region, FavoriteService $favorites): View
    {
        $items = $favorites->query()
            ->with(['ad.images', 'ad.company.phones', 'ad.category.translations', 'region'])
            ->whereHas('ad', function ($query) {
                $query->where('is_active', true)->where('is_banned', false);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('region.favorites.index', [
            'region' => $region,
            'items' => $items,
        ]);
    }

    public function toggle(Region $region, Ad $ad, FavoriteService $favorites): JsonResponse
    {
        abort_unless($ad->is_active && ! $ad->is_banned, 404);

        return response()->json([
            'favorited' => $favorites->toggle($ad, $region),
        ]);
    }
}
