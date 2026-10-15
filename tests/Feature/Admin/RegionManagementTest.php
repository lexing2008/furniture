<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\Language;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => Roles::MANAGER]);
        Language::create(['code' => 'de', 'name' => 'Deutsch', 'name_en' => 'German']);
    }

    private function payload(array $data = []): array
    {
        return [
            'parent_id' => null,
            'name' => 'Германия',
            'name_en' => 'Germany',
            'name_in' => 'в Германии',
            'subdomain' => 'germany',
            'lang' => 'de',
            'seo_title' => 'Мебель в Германии',
            'seo_description' => 'Описание',
            'seo_block' => 'Текст',
            'is_active' => true,
            ...$data,
        ];
    }

    private function createRegion(array $data = []): Region
    {
        $this->actingAs($this->staff)->post('/admin/regions', $this->payload($data))->assertSessionHasNoErrors();

        return Region::where('subdomain', $data['subdomain'] ?? 'germany')->firstOrFail();
    }

    private function city(): Region
    {
        $country = $this->createRegion();
        $area = $this->createRegion(['parent_id' => $country->id, 'name' => 'Берлин (земля)', 'subdomain' => 'berlin-land', 'lang' => 'de', 'seo_title' => '', 'seo_description' => '', 'seo_block' => '']);

        return $this->createRegion(['parent_id' => $area->id, 'name' => 'Берлин', 'subdomain' => 'berlin', 'seo_title' => '', 'seo_description' => '', 'seo_block' => '']);
    }

    public function test_pages_are_displayed(): void
    {
        $region = $this->createRegion();

        $this->actingAs($this->staff)->get('/admin/regions')->assertOk();
        $this->actingAs($this->staff)->get('/admin/regions/create')->assertOk();
        $this->actingAs($this->staff)->get("/admin/regions/{$region->id}/edit")->assertOk();
    }

    public function test_customer_has_no_access(): void
    {
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);

        $this->actingAs($customer)->get('/admin/regions')->assertForbidden();
    }

    public function test_country_can_be_created(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/regions', $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/regions');

        $region = Region::firstOrFail();

        $this->assertNull($region->parent_id);
        $this->assertSame('в Германии', $region->name_in);
        $this->assertSame('de', $region->lang);
        $this->assertTrue($region->is_active);
    }

    public function test_country_requires_language_and_seo(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/regions', $this->payload(['lang' => '', 'seo_title' => '', 'seo_description' => '', 'seo_block' => '']))
            ->assertSessionHasErrors(['lang', 'seo_title', 'seo_description', 'seo_block']);
    }

    public function test_area_and_city_may_have_empty_seo_and_ignore_language(): void
    {
        $city = $this->city();

        $this->assertNull($city->seo_title);
        $this->assertNull($city->lang);
        $this->assertSame('de', $city->language());
        $this->assertSame('Мебель в Германии', $city->seo()['seo_title']);
    }

    public function test_required_names_and_subdomain_rules(): void
    {
        $this->createRegion();

        $this->actingAs($this->staff)
            ->post('/admin/regions', $this->payload(['name' => '', 'name_en' => '', 'name_in' => '', 'subdomain' => 'germany']))
            ->assertSessionHasErrors(['name', 'name_en', 'name_in', 'subdomain']);

        foreach (['ber lin', '-berlin', 'berlin.de', 'www'] as $bad) {
            $this->actingAs($this->staff)
                ->post('/admin/regions', $this->payload(['subdomain' => $bad]))
                ->assertSessionHasErrors('subdomain');
        }
    }

    public function test_subdomain_is_lowercased(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/regions', $this->payload(['subdomain' => ' Germany ']))
            ->assertSessionHasNoErrors();

        $this->assertSame('germany', Region::firstOrFail()->subdomain);
    }

    public function test_region_can_be_updated_keeping_its_subdomain(): void
    {
        $region = $this->createRegion();

        $this->actingAs($this->staff)
            ->put("/admin/regions/{$region->id}", $this->payload(['name' => 'ФРГ']))
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/regions');

        $this->assertSame('ФРГ', $region->refresh()->name);
    }

    public function test_only_three_levels_are_allowed(): void
    {
        $city = $this->city();

        $this->actingAs($this->staff)
            ->post('/admin/regions', $this->payload(['parent_id' => $city->id, 'subdomain' => 'district', 'seo_title' => '', 'seo_description' => '', 'seo_block' => '']))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_region_cannot_be_moved_into_its_own_branch(): void
    {
        $city = $this->city();
        $country = Region::whereNull('parent_id')->firstOrFail();
        $area = $city->parent;

        $this->actingAs($this->staff)
            ->put("/admin/regions/{$country->id}", $this->payload(['parent_id' => $city->id]))
            ->assertSessionHasErrors('parent_id');

        // An area with cities cannot become a city itself
        $other = $this->createRegion(['name' => 'Австрия', 'subdomain' => 'austria']);

        $this->actingAs($this->staff)
            ->put("/admin/regions/{$area->id}", $this->payload(['parent_id' => $other->id, 'subdomain' => 'berlin-land', 'seo_title' => '', 'seo_description' => '', 'seo_block' => '']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->staff)
            ->put("/admin/regions/{$other->id}", $this->payload(['parent_id' => $area->id, 'name' => 'Австрия', 'subdomain' => 'austria']))
            ->assertSessionHasErrors('parent_id');
    }

    public function test_region_can_be_toggled(): void
    {
        $region = $this->createRegion();

        $this->actingAs($this->staff)->patch("/admin/regions/{$region->id}/toggle")->assertRedirect();
        $this->assertFalse($region->refresh()->is_active);

        $this->actingAs($this->staff)->patch("/admin/regions/{$region->id}/toggle")->assertRedirect();
        $this->assertTrue($region->refresh()->is_active);
    }
}
