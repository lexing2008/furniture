<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\View\View;

/**
 * Placeholder for a category page. It keeps the common header, footer and menu.
 */
class RegionCategoryController extends Controller
{
    public function show(Region $region, string $categorySlug): View
    {
        return view('region.category.show', [
            'categorySlug' => $categorySlug,
        ]);
    }
}
