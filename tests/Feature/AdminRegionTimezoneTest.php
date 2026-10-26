<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Language;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRegionTimezoneTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

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
    private function countryData(array $overrides = []): array
    {
        return [
            'parent_id' => null,
            'name' => 'Беларусь',
            'name_en' => 'Belarus',
            'name_in' => 'Беларуси',
            'subdomain' => 'belarus',
            'lang' => 'ru',
            'timezone' => 'Europe/Minsk',
            'seo_title' => 'Мебель в Беларуси',
            'seo_description' => 'Мебель в Беларуси',
            'seo_block' => '<p>Мебель в Беларуси</p>',
            'is_active' => true,
            ...$overrides,
        ];
    }

    public function test_timezone_is_required_for_a_country(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/regions', $this->countryData(['timezone' => '']))
            ->assertSessionHasErrors('timezone');

        $this->assertDatabaseCount('regions', 0);
    }

    public function test_timezone_must_be_a_known_iana_timezone(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/regions', $this->countryData(['timezone' => 'Mars/Phobos']))
            ->assertSessionHasErrors('timezone');

        $this->assertDatabaseCount('regions', 0);
    }

    public function test_country_timezone_is_saved(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/regions', $this->countryData())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.regions.index'));

        $this->assertDatabaseHas('regions', [
            'subdomain' => 'belarus',
            'timezone' => 'Europe/Minsk',
        ]);
    }

    public function test_timezone_is_optional_for_an_area(): void
    {
        $country = Region::create($this->countryData());

        $this->actingAs($this->admin)
            ->post('/admin/regions', [
                'parent_id' => $country->id,
                'name' => 'Минская область',
                'name_en' => 'Minsk region',
                'name_in' => 'Минской области',
                'subdomain' => 'minsk-region',
                'timezone' => '',
                'is_active' => true,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.regions.index'));

        $this->assertDatabaseHas('regions', [
            'subdomain' => 'minsk-region',
            'timezone' => null,
        ]);
    }
}
