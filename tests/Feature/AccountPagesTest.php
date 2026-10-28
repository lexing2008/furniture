<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Ad;
use App\Models\Category;
use App\Models\Company;
use App\Models\Filter;
use App\Models\FilterVariant;
use App\Models\Language;
use App\Models\Region;
use App\Models\RegionCountry;
use App\Models\SeoTemplate;
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

    private Region $country;

    private Region $city;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->country = Region::create([
            'name' => 'Беларусь',
            'name_en' => 'Belarus',
            'name_in' => 'Беларуси',
            'subdomain' => 'belarus',
            'timezone' => 'Europe/Minsk',
            'is_active' => true,
        ]);
        RegionCountry::create([
            'region_id' => $this->country->id,
            'phone_code' => '375',
            'currency' => 'BYN',
            'currency_decimals' => 2,
        ]);

        $area = Region::create([
            'name' => 'Минская область',
            'name_en' => 'Minsk region',
            'name_in' => 'Минской области',
            'subdomain' => 'minsk-region',
            'parent_id' => $this->country->id,
            'is_active' => true,
        ]);
        $this->city = Region::create([
            'name' => 'Минск',
            'name_en' => 'Minsk',
            'name_in' => 'Минске',
            'subdomain' => 'minsk',
            'parent_id' => $area->id,
            'timezone' => 'Europe/Minsk',
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
            'country_id' => $this->country->id,
            'legal_name' => 'ООО Ромашка',
            'tax_id' => '100000001',
            'display_name' => 'Ромашка',
            'description' => '<p>Описание</p>',
            'delivery' => '<p>Доставка по Минску</p>',
            'payment' => '<p>Наличные и карта</p>',
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

    public function test_company_forms_use_the_country_tax_id_label(): void
    {
        $this->country->countryData()->update(['tax_id_label' => 'УНП']);

        $this->get('http://minsk.furniture.test/account/register')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('taxIdLabel', 'УНП'));

        $this->actingAs($this->user)
            ->get($this->url('/general'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('taxIdLabel', 'УНП'));
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
            'legal_address' => 'г. Минск, ул. Новая, 1',
            'actual_address' => 'г. Минск, ул. Фактическая, 2',
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
        $this->assertSame($this->country->id, auth()->user()->company->country_id);
        $this->assertSame('г. Минск, ул. Новая, 1', auth()->user()->company->legal_address);
        $this->assertSame('г. Минск, ул. Фактическая, 2', auth()->user()->company->actual_address);
        $response->assertSessionHas('success');
    }

    public function test_language_comes_from_the_country_of_the_region(): void
    {
        // No country language: the default locale is Russian
        $this->get('http://minsk.furniture.test/')
            ->assertOk()
            ->assertSee('Мебель в Минске')
            ->assertSee('Информация');

        Language::create(['code' => 'en', 'name' => 'English', 'name_en' => 'English']);
        Language::create(['code' => 'de', 'name' => 'Deutsch', 'name_en' => 'German']);
        $this->country->update(['lang' => 'en']);

        $this->get('http://minsk.furniture.test/')
            ->assertOk()
            ->assertSee('Furniture in Minsk')
            ->assertSee('Information');

        // The country language is not supported: its fallback language wins
        $this->country->update(['lang' => 'de', 'fallback_lang' => 'en']);
        $this->get('http://minsk.furniture.test/')
            ->assertOk()
            ->assertSee('Furniture in Minsk');

        $this->post($this->url('/login'), [
            'email' => 'ivan@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
        $this->assertSame(
            'These credentials do not match our records.',
            session('errors')->first('email'),
        );

        $this->actingAs($this->user)
            ->get($this->url(''))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->where('locale', 'en'));
    }

    public function test_region_timezone_is_stored(): void
    {
        $this->assertSame('Europe/Minsk', $this->city->fresh()->timezone);

        $this->city->update(['timezone' => 'Asia/Bangkok']);

        $this->assertSame('Asia/Bangkok', $this->city->fresh()->timezone);
    }

    public function test_account_pages_are_available(): void
    {
        $this->actingAs($this->user);

        $this->get($this->url(''))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->where('locale', 'ru')
                ->where('company_region.name', 'Минск')
                ->where('company_region.url', 'http://minsk.furniture.test'));

        $this->get($this->url('/general'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Edit', false)
                ->where('company.display_name', 'Ромашка')
                ->where('regions.0.name', 'Беларусь')
                ->where('regions.0.selectable', false)
                ->where('regions.0.children.0.name', 'Минская область')
                ->where('regions.0.children.0.children.0.name', 'Минск')
                ->where('regions.0.children.0.children.0.selectable', true)
                ->has('categories'));

        $this->get($this->url('/delivery'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Delivery', false)
                ->where('company.delivery', '<p>Доставка по Минску</p>'));

        $this->get($this->url('/payment'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Payment', false)
                ->where('company.payment', '<p>Наличные и карта</p>'));

        $this->get($this->url('/contacts'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Contacts', false)
                ->where('company.email', 'shop@example.com')
                ->has('company.phones', 1)
                ->where('phoneCode', '375'));

        $this->get($this->url('/socials'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Socials', false)
                ->where('company.socials.facebook', 'https://facebook.com/romashka'));

        $this->get($this->url('/password'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Password/Edit', false));
    }

    public function test_dashboard_shows_ad_counts_and_profile_tasks(): void
    {
        $this->actingAs($this->user);

        $base = [
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Товар',
            'price' => 10,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
        ];

        Ad::create($base + ['is_active' => true, 'is_moderated' => true, 'is_banned' => false]);
        Ad::create($base + ['is_active' => false, 'is_moderated' => true, 'is_banned' => false]);
        Ad::create($base + ['is_active' => true, 'is_moderated' => false, 'is_banned' => false]);
        Ad::create($base + ['is_active' => true, 'is_moderated' => true, 'is_banned' => true]);

        $this->get($this->url(''))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->where('company.name', 'Ромашка')
                ->where('counts.all', 4)
                ->where('counts.active', 2)
                ->where('counts.disabled', 1)
                ->where('counts.moderation', 1)
                ->where('counts.banned', 1)
                ->has('tasks', 7)
                ->where('completed_tasks', 5)
                ->where('progress', 71)
                ->has('recent_ads', 4));
    }

    public function test_header_and_footer_show_categories_with_menu_name(): void
    {
        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

        $this->category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'menu_name' => 'Диваны в меню',
            'slug' => 'divany',
        ]);
        $withoutMenu = Category::create(['is_active' => true]);
        $withoutMenu->translations()->create([
            'lang' => 'ru',
            'name' => 'Столы',
            'slug' => 'stoly',
        ]);

        $vitebskArea = Region::create([
            'name' => 'Витебская область',
            'name_en' => 'Vitebsk region',
            'name_in' => 'Витебской области',
            'subdomain' => 'vitebsk-region',
            'parent_id' => $this->country->id,
            'is_active' => true,
        ]);
        Region::create([
            'name' => 'Витебск',
            'name_en' => 'Vitebsk',
            'name_in' => 'Витебске',
            'subdomain' => 'vitebsk',
            'parent_id' => $vitebskArea->id,
            'is_capital' => true,
            'is_active' => true,
        ]);
        $this->city->update(['is_capital' => true]);

        $this->get('http://minsk.furniture.test/')
            ->assertOk()
            ->assertSee('Диваны в меню')
            ->assertSee('Диваны в Минске')
            ->assertSee('Мебель в Витебске')
            ->assertSee('vitebsk.furniture.test')
            ->assertDontSee('Столы');

        $this->get('http://minsk.furniture.test/divany')
            ->assertOk()
            ->assertSee('Диваны в меню')
            ->assertSee('Диваны в Минске');

        $this->category->translations()->firstOrFail()->update(['menu_name' => 'Диваны обновлённые']);

        $this->get('http://minsk.furniture.test/')
            ->assertOk()
            ->assertSee('Диваны обновлённые')
            ->assertDontSee('Диваны в меню');
    }

    public function test_category_page_shows_active_ads_with_company_details(): void
    {
        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

        $this->category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'menu_name' => 'Диваны в меню',
            'slug' => 'divany',
        ]);

        Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Товар в категории',
            'description' => '<p>Описание товара для категории</p>',
            'price' => 123.45,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_active' => true,
            'is_moderated' => false,
            'is_banned' => false,
            'published_at' => now(),
        ]);

        Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Дешёвый товар',
            'description' => '<p>Короткое описание</p>',
            'price' => 50,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_active' => true,
            'is_moderated' => false,
            'is_banned' => false,
            'published_at' => now()->subDay(),
        ]);

        $this->get('http://minsk.furniture.test/divany')
            ->assertOk()
            ->assertSee('Товар в категории')
            ->assertSee('Описание товара для категории')
            ->assertSee('Подробнее')
            ->assertSee('Скрыть')
            ->assertSee('Ромашка')
            ->assertSee('29 111-22-33')
            ->assertSee('123,45')
            ->assertSee('text-green-third', false);

        $this->get('http://minsk.furniture.test/divany?sort=price_asc')
            ->assertOk()
            ->assertSeeInOrder(['Дешёвый товар', 'Товар в категории']);

        $this->get('http://minsk.furniture.test/divany?price_from=100')
            ->assertOk()
            ->assertSee('Товар в категории')
            ->assertDontSee('Дешёвый товар');
    }

    public function test_category_seo_uses_template_placeholders(): void
    {
        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

        $template = SeoTemplate::create(['name' => 'Категория — диваны']);
        $template->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'seo_title' => 'Купить {name} в {region_in}',
            'seo_description' => 'Описание {name_buy} в {region_in}',
            'seo_block' => '<p>Статья о {name_buy} в {region}.</p>',
        ]);

        $this->category->update(['seo_template_id' => $template->id]);
        $this->category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'name_buy' => 'Купить диваны',
            'menu_name' => 'Диваны в меню',
            'slug' => 'divany',
        ]);

        $this->get('http://minsk.furniture.test/divany')
            ->assertOk()
            ->assertSee('Купить диваны в Минске')
            ->assertSee('Описание купить диваны в Минске')
            ->assertSee('Статья о купить диваны в Минск.');
    }

    public function test_variant_seo_uses_its_template_placeholders(): void
    {
        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

        $this->category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'name_buy' => 'Купить диваны',
            'menu_name' => 'Диваны в меню',
            'slug' => 'divany',
        ]);
        $filter = Filter::create([
            'category_id' => $this->category->id,
            'type' => 'checkbox',
            'order' => 0,
            'is_active' => true,
        ]);
        $variant = FilterVariant::create(['filter_id' => $filter->id, 'order' => 0]);
        $variantTranslation = $variant->translations()->create([
            'lang' => 'ru',
            'name_in_filter' => 'Угловой',
            'name_full' => 'Угловой диван',
            'name_buy' => 'Купить угловой диван',
            'slug' => 'uglovoy',
            'is_active' => true,
        ]);

        $template = SeoTemplate::create(['name' => 'Угловые диваны']);
        $template->translations()->create([
            'lang' => 'ru',
            'name' => 'Угловые диваны',
            'seo_title' => '{name} в {region_in}',
            'seo_description' => '{name_buy} в {region_in}',
            'seo_block' => '<p>{name_buy} — статья для {region_in}.</p>',
        ]);
        $variantTranslation->update(['seo_template_id' => $template->id]);

        $secondVariant = FilterVariant::create(['filter_id' => $filter->id, 'order' => 1]);
        $secondVariant->translations()->create([
            'lang' => 'ru',
            'name_in_filter' => 'Диван-шкаф',
            'name_full' => 'Диван-шкаф',
            'name_buy' => 'Купить диван-шкаф',
            'slug' => 'divan-shkaf',
            'is_active' => true,
        ]);

        $this->get('http://minsk.furniture.test/divany/uglovoy/')
            ->assertOk()
            ->assertSee('угловой диван в Минске')
            ->assertSee('купить угловой диван в Минске')
            ->assertSee('купить угловой диван — статья для Минске.');

        $this->get('http://minsk.furniture.test/divany/uglovoy;divan-shkaf/')
            ->assertOk()
            ->assertSee('Угловой диван, Диван-шкаф в Минске');
    }

    public function test_category_filter_variant_slug_filters_ads(): void
    {
        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

        $this->category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'menu_name' => 'Диваны в меню',
            'slug' => 'divany',
        ]);

        $filter = Filter::create([
            'category_id' => $this->category->id,
            'type' => 'checkbox',
            'order' => 0,
            'is_active' => true,
        ]);
        $variant = FilterVariant::create(['filter_id' => $filter->id, 'order' => 0]);
        $variant->translations()->create([
            'lang' => 'ru',
            'name_in_filter' => 'Угловой',
            'name_full' => 'Угловой диван',
            'name_buy' => 'Купить угловой диван',
            'slug' => 'uglovoy',
            'is_active' => true,
        ]);

        $matching = Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Подходящий диван',
            'description' => '<p>Описание</p>',
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_active' => true,
            'is_banned' => false,
            'published_at' => now(),
        ]);
        $matching->filterVariants()->create([
            'filter_id' => $filter->id,
            'filter_variant_id' => $variant->id,
        ]);

        Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Другой диван',
            'description' => '<p>Описание</p>',
            'price' => 200,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_active' => true,
            'is_banned' => false,
            'published_at' => now(),
        ]);

        $this->get('http://minsk.furniture.test/divany/uglovoy/')
            ->assertOk()
            ->assertSee('Подходящий диван')
            ->assertDontSee('Другой диван');
    }

    public function test_category_children_are_available_as_json(): void
    {
        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

        $this->category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'menu_name' => 'Диваны в меню',
            'slug' => 'divany',
        ]);
        $child = Category::create(['parent_id' => $this->category->id, 'is_active' => true]);
        $child->translations()->create([
            'lang' => 'ru',
            'name' => 'Угловые диваны',
            'slug' => 'uglovye-divany',
        ]);

        $this->get('http://minsk.furniture.test/categories/'.$this->category->id.'/children')
            ->assertOk()
            ->assertJsonCount(1, 'categories')
            ->assertJsonPath('categories.0.name', 'Угловые диваны')
            ->assertJsonPath('categories.0.has_children', false);
    }

    public function test_account_language_is_saved_and_used(): void
    {
        $this->actingAs($this->user)->patch($this->url('/language'), ['lang' => 'en'])
            ->assertRedirect();

        $this->assertSame('en', $this->user->fresh()->lang);

        $this->get($this->url(''))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->where('locale', 'en'));

        $this->actingAs($this->user)->patch($this->url('/language'), ['lang' => 'de'])
            ->assertSessionHasErrors('lang');

        $this->assertSame('en', $this->user->fresh()->lang);
    }

    public function test_registration_page_uses_the_country_phone_code(): void
    {
        $this->get($this->url('/register'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Register', false)
                ->where('phoneCode', '375'));
    }

    public function test_delivery_and_payment_are_saved_independently(): void
    {
        $this->actingAs($this->user)
            ->put($this->url('/delivery'), ['delivery' => '<p>Новая доставка</p>'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('<p>Новая доставка</p>', $this->company->fresh()->delivery);
        $this->assertSame('<p>Наличные и карта</p>', $this->company->fresh()->payment);

        $this->actingAs($this->user)
            ->put($this->url('/payment'), ['payment' => '<p>Оплата переводом</p>'])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('<p>Оплата переводом</p>', $this->company->fresh()->payment);
        $this->assertSame('<p>Новая доставка</p>', $this->company->fresh()->delivery);
    }

    public function test_about_page_does_not_touch_contacts_and_socials(): void
    {
        $this->actingAs($this->user)->put($this->url('/general'), [
            'legal_name' => 'ООО Новое имя',
            'tax_id' => '200000002',
            'legal_address' => 'г. Минск, ул. Юридическая, 3',
            'actual_address' => 'г. Минск, ул. Фактическая, 4',
            'display_name' => 'Новое имя',
            'region_id' => $this->city->id,
            'description' => '<p>Новое описание</p>',
            'is_online_store' => true,
            'website' => 'new.example.com',
            'categories' => [$this->category->id],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $company = $this->company->fresh();

        $this->assertSame('ООО Новое имя', $company->legal_name);
        $this->assertSame('г. Минск, ул. Юридическая, 3', $company->legal_address);
        $this->assertSame('г. Минск, ул. Фактическая, 4', $company->actual_address);
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

    public function test_tags_are_removed_from_plain_text_company_fields(): void
    {
        $this->actingAs($this->user)->put($this->url('/general'), [
            'legal_name' => '<b>ООО Новое</b>',
            'tax_id' => '<i>200000002</i>',
            'legal_address' => '<span>г. Минск, ул. Юридическая, 5</span>',
            'actual_address' => '<b>г. Минск, ул. Фактическая, 6</b>',
            'display_name' => '<span>Новое имя</span>',
            'region_id' => $this->city->id,
            'description' => '<p>Новое описание</p>',
            'is_online_store' => true,
            'website' => '<b>new.example.com</b>',
            'categories' => [$this->category->id],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $company = $this->company->fresh();

        $this->assertSame('ООО Новое', $company->legal_name);
        $this->assertSame('200000002', $company->tax_id);
        $this->assertSame('г. Минск, ул. Юридическая, 5', $company->legal_address);
        $this->assertSame('г. Минск, ул. Фактическая, 6', $company->actual_address);
        $this->assertSame('Новое имя', $company->display_name);
        $this->assertSame('https://new.example.com', $company->website);
        $this->assertSame('<p>Новое описание</p>', $company->description);

        $this->actingAs($this->user)->put($this->url('/contacts'), [
            'email' => '<i>new@example.com</i>',
            'phones' => [
                ['code' => '<b>375</b>', 'number' => '<i>29 222-33-44</i>', 'description' => '<span>Отдел продаж</span>'],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('new@example.com', $this->company->fresh()->email);
        $this->assertDatabaseHas('company_phones', [
            'company_id' => $this->company->id,
            'code' => '+375',
            'number' => '29 222-33-44',
            'description' => 'Отдел продаж',
        ]);

        $this->actingAs($this->user)->put($this->url('/socials'), [
            'socials' => [
                'facebook' => '<b>https://facebook.com/new</b>',
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(
            ['facebook' => 'https://facebook.com/new'],
            $this->company->fresh()->socials,
        );
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

    public function test_socials_only_accept_their_network_domains(): void
    {
        $this->actingAs($this->user)->put($this->url('/socials'), [
            'socials' => [
                'facebook' => 'https://m.facebook.com/romashka',
                'youtube' => 'https://vimeo.com/romashka',
                'vk' => 'https://vk.com.evil.example/romashka',
                'x' => 'https://twitter.com/romashka',
            ],
        ])->assertSessionHasErrors(['socials.youtube', 'socials.vk']);

        $this->assertSame(
            ['facebook' => 'https://facebook.com/romashka'],
            $this->company->fresh()->socials,
        );

        $this->actingAs($this->user)->put($this->url('/socials'), [
            'socials' => [
                'facebook' => 'https://m.facebook.com/romashka',
                'telegram' => 'https://t.me/romashka',
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(
            [
                'facebook' => 'https://m.facebook.com/romashka',
                'telegram' => 'https://t.me/romashka',
            ],
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
