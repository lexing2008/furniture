<?php

namespace Tests\Feature;

use App\Cache\RegionCacheKeys;
use App\Enums\Roles;
use App\Models\Language;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRegionCountryIdTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => Roles::ADMIN,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function countryData(string $subdomain = 'belarus', string $name = 'Беларусь'): array
    {
        return [
            'parent_id' => null,
            'name' => $name,
            'name_en' => $name,
            'name_in' => $name,
            'subdomain' => $subdomain,
            'lang' => 'ru',
            'timezone' => 'Europe/Minsk',
            'seo_title' => "Мебель в {$name}",
            'seo_description' => "Мебель в {$name}",
            'seo_block' => "<p>Мебель в {$name}</p>",
            'is_active' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function childData(Region $parent, string $subdomain, string $name): array
    {
        return [
            'parent_id' => $parent->id,
            'name' => $name,
            'name_en' => $name,
            'name_in' => $name,
            'subdomain' => $subdomain,
            'timezone' => '',
            'is_active' => true,
        ];
    }

    public function test_country_id_is_filled_for_country_area_and_city(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/regions', $this->countryData())
            ->assertRedirect(route('admin.regions.index'));

        $country = Region::query()->where('subdomain', 'belarus')->firstOrFail();
        $this->assertSame($country->id, $country->country_id);

        $this->actingAs($this->admin)
            ->post('/admin/regions', $this->childData($country, 'minsk-region', 'Минская область'))
            ->assertRedirect(route('admin.regions.index'));

        $area = Region::query()->where('subdomain', 'minsk-region')->firstOrFail();
        $this->assertSame($country->id, $area->country_id);

        $this->actingAs($this->admin)
            ->post('/admin/regions', $this->childData($area, 'minsk', 'Минск'))
            ->assertRedirect(route('admin.regions.index'));

        $city = Region::query()->where('subdomain', 'minsk')->firstOrFail();
        $this->assertSame($country->id, $city->country_id);
    }

    public function test_saving_a_region_invalidates_the_region_cache_version(): void
    {
        $before = RegionCacheKeys::version();

        $this->actingAs($this->admin)
            ->post('/admin/regions', $this->countryData())
            ->assertRedirect(route('admin.regions.index'));

        $this->assertSame($before + 1, RegionCacheKeys::version());
    }

    public function test_country_id_changes_when_a_region_is_moved_to_another_country(): void
    {
        $firstCountry = Region::create($this->countryData());
        $firstArea = Region::create($this->childData($firstCountry, 'first-area', 'Первая область'));
        $city = Region::create($this->childData($firstArea, 'city', 'Город'));

        $secondCountry = Region::create($this->countryData('poland', 'Польша'));
        $secondArea = Region::create($this->childData($secondCountry, 'second-area', 'Вторая область'));

        $this->actingAs($this->admin)
            ->put(route('admin.regions.update', $city), $this->childData($secondArea, 'city', 'Город'))
            ->assertRedirect(route('admin.regions.index'));

        $this->assertSame($secondCountry->id, $city->fresh()->country_id);
    }
}
