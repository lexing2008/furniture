<?php

namespace Tests\Feature;

use App\Cache\RegionCacheKeys;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheBenchmarkControllerTest extends TestCase
{
    public function test_benchmark_reads_a_tagged_region_cache_entry(): void
    {
        Cache::flush();
        Cache::tags([RegionCacheKeys::TAG])->put('region:3', [
            'id' => 3,
            'name' => 'Брест',
        ], now()->addWeek());

        $this->getJson('/test/cache-benchmark?key=region:3&iterations=1')
            ->assertOk()
            ->assertJson([
                'key' => 'region:3',
                'tagged' => true,
                'iterations' => 1,
                'found' => true,
                'region_id' => 3,
                'name' => 'Брест',
            ])
            ->assertJsonStructure(['time_ms']);
    }
}
