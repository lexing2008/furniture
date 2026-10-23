<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Category;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    private Region $city;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $country = Region::create([
            'name' => 'Беларусь',
            'name_en' => 'Belarus',
            'name_in' => 'Беларуси',
            'subdomain' => 'belarus',
            'is_active' => true,
        ]);
        $area = Region::create([
            'name' => 'Минская область',
            'name_en' => 'Minsk region',
            'name_in' => 'Минской области',
            'subdomain' => 'minsk-region',
            'parent_id' => $country->id,
            'is_active' => true,
        ]);
        $this->city = Region::create([
            'name' => 'Минск',
            'name_en' => 'Minsk',
            'name_in' => 'Минске',
            'subdomain' => 'minsk',
            'parent_id' => $area->id,
            'is_active' => true,
        ]);

        $this->category = Category::create(['is_active' => true]);

        $this->user = User::create([
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'password' => 'old-password',
            'role' => Roles::CUSTOMER,
            'email_verified_at' => now(),
        ]);

        $this->company = Company::create([
            'user_id' => $this->user->id,
            'region_id' => $this->city->id,
            'legal_name' => 'ООО Ромашка',
            'tax_id' => '100000001',
            'display_name' => 'Ромашка',
            'description' => '<p>Описание</p>',
            'is_online_store' => false,
            'website' => 'https://romashka.example.com',
            'email' => 'shop@example.com',
            'socials' => ['facebook' => 'https://facebook.com/romashka'],
        ]);
        $this->company->phones()->create(['code' => '+375', 'number' => '29 111-22-33', 'description' => 'Отдел продаж']);
        $this->company->categories()->attach($this->category);
    }

    private function url(string $path): string
    {
        return "http://minsk.furniture.test/account{$path}";
    }

    public function test_customer_login_does_a_full_page_load_into_the_account(): void
    {
        $response = $this->post($this->url('/login'), [
            'email' => 'ivan@example.com',
            'password' => 'old-password',
        ], ['X-Inertia' => 'true']);

        // A plain Inertia redirect would keep the guest Blade shell of the login page
        $response->assertStatus(409);
        $this->assertStringEndsWith('/account', $response->headers->get('X-Inertia-Location'));
    }

    public function test_registration_does_a_full_page_load_into_the_account(): void
    {
        $response = $this->post($this->url('/register'), [
            'legal_name' => 'ООО Новая компания',
            'tax_id' => '300000003',
            'display_name' => 'Новая компания',
            'region_id' => $this->city->id,
            'description' => '',
            'is_online_store' => false,
            'website' => '',
            'phones' => [
                ['code' => '+375', 'number' => '29 123-45-67', 'description' => ''],
            ],
            'categories' => [$this->category->id],
            'name' => 'Новый владелец',
            'email' => 'new-owner@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'terms' => true,
        ], ['X-Inertia' => 'true']);

        $response->assertStatus(409);
        $this->assertStringEndsWith('/account', $response->headers->get('X-Inertia-Location'));
        $this->assertAuthenticated();
        $this->assertSame('new-owner@example.com', auth()->user()->email);
        $response->assertSessionHas('success');
    }

    public function test_account_pages_are_available(): void
    {
        $this->actingAs($this->user);

        $this->get($this->url(''))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Edit', false)
                ->where('company.display_name', 'Ромашка')
                ->has('regions')
                ->has('categories'));

        $this->get($this->url('/contacts'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Contacts', false)
                ->where('company.email', 'shop@example.com')
                ->has('company.phones', 1));

        $this->get($this->url('/socials'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Socials', false)
                ->where('company.socials.facebook', 'https://facebook.com/romashka'));

        $this->get($this->url('/password'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Password/Edit', false));
    }

    public function test_about_page_does_not_touch_contacts_and_socials(): void
    {
        $this->actingAs($this->user)->put($this->url(''), [
            'legal_name' => 'ООО Новое имя',
            'tax_id' => '200000002',
            'display_name' => 'Новое имя',
            'region_id' => $this->city->id,
            'description' => '<p>Новое описание</p>',
            'is_online_store' => true,
            'website' => 'new.example.com',
            'categories' => [$this->category->id],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $company = $this->company->fresh();

        $this->assertSame('ООО Новое имя', $company->legal_name);
        $this->assertSame('https://new.example.com', $company->website);
        $this->assertSame('shop@example.com', $company->email);
        $this->assertSame(['facebook' => 'https://facebook.com/romashka'], $company->socials);
        $this->assertDatabaseHas('company_phones', [
            'company_id' => $company->id,
            'code' => '+375',
            'number' => '29 111-22-33',
        ]);
    }

    public function test_contacts_are_saved_with_normalization(): void
    {
        $this->actingAs($this->user)->put($this->url('/contacts'), [
            'email' => ' NEW@Example.COM ',
            'phones' => [
                ['code' => '375', 'number' => ' 29 222-33-44 ', 'description' => ''],
                ['code' => '', 'number' => '', 'description' => ''],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $company = $this->company->fresh();

        $this->assertSame('new@example.com', $company->email);
        $this->assertSame(1, $company->phones()->count());
        $this->assertDatabaseHas('company_phones', [
            'company_id' => $company->id,
            'code' => '+375',
            'number' => '29 222-33-44',
            'description' => null,
        ]);
    }

    public function test_socials_are_saved_and_empty_ones_are_removed(): void
    {
        $this->actingAs($this->user)->put($this->url('/socials'), [
            'socials' => [
                'facebook' => '',
                'youtube' => 'youtube.com/channel/romashka',
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(
            ['youtube' => 'https://youtube.com/channel/romashka'],
            $this->company->fresh()->socials,
        );
    }

    public function test_password_can_be_changed(): void
    {
        $this->actingAs($this->user)->put($this->url('/password'), [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $this->user->fresh()->password));
    }

    public function test_password_is_not_changed_with_a_wrong_current_password(): void
    {
        $this->actingAs($this->user)
            ->from($this->url('/password'))
            ->put($this->url('/password'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $this->user->fresh()->password));
    }
}
