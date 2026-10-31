<?php

namespace Tests\Feature;

use App\Cache\RegionCacheKeys;
use App\Http\Services\RegionService;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegionPopupTest extends TestCase
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
            'lang' => null,
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

    public function test_country_page_lists_areas_and_cities(): void
    {
        $this->get('http://belarus.furniture.test/')
            ->assertOk()
            ->assertSee('Беларусь')
            ->assertSee('Брестская область')
            ->assertSee('Мебель в Бресте');
    }

    public function test_region_popup_content_is_loaded_with_a_versioned_cache_key(): void
    {
        $response = $this->getJson('http://brest.furniture.test/regions/popup')
            ->assertOk()
            ->assertJsonStructure(['html']);

        $html = $response->json('html');

        $this->assertStringContainsString('Брестская область', $html);
        $this->assertStringContainsString('Брест', $html);
        $this->assertSame(1, RegionCacheKeys::version());
        $this->assertTrue(Cache::has('regions:tag_regions_v1:country:'.$this->country->id));
    }

    public function test_region_popup_content_is_not_present_in_the_initial_page_html(): void
    {
        $this->get('http://brest.furniture.test/')
            ->assertOk()
            ->assertSee('data-region-popup-content', false)
            ->assertSee('data-region-popup-url', false)
            ->assertDontSee('data-area-toggle', false);
    }

    public function test_capital_cities_are_loaded_by_country_and_capital_flag(): void
    {
        $anotherArea = Region::create([
            'name' => 'Гомельская область',
            'name_en' => 'Gomel region',
            'name_in' => 'Гомельской области',
            'subdomain' => 'gomel-region',
            'parent_id' => $this->country->id,
            'is_active' => true,
        ]);
        $notCapital = Region::create([
            'name' => 'Гомель',
            'name_en' => 'Gomel',
            'name_in' => 'Гомеле',
            'subdomain' => 'gomel',
            'parent_id' => $anotherArea->id,
            'is_capital' => false,
            'is_active' => true,
        ]);

        $otherCountry = Region::create([
            'name' => 'Польша',
            'name_en' => 'Poland',
            'name_in' => 'Польше',
            'subdomain' => 'poland',
            'lang' => null,
            'timezone' => 'Europe/Warsaw',
            'is_active' => true,
        ]);
        $otherArea = Region::create([
            'name' => 'Мазовецкое воеводство',
            'name_en' => 'Masovia',
            'name_in' => 'Мазовецком воеводстве',
            'subdomain' => 'masovia',
            'parent_id' => $otherCountry->id,
            'is_active' => true,
        ]);
        Region::create([
            'name' => 'Варшава',
            'name_en' => 'Warsaw',
            'name_in' => 'Варшаве',
            'subdomain' => 'warsaw',
            'parent_id' => $otherArea->id,
            'is_capital' => true,
            'is_active' => true,
        ]);

        $cities = app(RegionService::class)->getCapitalCities($this->country);

        $this->assertCount(1, $cities);
        $this->assertSame($this->city->id, $cities->first()->id);
        $this->assertNotContains($notCapital->id, $cities->pluck('id')->all());
        $this->assertTrue(Cache::has(RegionCacheKeys::countryCapitals($this->country)));
    }

    public function test_region_cache_version_is_incremented_when_regions_change(): void
    {
        $before = RegionCacheKeys::version();

        app(RegionService::class)->forgetCache();

        $this->assertSame($before + 1, RegionCacheKeys::version());
    }
}
