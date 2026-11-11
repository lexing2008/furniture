<?php

namespace Tests\Feature;

use App\Cache\RegionCacheKeys;
use App\Models\Region;
use Illuminate\Cache\TaggedCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RegionRouteCacheTest extends TestCase
{
    use RefreshDatabase;

    private Region $country;

    private Region $area;

    private Region $city;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->country = Region::create([
            'name' => 'Беларусь',
            'name_en' => 'Belarus',
            'name_in' => 'Беларуси',
            'subdomain' => 'belarus',
            'timezone' => 'Europe/Minsk',
            'is_active' => true,
        ]);
        $this->area = Region::create([
            'name' => 'Брестская область',
            'name_en' => 'Brest region',
            'name_in' => 'Брестской области',
            'subdomain' => 'brest-region',
            'parent_id' => $this->country->id,
            'is_active' => true,
        ]);
        $this->city = Region::create([
            'name' => 'Брест',
            'name_en' => 'Brest',
            'name_in' => 'Бресте',
            'subdomain' => 'brest',
            'parent_id' => $this->area->id,
            'is_capital' => true,
            'is_active' => true,
        ]);
    }

    private function regionCache(): TaggedCache
    {
        return Cache::tags([RegionCacheKeys::TAG]);
    }

    public function test_region_is_cached_by_id_and_the_subdomain_key_points_to_the_region_key(): void
    {
        $this->get('http://brest.furniture.test/')->assertOk();

        $regionKey = RegionCacheKeys::model($this->city);

        $this->assertSame($regionKey, $this->regionCache()->get('region:domain:brest'));
        $this->assertTrue($this->regionCache()->has($regionKey));
        $this->assertSame('Брест', $this->regionCache()->get($regionKey)['name']);
    }

    public function test_cold_cache_resolves_a_region_with_a_single_database_query(): void
    {
        Route::domain('{region}.'.config('app.domain'))
            ->get('binding_test', fn (Region $region) => (string) $region->id)
            ->middleware(SubstituteBindings::class);

        DB::enableQueryLog();

        $this->get('http://brest.furniture.test/binding_test')
            ->assertOk()
            ->assertSee((string) $this->city->id);

        $queries = collect(DB::getQueryLog())->pluck('query');

        $this->assertCount(
            1,
            $queries->filter(fn (string $query) => str_contains($query, 'from "regions"')),
        );
    }

    public function test_region_cache_is_forgotten_when_the_subdomain_changes(): void
    {
        $this->get('http://brest.furniture.test/')->assertOk();

        $this->city->update(['subdomain' => 'brest-city']);

        $this->assertFalse($this->regionCache()->has('region:domain:brest'));
        $this->assertFalse($this->regionCache()->has(RegionCacheKeys::model($this->city)));

        $this->get('http://brest-city.furniture.test/')->assertOk();

        $this->assertSame(
            RegionCacheKeys::model($this->city),
            $this->regionCache()->get('region:domain:brest-city'),
        );

        $this->get('http://brest.furniture.test/')->assertNotFound();
    }

    public function test_region_cache_is_forgotten_when_the_region_is_deactivated_or_deleted(): void
    {
        $this->get('http://brest.furniture.test/')->assertOk();

        $this->city->update(['is_active' => false]);

        $this->assertFalse($this->regionCache()->has('region:domain:brest'));
        $this->assertFalse($this->regionCache()->has(RegionCacheKeys::model($this->city)));
        $this->get('http://brest.furniture.test/')->assertNotFound();

        $this->city->update(['is_active' => true]);
        $this->get('http://brest.furniture.test/')->assertOk();

        $this->city->delete();

        $this->assertFalse($this->regionCache()->has('region:domain:brest'));
        $this->assertFalse($this->regionCache()->has(RegionCacheKeys::model($this->city)));
    }

    public function test_region_cache_is_forgotten_for_a_non_taggable_store(): void
    {
        Cache::setDefaultDriver('database');
        Cache::flush();

        $this->get('http://brest.furniture.test/')->assertOk();

        $this->assertSame(RegionCacheKeys::model($this->city), Cache::get('region:domain:brest'));
        $this->assertTrue(Cache::has(RegionCacheKeys::model($this->city)));

        $this->city->update(['subdomain' => 'brest-city']);

        $this->assertFalse(Cache::has('region:domain:brest'));
        $this->assertFalse(Cache::has(RegionCacheKeys::model($this->city)));

        $this->get('http://brest-city.furniture.test/')->assertOk();

        $this->assertSame(RegionCacheKeys::model($this->city), Cache::get('region:domain:brest-city'));
    }
}
