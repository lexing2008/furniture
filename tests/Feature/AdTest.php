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
use App\Models\TmpFile;
use App\Models\User;
use App\Support\AdImagePath;
use App\Support\Slug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdTest extends TestCase
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
        Storage::fake('public');

        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);

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
        Region::create([
            'name' => 'Борисов',
            'name_en' => 'Borisov',
            'name_in' => 'Борисове',
            'subdomain' => 'borisov',
            'parent_id' => $area->id,
            'is_active' => true,
        ]);

        $this->category = Category::create(['is_active' => true]);
        $this->category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'menu_name' => 'Диваны',
            'slug' => 'divany',
        ]);

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
            'is_online_store' => false,
        ]);
    }

    public function test_ad_is_created_with_a_webp_image_and_filter_values(): void
    {
        [$color, $colorVariant] = $this->checkboxFilter('Цвет', 'Белый', 'white');
        $height = $this->intFilter('Высота', 'см');
        $width = $this->floatFilter('Ширина', 'см', 1);

        $upload = $this->actingAs($this->user)
            ->postJson($this->url('/ads/images'), [
                'image' => UploadedFile::fake()->image('photo.jpg', 1600, 900),
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'url']);

        $tmp = TmpFile::findOrFail($upload->json('id'));
        $this->assertSame(TmpFile::TYPE_AD_IMAGE, $tmp->type);
        $this->assertStringEndsWith('.webp', $tmp->path);
        Storage::disk('public')->assertExists($tmp->path);
        Storage::disk('public')->assertExists(AdImagePath::medium($tmp->path));
        Storage::disk('public')->assertExists(AdImagePath::listing($tmp->path));
        Storage::disk('public')->assertExists(AdImagePath::small($tmp->path));

        [$imageWidth, $imageHeight] = getimagesize(Storage::disk('public')->path($tmp->path));
        $this->assertLessThanOrEqual(1200, $imageWidth);
        $this->assertLessThanOrEqual(1200, $imageHeight);

        [$mediumWidth, $mediumHeight] = getimagesize(Storage::disk('public')->path(AdImagePath::medium($tmp->path)));
        $this->assertLessThanOrEqual(630, $mediumWidth);
        $this->assertLessThanOrEqual(467, $mediumHeight);

        [$listingWidth, $listingHeight] = getimagesize(Storage::disk('public')->path(AdImagePath::listing($tmp->path)));
        $this->assertLessThanOrEqual(325, $listingWidth);
        $this->assertLessThanOrEqual(237, $listingHeight);

        [$smallWidth, $smallHeight] = getimagesize(Storage::disk('public')->path(AdImagePath::small($tmp->path)));
        $this->assertLessThanOrEqual(100, $smallWidth);
        $this->assertLessThanOrEqual(100, $smallHeight);

        $response = $this->actingAs($this->user)->post($this->url('/ads'), [
            'title' => 'Диван Осло',
            'number' => 'SKU-1',
            'article' => 'ART-1',
            'category_id' => $this->category->id,
            'description' => 'Удобный диван',
            'price' => '1999.90',
            'warranty_value' => 12,
            'warranty_unit' => 'months',
            'video' => 'https://www.youtube.com/watch?v=test',
            'is_active' => false,
            'regions' => [$this->city->id],
            'filters' => [
                (string) $color->id => [$colorVariant->id],
                (string) $height->id => '90',
                (string) $width->id => '200.5',
            ],
            'existing_images' => [],
            'new_images' => [['temp_file_id' => $tmp->id, 'order' => 0]],
            'deleted_image_ids' => [],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $ad = Ad::query()->firstOrFail();
        $this->assertSame('SKU-1', $ad->number);
        $this->assertSame('ART-1', $ad->article);
        $this->assertFalse($ad->is_active);
        $this->assertFalse($ad->is_banned);
        $this->assertNotNull($ad->published_at);
        $this->assertSame('BYN', $ad->currency);
        $this->assertSame('1999.9000', $ad->price);
        $this->assertSame($this->city->id, $ad->regions()->first()?->id);
        $this->assertSame('Белый', $ad->filterVariants()->first()?->variant?->nameIn('ru'));
        $this->assertSame(90, $ad->filterInts()->first()?->value);
        $this->assertSame(200.5, $ad->filterFloats()->first()?->value);
        $this->assertDatabaseMissing('tmp_files', ['id' => $tmp->id]);

        $image = $ad->images()->firstOrFail();
        $this->assertNotNull($image->uuid);
        $this->assertSame(
            'furniture/manual/divany/divan-oslo.'.$image->uuid.'.webp',
            $image->path,
        );
        Storage::disk('public')->assertExists($image->path);
        $this->assertSame(
            'furniture/manual/divany/630/divan-oslo.'.$image->uuid.'.webp',
            AdImagePath::medium($image->path),
        );
        $this->assertSame(
            'furniture/manual/divany/325/divan-oslo.'.$image->uuid.'.webp',
            AdImagePath::listing($image->path),
        );
        $this->assertSame(
            'furniture/manual/divany/100/divan-oslo.'.$image->uuid.'.webp',
            AdImagePath::small($image->path),
        );
        Storage::disk('public')->assertExists(AdImagePath::medium($image->path));
        Storage::disk('public')->assertExists(AdImagePath::listing($image->path));
        Storage::disk('public')->assertExists(AdImagePath::small($image->path));

        $image->refresh();
        $this->assertSame($imageWidth, $image->width);
        $this->assertSame($imageHeight, $image->height);
        $this->assertSame($mediumWidth, $image->thumb_630_width);
        $this->assertSame($mediumHeight, $image->thumb_630_height);
        $this->assertSame($listingWidth, $image->thumb_325_width);
        $this->assertSame($listingHeight, $image->thumb_325_height);
        $this->assertSame($smallWidth, $image->thumb_100_width);
        $this->assertSame($smallHeight, $image->thumb_100_height);

        $this->actingAs($this->user)
            ->get($this->url("/ads/{$ad->id}/edit"))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ads/Edit', false)
                ->where('filters.0.value', [$colorVariant->id])
                ->where('filters.1.value', 90)
                ->where('filters.2.value', 200.5));

        $this->actingAs($this->user)
            ->get($this->url('/ads'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ads/Index', false)
                ->has('ads', 1)
                ->where('ads.0.article', 'ART-1')
                ->where('ads.0.images_count', 1)
                ->where('ads.0.filter_count', 3)
                ->where('ads.0.thumb_url', url('/storage/'.ltrim(AdImagePath::small($image->path), '/'))));

        $this->actingAs($this->user)
            ->get($this->url("/ads/create?copy={$ad->id}"))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ads/Create', false)
                ->where('source.category_id', $this->category->id)
                ->missing('source.price')
                ->where('source.warranty_value', 12)
                ->where('source.regions', [$this->city->id])
                ->where('filters.0.value', [$colorVariant->id])
                ->has('filters', 3));
    }

    public function test_ads_can_be_filtered_by_list_parameters(): void
    {
        $otherCategory = Category::create(['is_active' => true]);
        $otherCategory->translations()->create([
            'lang' => 'ru',
            'name' => 'Столы',
            'menu_name' => 'Столы',
            'slug' => 'stoly',
        ]);

        $base = [
            'company_id' => $this->company->id,
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
        ];

        $matching = Ad::create([
            ...$base,
            'category_id' => $this->category->id,
            'title' => 'Подходящий',
            'price' => 50,
            'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $matching->regions()->attach($this->city->id);
        $matching->images()->create(['path' => 'furniture/manual/filtered.webp', 'order' => 0]);
        $filter = $this->intFilter('Высота', 'см');
        $matching->filterInts()->create(['filter_id' => $filter->id, 'value' => 90]);

        $other = Ad::create([
            ...$base,
            'category_id' => $otherCategory->id,
            'title' => 'Другой',
            'price' => 500,
            'video' => null,
        ]);

        $queries = [
            'category_id='.$this->category->id => $matching,
            'area_id='.$this->city->parent_id => $matching,
            'city_id='.$this->city->id => $matching,
            'price_from=10&price_to=100' => $matching,
            'has_filters=with' => $matching,
            'has_filters=without' => $other,
            'photo=with' => $matching,
            'photo=without' => $other,
            'has_video=with' => $matching,
            'has_video=without' => $other,
        ];

        foreach ($queries as $query => $expected) {
            $this->actingAs($this->user)
                ->get($this->url('/ads?'.$query))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->has('ads', 1)
                    ->where('ads.0.id', $expected->id));
        }
    }

    public function test_ads_can_be_searched_by_title_article_and_number(): void
    {
        $base = [
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
        ];

        $sofa = Ad::create([
            ...$base,
            'title' => 'Диван Осло',
            'article' => 'ART-1',
            'number' => 'SKU-1',
        ]);
        $table = Ad::create([
            ...$base,
            'title' => 'Стол',
            'article' => 'ART-2',
            'number' => 'SKU-2',
        ]);

        foreach ([['Осло', $sofa], ['ART-2', $table], ['SKU-1', $sofa]] as [$term, $expected]) {
            $this->actingAs($this->user)
                ->get($this->url('/ads?search='.urlencode($term)))
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Ads/Index', false)
                    ->has('ads', 1)
                    ->where('ads.0.id', $expected->id)
                    ->where('search', $term));
        }
    }

    public function test_tags_are_removed_from_plain_text_ad_fields(): void
    {
        $this->actingAs($this->user)
            ->post($this->url('/ads'), [
                'title' => '<b>Диван</b>',
                'number' => '<i>SKU-10</i>',
                'article' => '<span>ART-10</span>',
                'category_id' => $this->category->id,
                'description' => '<p>Описание</p>',
                'price' => '100',
                'warranty_value' => null,
                'warranty_unit' => 'months',
                'video' => null,
                'regions' => [$this->city->id],
                'filters' => [],
                'existing_images' => [],
                'new_images' => [],
                'deleted_image_ids' => [],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $ad = Ad::query()->firstOrFail();

        $this->assertSame('Диван', $ad->title);
        $this->assertSame('SKU-10', $ad->number);
        $this->assertSame('ART-10', $ad->article);
        $this->assertSame('<p>Описание</p>', $ad->description);
    }

    public function test_ad_name_slug_is_transliterated_and_limited(): void
    {
        $this->assertSame('divan-oslo', Slug::makeLimited('Диван Осло', 150));
        $this->assertSame(150, strlen(Slug::makeLimited(str_repeat('a', 200), 150)));
    }

    public function test_description_length_ignores_html_tags(): void
    {
        $payload = [
            'title' => 'Описание',
            'category_id' => $this->category->id,
            'description' => '<p>'.str_repeat('a', 15000).'</p>',
            'price' => '1',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'video' => null,
            'regions' => [$this->city->id],
            'filters' => [],
            'existing_images' => [],
            'new_images' => [],
            'deleted_image_ids' => [],
        ];

        $this->actingAs($this->user)
            ->post($this->url('/ads'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('ads', 1);

        $this->actingAs($this->user)
            ->post($this->url('/ads'), [
                ...$payload,
                'title' => 'Слишком длинное описание',
                'description' => '<p>'.str_repeat('a', 15001).'</p>',
            ])
            ->assertSessionHasErrors('description');

        $this->assertDatabaseCount('ads', 1);
    }

    public function test_number_is_optional_and_new_ads_are_not_moderated(): void
    {
        $this->actingAs($this->user)->post($this->url('/ads'), [
            'title' => 'Без номера',
            'number' => '',
            'category_id' => $this->category->id,
            'description' => '',
            'price' => '10',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'video' => null,
            'regions' => [$this->city->id],
            'filters' => [],
            'existing_images' => [],
            'new_images' => [],
            'deleted_image_ids' => [],
        ])->assertRedirect()->assertSessionHas('success');

        $ad = Ad::query()->firstOrFail();
        $this->assertNull($ad->number);
        $this->assertNull($ad->article);
        $this->assertTrue($ad->is_active);
        $this->assertFalse($ad->is_banned);
        $this->assertFalse($ad->is_moderated);
    }

    public function test_published_at_is_updated_by_actions(): void
    {
        $ad = Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Диван',
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_moderated' => true,
            'published_at' => now()->subDay(),
        ]);

        $before = $ad->published_at;

        $this->travel(1)->minutes();

        $this->actingAs($this->user)
            ->put($this->url("/ads/{$ad->id}"), [
                'title' => 'Диван',
                'number' => null,
                'article' => null,
                'category_id' => $this->category->id,
                'description' => '',
                'price' => '100',
                'warranty_value' => null,
                'warranty_unit' => 'months',
                'video' => null,
                'regions' => [$this->city->id],
                'filters' => [],
                'existing_images' => [],
                'new_images' => [],
                'deleted_image_ids' => [],
            ])
            ->assertRedirect();

        $afterUpdate = $ad->fresh()?->published_at;
        $this->assertTrue($afterUpdate?->greaterThan($before) ?? false);

        $this->travel(1)->minutes();

        $this->actingAs($this->user)
            ->patchJson($this->url("/ads/{$ad->id}/price"), ['price' => '200'])
            ->assertOk();

        $afterPrice = $ad->fresh()?->published_at;
        $this->assertTrue($afterPrice?->greaterThan($afterUpdate) ?? false);

        $this->travel(1)->minutes();

        $this->actingAs($this->user)
            ->patchJson($this->url("/ads/{$ad->id}/actualize"))
            ->assertOk()
            ->assertJsonStructure(['published_at']);

        $this->assertTrue($ad->fresh()?->published_at?->greaterThan($afterPrice) ?? false);
    }

    public function test_published_at_uses_company_region_timezone_with_parent_fallback(): void
    {
        $this->travelTo(Carbon::parse('2026-10-27 12:00:00', 'UTC'));
        $this->city->update(['timezone' => 'Asia/Tokyo']);

        $ad = Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Товар',
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_moderated' => true,
            'published_at' => Carbon::parse('2026-10-26 00:00:00', 'UTC'),
        ]);

        $this->actingAs($this->user)
            ->patchJson($this->url("/ads/{$ad->id}/actualize"))
            ->assertOk()
            ->assertJsonPath('published_at', '27.10.2026 21:00');

        $this->assertSame(
            '2026-10-27 12:00:00',
            $ad->fresh()->published_at?->utc()->format('Y-m-d H:i:s'),
        );

        $this->city->update(['timezone' => null]);
        Region::findOrFail($this->city->parent_id)->update(['timezone' => 'Asia/Tokyo']);

        $this->actingAs($this->user)
            ->patchJson($this->url("/ads/{$ad->id}/actualize"))
            ->assertOk()
            ->assertJsonPath('published_at', '27.10.2026 21:00');

        $area = Region::findOrFail($this->city->parent_id);
        $area->update(['timezone' => null]);
        $this->country->update(['timezone' => 'Asia/Tokyo']);

        $this->actingAs($this->user)
            ->patchJson($this->url("/ads/{$ad->id}/actualize"))
            ->assertOk()
            ->assertJsonPath('published_at', '27.10.2026 21:00');
    }

    public function test_price_can_be_updated_from_the_list(): void
    {
        $ad = Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Диван',
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_moderated' => true,
        ]);

        $this->actingAs($this->user)
            ->patchJson($this->url("/ads/{$ad->id}/price"), ['price' => '123.45'])
            ->assertOk()
            ->assertJsonPath('price', 123.45);

        $this->assertSame('123.4500', $ad->fresh()?->price);
        $this->assertTrue($ad->fresh()?->is_moderated);

        $this->actingAs($this->user)
            ->patchJson($this->url("/ads/{$ad->id}/price"), ['price' => '-1'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('price');
    }

    public function test_moderation_is_reset_only_when_content_changes(): void
    {
        $ad = Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Диван',
            'number' => 'MOD-1',
            'description' => '<p>Описание</p>',
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_moderated' => true,
        ]);

        $payload = [
            'title' => 'Диван',
            'number' => 'MOD-1',
            'category_id' => $this->category->id,
            'description' => '<p>Описание</p>',
            'price' => '120',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'video' => null,
            'regions' => [$this->city->id],
            'filters' => [],
            'existing_images' => [],
            'new_images' => [],
            'deleted_image_ids' => [],
        ];

        $this->actingAs($this->user)
            ->put($this->url("/ads/{$ad->id}"), $payload)
            ->assertRedirect();
        $this->assertTrue($ad->fresh()?->is_moderated);

        $this->actingAs($this->user)
            ->put($this->url("/ads/{$ad->id}"), [...$payload, 'title' => 'Диван изменён'])
            ->assertRedirect();
        $this->assertFalse($ad->fresh()?->is_moderated);
    }

    public function test_ads_are_filtered_by_status_and_counts_are_shared(): void
    {
        $base = [
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
        ];

        Ad::create([...$base, 'title' => 'Активный', 'is_active' => true, 'is_moderated' => true, 'is_banned' => false]);
        Ad::create([...$base, 'title' => 'Отключённый', 'is_active' => false, 'is_moderated' => true, 'is_banned' => false]);
        Ad::create([...$base, 'title' => 'На модерации', 'is_active' => true, 'is_moderated' => false, 'is_banned' => false]);
        Ad::create([...$base, 'title' => 'Забаненный', 'is_active' => true, 'is_moderated' => true, 'is_banned' => true]);

        $this->actingAs($this->user)
            ->get($this->url('/ads?status=active'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ads/Index', false)
                ->where('status', 'active')
                ->has('ads', 2)
                ->where('ad_counts.all', 4)
                ->where('ad_counts.active', 2)
                ->where('ad_counts.disabled', 1)
                ->where('ad_counts.moderation', 1)
                ->where('ad_counts.banned', 1));

        $this->actingAs($this->user)
            ->get($this->url('/ads?status=banned'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('status', 'banned')
                ->has('ads', 1)
                ->where('ads.0.title', 'Забаненный'));
    }

    public function test_image_can_be_uploaded_from_a_company_subdomain(): void
    {
        $this->company->update(['website' => 'https://example.com']);

        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);
        $contents = file_get_contents($file->getRealPath());

        Http::fake([
            'https://img.example.com/photo.jpg' => Http::response($contents, 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson($this->url('/ads/images/url'), [
                'url' => 'https://img.example.com/photo.jpg',
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'url', 'thumb_630_url', 'thumb_100_url']);

        $tmp = TmpFile::findOrFail($response->json('id'));
        Storage::disk('public')->assertExists($tmp->path);
        Storage::disk('public')->assertExists(AdImagePath::medium($tmp->path));
        Storage::disk('public')->assertExists(AdImagePath::small($tmp->path));

        [$width, $height] = getimagesize(Storage::disk('public')->path($tmp->path));
        $this->assertLessThanOrEqual(1200, $width);
        $this->assertLessThanOrEqual(1200, $height);

        Http::assertSent(fn ($request) => $request->url() === 'https://img.example.com/photo.jpg');
    }

    public function test_image_url_can_be_uploaded_from_any_public_site(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);
        $contents = file_get_contents($file->getRealPath());

        Http::fake([
            'https://images.other-site.test/photo.jpg' => Http::response($contents, 200, ['Content-Type' => 'image/jpeg']),
            'https://images.other-site.test/not-an-image.jpg' => Http::response('not an image', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        $response = $this->actingAs($this->user)
            ->postJson($this->url('/ads/images/url'), [
                'url' => 'https://images.other-site.test/photo.jpg',
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'url', 'thumb_630_url', 'thumb_100_url']);

        $tmp = TmpFile::findOrFail($response->json('id'));
        Storage::disk('public')->assertExists($tmp->path);
        $this->assertNull($this->company->fresh()->website);

        $this->actingAs($this->user)
            ->postJson($this->url('/ads/images/url'), [
                'url' => 'https://images.other-site.test/not-an-image.jpg',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('url');

        Http::assertSent(fn ($request) => $request->url() === 'https://images.other-site.test/photo.jpg');
    }

    public function test_temporary_image_can_be_deleted_from_the_server(): void
    {
        $upload = $this->actingAs($this->user)
            ->postJson($this->url('/ads/images'), [
                'image' => UploadedFile::fake()->image('photo.jpg', 800, 600),
            ])
            ->assertCreated();

        $tmp = TmpFile::findOrFail($upload->json('id'));

        $this->deleteJson($this->url("/ads/images/{$tmp->id}"))
            ->assertNoContent();

        $this->assertDatabaseMissing('tmp_files', ['id' => $tmp->id]);
        Storage::disk('public')->assertMissing(AdImagePath::variants($tmp->path));
    }

    public function test_radio_filter_value_is_saved(): void
    {
        $filter = Filter::create([
            'category_id' => $this->category->id,
            'type' => 'radio',
            'order' => 0,
            'is_active' => true,
        ]);
        $filter->translations()->create(['lang' => 'ru', 'name' => 'Форма', 'is_active' => true]);
        $variant = $filter->variants()->create(['order' => 0]);
        $variant->translations()->create([
            'lang' => 'ru',
            'name_in_filter' => 'Угловая',
            'name_full' => 'Угловая',
            'name_buy' => 'угловая',
            'slug' => 'uglovaya',
            'is_active' => true,
        ]);

        $this->actingAs($this->user)->post($this->url('/ads'), [
            'title' => 'Угловой диван',
            'number' => 'SKU-RADIO',
            'category_id' => $this->category->id,
            'description' => '',
            'price' => '100',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'video' => null,
            'regions' => [$this->city->id],
            'filters' => [(string) $filter->id => $variant->id],
            'existing_images' => [],
            'new_images' => [],
            'deleted_image_ids' => [],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('adv_filter_variants', [
            'ad_id' => Ad::query()->value('id'),
            'filter_id' => $filter->id,
            'filter_variant_id' => $variant->id,
        ]);
    }

    public function test_update_reorders_deletes_and_adds_images(): void
    {
        $ad = Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Стол',
            'number' => 'SKU-2',
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => 1,
            'warranty_unit' => 'years',
        ]);

        Storage::disk('public')->put('furniture/manual/first.webp', 'first');
        Storage::disk('public')->put('furniture/manual/second.webp', 'second');

        $first = $ad->images()->create(['path' => 'furniture/manual/first.webp', 'order' => 0]);
        $second = $ad->images()->create(['path' => 'furniture/manual/second.webp', 'order' => 1]);

        $upload = $this->actingAs($this->user)
            ->postJson($this->url('/ads/images'), [
                'image' => UploadedFile::fake()->image('third.png', 600, 800),
            ])
            ->assertCreated();

        $tmp = TmpFile::findOrFail($upload->json('id'));

        $this->actingAs($this->user)
            ->put($this->url("/ads/{$ad->id}"), [
                'title' => 'Стол',
                'number' => 'SKU-2',
                'category_id' => $this->category->id,
                'description' => '',
                'price' => '100',
                'warranty_value' => 1,
                'warranty_unit' => 'years',
                'video' => null,
                'regions' => [$this->city->id],
                'filters' => [],
                'existing_images' => [['id' => $second->id, 'order' => 0]],
                'new_images' => [['temp_file_id' => $tmp->id, 'order' => 1]],
                'deleted_image_ids' => [$first->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('ad_images', ['id' => $first->id]);
        Storage::disk('public')->assertMissing('furniture/manual/first.webp');
        $this->assertSame(0, $second->fresh()?->order);
        $this->assertSame('furniture/manual/divany/stol.second.webp', $second->fresh()?->path);
        $this->assertSame(2, $ad->images()->count());
        $this->assertDatabaseMissing('tmp_files', ['id' => $tmp->id]);
    }

    public function test_temporary_files_older_than_ten_days_are_removed(): void
    {
        Storage::disk('public')->put('furniture/manual/old.webp', 'old');
        Storage::disk('public')->put('furniture/manual/new.webp', 'new');

        $old = TmpFile::create([
            'user_id' => $this->user->id,
            'type' => TmpFile::TYPE_AD_IMAGE,
            'path' => 'furniture/manual/old.webp',
        ]);
        $new = TmpFile::create([
            'user_id' => $this->user->id,
            'type' => TmpFile::TYPE_AD_IMAGE,
            'path' => 'furniture/manual/new.webp',
        ]);

        TmpFile::query()->whereKey($old->id)->update(['created_at' => now()->subDays(11)]);

        $this->artisan('tmp-files:clean')->assertSuccessful();

        $this->assertDatabaseMissing('tmp_files', ['id' => $old->id]);
        $this->assertDatabaseHas('tmp_files', ['id' => $new->id]);
        Storage::disk('public')->assertMissing('furniture/manual/old.webp');
        Storage::disk('public')->assertExists('furniture/manual/new.webp');
    }

    public function test_filters_endpoint_returns_filters_of_a_category(): void
    {
        $this->checkboxFilter('Цвет', 'Белый', 'white');
        $this->intFilter('Высота', 'см');

        $this->actingAs($this->user)
            ->getJson($this->url('/ads/filters?category_id='.$this->category->id))
            ->assertOk()
            ->assertJsonPath('filters.0.type', 'checkbox')
            ->assertJsonPath('filters.0.variants.0.name', 'Белый')
            ->assertJsonPath('filters.1.type', 'input_int');
    }

    private function url(string $path): string
    {
        return 'http://minsk.furniture.test/account'.$path;
    }

    public function test_ad_can_be_disabled_and_enabled_from_the_list(): void
    {
        $ad = Ad::create([
            'company_id' => $this->company->id,
            'category_id' => $this->category->id,
            'title' => 'Переключаемый товар',
            'price' => 100,
            'currency' => 'BYN',
            'warranty_value' => null,
            'warranty_unit' => 'months',
            'is_active' => true,
            'is_banned' => false,
            'published_at' => now(),
        ]);

        $this->actingAs($this->user)
            ->patchJson($this->url('/ads/'.$ad->id.'/active'))
            ->assertOk()
            ->assertJson(['is_active' => false]);

        $this->assertFalse($ad->fresh()->is_active);

        $this->actingAs($this->user)
            ->patchJson($this->url('/ads/'.$ad->id.'/active'))
            ->assertOk()
            ->assertJson(['is_active' => true]);

        $this->assertTrue($ad->fresh()->is_active);
    }

    /**
     * @return array{0: Filter, 1: FilterVariant}
     */
    private function checkboxFilter(string $name, string $variantName, string $slug): array
    {
        $filter = Filter::create([
            'category_id' => $this->category->id,
            'type' => 'checkbox',
            'order' => 0,
            'is_active' => true,
        ]);
        $filter->translations()->create(['lang' => 'ru', 'name' => $name, 'is_active' => true]);
        $variant = $filter->variants()->create(['order' => 0]);
        $variant->translations()->create([
            'lang' => 'ru',
            'name_in_filter' => $variantName,
            'name_full' => $variantName,
            'name_buy' => $variantName,
            'slug' => $slug,
            'is_active' => true,
        ]);

        return [$filter, $variant];
    }

    private function intFilter(string $name, string $unit): Filter
    {
        $filter = Filter::create([
            'category_id' => $this->category->id,
            'type' => 'input_int',
            'order' => 1,
            'is_active' => true,
        ]);
        $filter->translations()->create(['lang' => 'ru', 'name' => $name, 'unit' => $unit, 'is_active' => true]);

        return $filter;
    }

    private function floatFilter(string $name, string $unit, int $decimals): Filter
    {
        $filter = Filter::create([
            'category_id' => $this->category->id,
            'type' => 'input_float',
            'digits_after_point' => $decimals,
            'order' => 2,
            'is_active' => true,
        ]);
        $filter->translations()->create(['lang' => 'ru', 'name' => $name, 'unit' => $unit, 'is_active' => true]);

        return $filter;
    }
}
