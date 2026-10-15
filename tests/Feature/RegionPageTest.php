<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionPageTest extends TestCase
{
    use RefreshDatabase;

    private Region $country;

    private Region $area;

    private Region $city;

    protected function setUp(): void
    {
        parent::setUp();

        Language::create(['code' => 'de', 'name' => 'Deutsch', 'name_en' => 'German']);

        $this->country = $this->region('Германия', 'germany', [
            'lang' => 'de',
            'seo_title' => 'Мебель в Германии',
            'seo_description' => 'Описание страны',
            'seo_block' => 'SEO-текст страны',
        ]);
        $this->area = $this->region('Бавария', 'bavaria', ['parent_id' => $this->country->id]);
        $this->city = $this->region('Мюнхен', 'munich', ['parent_id' => $this->area->id, 'seo_title' => 'Мебель в Мюнхене']);
    }

    private function region(string $name, string $subdomain, array $data = []): Region
    {
        return Region::create([
            'name' => $name,
            'name_en' => $name,
            'name_in' => "в {$name}",
            'subdomain' => $subdomain,
            ...$data,
        ]);
    }

    public function test_country_page_uses_its_own_seo_and_language(): void
    {
        $this->get('http://germany.furniture.test/')
            ->assertOk()
            ->assertSee('<html lang="de">', false)
            ->assertSee('<title>Мебель в Германии</title>', false)
            ->assertSee('<meta name="description" content="Описание страны">', false)
            ->assertSee('SEO-текст страны');
    }

    public function test_empty_seo_fields_are_taken_from_the_country(): void
    {
        $this->get('http://munich.furniture.test/')
            ->assertOk()
            ->assertSee('<html lang="de">', false)
            ->assertSee('<title>Мебель в Мюнхене</title>', false) // own value
            ->assertSee('<meta name="description" content="Описание страны">', false) // from the country
            ->assertSee('SEO-текст страны');
    }

    public function test_header_button_shows_the_region_and_popup_lists_areas_and_cities(): void
    {
        $this->region('Аугсбург', 'augsburg', ['parent_id' => $this->area->id]);
        $this->region('Гессен', 'hesse', ['parent_id' => $this->country->id]);

        $response = $this->get('http://munich.furniture.test/')->assertOk();

        $response->assertSeeInOrder(['id="region-button"', 'Мюнхен']);
        $response->assertSee('data-area-toggle', false);
        $response->assertSee('href="http://bavaria.furniture.test"', false);
        $response->assertSee('href="http://munich.furniture.test"', false);
        $response->assertSee('href="http://augsburg.furniture.test"', false);
        // An area without cities is a plain link
        $response->assertSee('href="http://hesse.furniture.test"', false);
    }

    public function test_inactive_regions_are_not_shown(): void
    {
        $this->region('Аугсбург', 'augsburg', ['parent_id' => $this->area->id, 'is_active' => false]);

        $this->get('http://augsburg.furniture.test/')->assertNotFound();

        $this->get('http://munich.furniture.test/')
            ->assertOk()
            ->assertDontSee('augsburg.furniture.test');
    }

    public function test_children_of_an_inactive_parent_are_not_available(): void
    {
        $this->area->update(['is_active' => false]);

        $this->get('http://munich.furniture.test/')->assertNotFound();
        $this->get('http://bavaria.furniture.test/')->assertNotFound();

        $this->get('http://germany.furniture.test/')
            ->assertOk()
            ->assertDontSee('bavaria.furniture.test');
    }

    public function test_unknown_subdomain_is_not_found(): void
    {
        $this->get('http://unknown.furniture.test/')->assertNotFound();
    }

    public function test_main_domain_is_not_a_region_page(): void
    {
        $this->get('http://furniture.test/')->assertOk()->assertDontSee('region-button', false);
    }
}
