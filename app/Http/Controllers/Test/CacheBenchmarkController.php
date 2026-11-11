<?php

namespace App\Http\Controllers\Test;

use App\Cache\RegionCacheKeys;
use App\Http\Controllers\Controller;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\Cache;

/**
 * Local-only endpoint for measuring cache reads:
 * GET /test/cache-benchmark?key=region:3&iterations=1000
 */
class CacheBenchmarkController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $key = $request->string('key', 'region:3')->toString();
        $iterations = max(1, min(10_000, $request->integer('iterations', 1_000)));

        // Region cache entries live in the "regions" tag, so a plain Cache::get() won't find them.
        $tagged = $request->boolean('tagged', true) && Cache::getStore() instanceof TaggableStore;

        /** @var Repository $cache */
        $cache = $tagged
            ? Cache::tags([RegionCacheKeys::TAG])
            : Cache::store();

        $time = Benchmark::measure(
            fn () => $cache->get($key),
            $iterations,
        );

        // Benchmark::measure() returns only the duration, so read the value separately.
        $value = $cache->get($key);

        return response()->json([
            'key' => $key,
            'tagged' => $tagged,
            'iterations' => $iterations,
            'time_ms' => round($time, 4),
            'found' => $value !== null,
            'region_id' => is_array($value) ? ($value['id'] ?? null) : null,
            'name' => is_array($value) ? ($value['name'] ?? null) : null,
        ]);
    }
}
