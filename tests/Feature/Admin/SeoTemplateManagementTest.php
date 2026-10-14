<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\SeoTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTemplateManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Language $ru;

    private Language $en;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => Roles::SEO]);
        $this->ru = Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);
        $this->en = Language::create(['code' => 'en', 'name' => 'English', 'name_en' => 'English']);
    }

    private function translation(Language $language, array $data = []): array
    {
        return [
            'lang' => $language->code,
            'name' => '',
            'seo_title' => '',
            'seo_description' => '',
            'seo_block' => '',
            ...$data,
        ];
    }

    private function categoryTranslation(Language $language, array $data = []): array
    {
        return [
            'lang' => $language->code,
            'name' => '',
            'slug' => '',
            'seo_template_id' => null,
            'seo_title' => '',
            'seo_description' => '',
            'seo_block' => '',
            ...$data,
        ];
    }

    private function createTemplate(string $name = 'Категория', array $translations = []): SeoTemplate
    {
        $template = SeoTemplate::create(['name' => $name]);

        $template->translations()->createMany($translations ?: [
            ['lang' => 'ru', 'name' => 'Шаблон', 'seo_title' => 'Купить {name}', 'seo_description' => '{name} недорого'],
        ]);

        return $template;
    }

    public function test_pages_are_displayed(): void
    {
        $template = $this->createTemplate();

        $this->actingAs($this->staff)->get('/admin/seo-templates')->assertOk();
        $this->actingAs($this->staff)->get('/admin/seo-templates/create')->assertOk();
        $this->actingAs($this->staff)->get("/admin/seo-templates/{$template->id}/edit")->assertOk();
    }

    public function test_customer_has_no_access(): void
    {
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);

        $this->actingAs($customer)->get('/admin/seo-templates')->assertForbidden();
    }

    public function test_template_can_be_created_with_translations(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/seo-templates', [
                'name' => 'Мебель',
                'translations' => [
                    $this->translation($this->ru, ['name' => 'Мебель', 'seo_title' => 'Купить {name}', 'seo_block' => 'Текст']),
                    $this->translation($this->en),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/seo-templates');

        $template = SeoTemplate::with('translations')->firstOrFail();

        $this->assertSame('Мебель', $template->name);
        $this->assertCount(1, $template->translations);
        $this->assertSame('Купить {name}', $template->translations->first()->seo_title);
    }

    public function test_template_name_must_be_unique(): void
    {
        $this->createTemplate('Мебель');

        $this->actingAs($this->staff)
            ->post('/admin/seo-templates', [
                'name' => 'Мебель',
                'translations' => [$this->translation($this->ru, ['name' => 'Мебель'])],
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_at_least_one_translation_is_required(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/seo-templates', [
                'name' => 'Мебель',
                'translations' => [$this->translation($this->ru), $this->translation($this->en, ['seo_title' => 'Buy'])],
            ])
            ->assertSessionHasErrors(['translations', 'translations.1.name']);
    }

    public function test_template_can_be_updated_and_translation_cleared(): void
    {
        $template = $this->createTemplate('Старое', [
            ['lang' => 'ru', 'name' => 'Шаблон'],
            ['lang' => 'en', 'name' => 'Template'],
        ]);

        $this->actingAs($this->staff)
            ->put("/admin/seo-templates/{$template->id}", [
                'name' => 'Новое',
                'translations' => [
                    $this->translation($this->ru, ['name' => 'Шаблон', 'seo_title' => 'Новый title']),
                    $this->translation($this->en),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/seo-templates');

        $template->refresh()->load('translations');

        $this->assertSame('Новое', $template->name);
        $this->assertSame(['ru'], $template->translations->pluck('lang')->all());
        $this->assertSame('Новый title', $template->translations->first()->seo_title);
    }

    public function test_clearing_template_language_unlinks_categories_in_that_language(): void
    {
        $template = $this->createTemplate('Мебель', [
            ['lang' => 'ru', 'name' => 'Шаблон'],
            ['lang' => 'en', 'name' => 'Template'],
        ]);

        $category = Category::create(['is_active' => true]);
        $category->translations()->createMany([
            ['lang' => 'ru', 'name' => 'Диваны', 'slug' => 'divany', 'seo_template_id' => $template->id],
            ['lang' => 'en', 'name' => 'Sofas', 'slug' => 'sofas', 'seo_template_id' => $template->id],
        ]);

        $this->actingAs($this->staff)
            ->put("/admin/seo-templates/{$template->id}", [
                'name' => 'Мебель',
                'translations' => [$this->translation($this->ru, ['name' => 'Шаблон']), $this->translation($this->en)],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($template->id, $category->translations()->where('lang', 'ru')->value('seo_template_id'));
        $this->assertNull($category->translations()->where('lang', 'en')->value('seo_template_id'));
    }

    public function test_category_translation_can_be_linked_to_template(): void
    {
        $template = $this->createTemplate();

        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'is_active' => true,
                'translations' => [
                    $this->categoryTranslation($this->ru, ['name' => 'Диваны', 'seo_template_id' => $template->id]),
                ],
            ])
            ->assertSessionHasNoErrors();

        $translation = CategoryTranslation::firstOrFail();
        $this->assertSame($template->id, $translation->seo_template_id);

        $this->actingAs($this->staff)
            ->put("/admin/categories/{$translation->category_id}", [
                'is_active' => true,
                'translations' => [$this->categoryTranslation($this->ru, ['name' => 'Диваны', 'seo_template_id' => ''])],
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($translation->refresh()->seo_template_id);
    }

    public function test_template_without_translation_in_language_is_rejected(): void
    {
        $template = $this->createTemplate(); // ru only

        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'is_active' => true,
                'translations' => [
                    $this->categoryTranslation($this->en, ['name' => 'Sofas', 'seo_template_id' => $template->id]),
                ],
            ])
            ->assertSessionHasErrors('translations.0.seo_template_id');
    }

    public function test_template_without_category_name_is_rejected(): void
    {
        $template = $this->createTemplate();

        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'is_active' => true,
                'translations' => [
                    $this->categoryTranslation($this->en, ['name' => 'Sofas']),
                    $this->categoryTranslation($this->ru, ['seo_template_id' => $template->id]),
                ],
            ])
            ->assertSessionHasErrors('translations.1.name');
    }

    public function test_own_seo_fields_win_and_empty_ones_come_from_template(): void
    {
        $template = $this->createTemplate();

        $category = Category::create(['is_active' => true]);
        $translation = $category->translations()->create([
            'lang' => 'ru',
            'name' => 'Диваны',
            'slug' => 'divany',
            'seo_template_id' => $template->id,
            'seo_description' => 'Своё описание',
        ]);

        $this->assertSame([
            'seo_title' => 'Купить Диваны',
            'seo_description' => 'Своё описание',
            'seo_block' => null,
        ], $translation->seo());
    }
}
