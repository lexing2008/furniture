<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCountryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Region $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => Roles::ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->country = Region::create([
            'name' => 'Беларусь',
            'name_en' => 'Belarus',
            'name_in' => 'Беларуси',
            'subdomain' => 'belarus',
            'timezone' => 'Europe/Minsk',
            'is_active' => true,
        ]);
    }

    public function test_countries_page_lists_only_countries(): void
    {
        Region::create([
            'name' => 'Минская область',
            'name_en' => 'Minsk region',
            'name_in' => 'Минской области',
            'subdomain' => 'minsk-region',
            'parent_id' => $this->country->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.countries.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('countries/IndexCountries', false)
                ->has('countries', 1)
                ->where('countries.0.id', $this->country->id)
                ->where('countries.0.name', 'Беларусь')
                ->where('countries.0.phone_code', null));
    }

    public function test_country_edit_page_is_available(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.countries.edit', $this->country))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('countries/EditCountry', false)
                ->where('country.id', $this->country->id)
                ->where('country.name', 'Беларусь')
                ->where('country.currency_decimals', 2));
    }

    public function test_country_data_can_be_saved(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.countries.update', $this->country), [
                'phone_code' => '+375',
                'currency' => 'byn',
                'currency_decimals' => 2,
                'tax_id_label' => 'УНП',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.countries.index'));

        $this->assertDatabaseHas('region_countries', [
            'region_id' => $this->country->id,
            'phone_code' => '375',
            'currency' => 'BYN',
            'currency_decimals' => 2,
            'tax_id_label' => 'УНП',
        ]);
        $this->assertSame('375', $this->country->fresh()->countryData->phone_code);
        $this->assertSame('УНП', $this->country->fresh()->countryData->tax_id_label);
    }

    public function test_country_fields_are_validated(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.countries.update', $this->country), [
                'phone_code' => 'abc',
                'currency' => 'BY',
                'currency_decimals' => 9,
            ])
            ->assertSessionHasErrors(['phone_code', 'currency', 'currency_decimals']);
    }

    public function test_an_area_is_not_a_country_page(): void
    {
        $area = Region::create([
            'name' => 'Минская область',
            'name_en' => 'Minsk region',
            'name_in' => 'Минской области',
            'subdomain' => 'minsk-region',
            'parent_id' => $this->country->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.countries.edit', $area))
            ->assertNotFound();
    }
}
