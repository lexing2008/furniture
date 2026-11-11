<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class AdGalleryController extends Controller
{
    public function show(Region $region, Ad $ad): JsonResponse
    {
        abort_unless($ad->is_active && ! $ad->is_banned, 404);

        $images = $ad->images()->get();

        return response()->json([
            'total' => (int) $ad->images_count,
            'images' => $images
                ->slice(2)
                ->values()
                ->map(fn (AdImage $image) => [
                    'url' => $image->url(),
                    'alt' => $ad->title,
                    'width' => $image->width,
                    'height' => $image->height,
                ])
                ->all(),
        ]);
    }
}
