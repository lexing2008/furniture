<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterVariantTranslation;
use App\Models\Language;
use App\Models\SeoTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilterManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => Roles::MANAGER]);
        Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);
        Language::create(['code' => 'en', 'name' => 'English', 'name_en' => 'English']);

        $this->category = Category::create(['is_active' => true]);
        $this->category->translations()->create(['lang' => 'ru', 'name' => 'Диваны', 'slug' => 'divany']);
    }

    private function variant(array $data = []): array
    {
        return [
            'id' => null,
            'name_in_filter' => 'Угловой',
            'name_full' => 'Угловой диван',
            'name_buy' => 'Купить угловой диван',
            'slug' => '',
            'seo_template_id' => null,
            'translation_seo_template_id' => null,
            'is_active' => true,
            ...$data,
        ];
    }

    private function payload(array $data = []): array
    {
        return [
            'type' => 'checkbox',
            'digits_after_point' => null,
            'is_active' => true,
            'name' => 'Тип',
            'unit' => '',
            'variants' => [$this->variant()],
            ...$data,
        ];
    }

    private function createFilter(array $data = [], ?Category $category = null): Filter
    {
        $category ??= $this->category;

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$category->id}/filters", $this->payload($data))
            ->assertSessionHasNoErrors();

        return Filter::latest('id')->firstOrFail();
    }

    private function templateWith(string ...$langs): SeoTemplate
    {
        $template = SeoTemplate::create(['name' => 'Шаблон '.implode('', $langs)]);

        foreach ($langs as $lang) {
            $template->translations()->create(['lang' => $lang, 'name' => 'Шаблон', 'seo_title' => 'Купить {name}']);
        }

        return $template;
    }

    public function test_pages_are_displayed(): void
    {
        $filter = $this->createFilter();

        $this->actingAs($this->staff)->get("/admin/categories/{$this->category->id}/filters")->assertOk();
        $this->actingAs($this->staff)->get("/admin/categories/{$this->category->id}/filters/create")->assertOk();
        $this->actingAs($this->staff)->get("/admin/filters/{$filter->id}/edit")->assertOk();
        $this->actingAs($this->staff)->get("/admin/filters/{$filter->id}/translations/en")->assertOk();
    }

    public function test_customer_has_no_access(): void
    {
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);

        $this->actingAs($customer)->get("/admin/categories/{$this->category->id}/filters")->assertForbidden();
    }

    public function test_checkbox_filter_is_created_with_russian_texts_and_variants(): void
    {
        $filter = $this->createFilter(['unit' => 'шт']);

        $this->assertSame($this->category->id, $filter->category_id);
        $this->assertTrue($filter->is_active);

        $translation = $filter->translations()->firstOrFail();
        $this->assertSame('ru', $translation->lang);
        $this->assertSame('Тип', $translation->name);
        $this->assertSame('шт', $translation->unit);

        $variant = $filter->variants()->with('translations')->firstOrFail();
        $ru = $variant->translations->firstOrFail();
        $this->assertSame('ru', $ru->lang);
        $this->assertSame('Угловой диван', $ru->name_full);
        $this->assertNotSame('', $ru->slug); // generated from the Cyrillic name
    }

    public function test_numeric_filters_need_digits_only_when_float_and_have_no_variants(): void
    {
        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['type' => 'input_float', 'variants' => []]))
            ->assertSessionHasErrors('digits_after_point');

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['type' => 'input_float', 'digits_after_point' => 2, 'variants' => [], 'unit' => 'м']))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['type' => 'input_int', 'digits_after_point' => 3, 'variants' => []]))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Filter::where('type', 'input_float')->firstOrFail()->digits_after_point);
        $this->assertNull(Filter::where('type', 'input_int')->firstOrFail()->digits_after_point);

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['type' => 'input_int']))
            ->assertSessionHasErrors('variants');
    }

    public function test_type_and_names_are_validated(): void
    {
        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['type' => 'select', 'name' => '']))
            ->assertSessionHasErrors(['type', 'name']);

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['variants' => [$this->variant(['name_in_filter' => '', 'name_full' => '', 'name_buy' => ''])]]))
            ->assertSessionHasErrors(['variants.0.name_in_filter', 'variants.0.name_full', 'variants.0.name_buy']);
    }

    public function test_slug_is_unique_within_category_and_language(): void
    {
        $this->createFilter();

        // The same variant in another filter of the same category
        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['name' => 'Форма']))
            ->assertSessionHasErrors('variants.0.slug');

        // The same slug twice in one request
        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload([
                'name' => 'Форма',
                'variants' => [$this->variant(['slug' => 'a']), $this->variant(['slug' => 'a'])],
            ]))
            ->assertSessionHasErrors('variants.1.slug');

        // Another category is free to use it
        $other = Category::create(['is_active' => true]);
        $this->createFilter([], $other);
    }

    public function test_filter_is_updated_variants_synced_and_reordered(): void
    {
        $filter = $this->createFilter(['variants' => [
            $this->variant(),
            $this->variant(['name_in_filter' => 'Прямой', 'name_full' => 'Прямой диван', 'name_buy' => 'Купить прямой диван']),
        ]]);

        [$corner, $straight] = $filter->variants->all();

        $this->actingAs($this->staff)
            ->put("/admin/filters/{$filter->id}", $this->payload([
                'name' => 'Конфигурация',
                'variants' => [
                    // reordered, the corner one is renamed, a new one is added, the straight one is dropped
                    $this->variant(['name_in_filter' => 'Модульный', 'name_full' => 'Модульный диван', 'name_buy' => 'Купить модульный диван']),
                    $this->variant(['id' => $corner->id, 'name_in_filter' => 'Угловые']),
                ],
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect("/admin/categories/{$this->category->id}/filters");

        $filter->refresh();
        $this->assertSame('Конфигурация', $filter->baseTranslation->name);

        $names = $filter->variants()->with('translations')->get()->map(fn ($v) => $v->translations->first()->name_in_filter)->all();
        $this->assertSame(['Модульный', 'Угловые'], $names);

        $this->assertSoftDeleted('filter_variants', ['id' => $straight->id]);
        $this->assertSame($corner->id, $filter->variants()->get()[1]->id);
    }

    public function test_variant_of_another_filter_cannot_be_updated(): void
    {
        $first = $this->createFilter();
        $foreign = $this->createFilter(['name' => 'Форма', 'variants' => [$this->variant(['slug' => 'other'])]]);

        $this->actingAs($this->staff)
            ->put("/admin/filters/{$first->id}", $this->payload(['variants' => [$this->variant(['id' => $foreign->variants->first()->id])]]))
            ->assertSessionHasErrors('variants.0.id');
    }

    public function test_type_cannot_be_changed(): void
    {
        $filter = $this->createFilter();

        $this->actingAs($this->staff)
            ->put("/admin/filters/{$filter->id}", $this->payload([
                'type' => 'input_int',
                'variants' => [$this->variant(['id' => $filter->variants->first()->id])],
            ]))
            ->assertSessionHasNoErrors();

        // The sent type is ignored: a checkbox filter keeps its variants
        $this->assertSame('checkbox', $filter->refresh()->type->value);
        $this->assertCount(1, $filter->variants);
    }

    public function test_filter_can_be_toggled_and_soft_deleted(): void
    {
        $filter = $this->createFilter();

        $this->actingAs($this->staff)->patch("/admin/filters/{$filter->id}/toggle")->assertRedirect();
        $this->assertFalse($filter->refresh()->is_active);

        $this->actingAs($this->staff)->patch("/admin/filters/{$filter->id}/toggle")->assertRedirect();
        $this->assertTrue($filter->refresh()->is_active);

        $this->actingAs($this->staff)->delete("/admin/filters/{$filter->id}")->assertRedirect();

        $this->assertSoftDeleted('filters', ['id' => $filter->id]);
        $this->assertDatabaseHas('filter_trans', ['filter_id' => $filter->id]);
        $this->actingAs($this->staff)->get("/admin/filters/{$filter->id}/edit")->assertNotFound();

        // A deleted variant does not hold its slug any longer
        $this->createFilter(['name' => 'Новый']);
    }

    public function test_filters_can_be_moved(): void
    {
        $a = $this->createFilter(['name' => 'А', 'type' => 'input_int', 'variants' => []]);
        $b = $this->createFilter(['name' => 'Б', 'type' => 'input_int', 'variants' => []]);
        $c = $this->createFilter(['name' => 'В', 'type' => 'input_int', 'variants' => []]);

        $order = fn () => $this->category->filters()->pluck('id')->all();

        $this->assertSame([$a->id, $b->id, $c->id], $order());

        $this->actingAs($this->staff)->patch("/admin/filters/{$c->id}/move", ['direction' => 'up'])->assertRedirect();
        $this->assertSame([$a->id, $c->id, $b->id], $order());

        // Out of range: nothing changes
        $this->actingAs($this->staff)->patch("/admin/filters/{$a->id}/move", ['direction' => 'up'])->assertRedirect();
        $this->assertSame([$a->id, $c->id, $b->id], $order());

        $this->actingAs($this->staff)->patch("/admin/filters/{$a->id}/move", ['direction' => 'down'])->assertRedirect();
        $this->assertSame([$c->id, $a->id, $b->id], $order());

        $this->actingAs($this->staff)->patch("/admin/filters/{$a->id}/move", ['direction' => 'left'])->assertSessionHasErrors('direction');
    }

    public function test_variant_seo_templates_are_validated(): void
    {
        $ruOnly = $this->templateWith('ru');
        $enOnly = $this->templateWith('en');

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['variants' => [
                $this->variant(['seo_template_id' => $enOnly->id, 'translation_seo_template_id' => $ruOnly->id]),
            ]]))
            ->assertSessionHasNoErrors();

        $variant = Filter::firstOrFail()->variants->firstOrFail();
        $this->assertSame($enOnly->id, $variant->seo_template_id);
        $this->assertSame($ruOnly->id, $variant->translations->first()->seo_template_id);

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload(['name' => 'Форма', 'variants' => [
                $this->variant(['slug' => 'x', 'translation_seo_template_id' => $enOnly->id]),
            ]]))
            ->assertSessionHasErrors('variants.0.translation_seo_template_id');
    }

    public function test_filters_need_the_russian_language(): void
    {
        Language::where('code', 'ru')->delete();

        $this->actingAs($this->staff)
            ->post("/admin/categories/{$this->category->id}/filters", $this->payload())
            ->assertSessionHasErrors('name');
    }

    public function test_filter_can_be_translated(): void
    {
        $filter = $this->createFilter(['unit' => 'шт']);
        $variant = $filter->variants->firstOrFail();
        $template = $this->templateWith('en');

        $this->actingAs($this->staff)
            ->put("/admin/filters/{$filter->id}/translations/en", [
                'name' => 'Type',
                'unit' => 'pcs',
                'is_active' => true,
                'variants' => [[
                    'id' => $variant->id,
                    'name_in_filter' => 'Corner',
                    'name_full' => 'Corner sofa',
                    'name_buy' => 'Buy a corner sofa',
                    'slug' => '',
                    'seo_template_id' => $template->id,
                    'is_active' => false,
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect("/admin/categories/{$this->category->id}/filters");

        $en = $filter->translations()->where('lang', 'en')->firstOrFail();
        $this->assertSame('Type', $en->name);
        $this->assertSame('pcs', $en->unit);

        $variantEn = $variant->translations()->where('lang', 'en')->firstOrFail();
        $this->assertSame('corner-sofa', $variantEn->slug);
        $this->assertSame($template->id, $variantEn->seo_template_id);
        $this->assertFalse($variantEn->is_active);

        // The Russian version is untouched
        $this->assertSame('Тип', $filter->translations()->where('lang', 'ru')->firstOrFail()->name);
        $this->assertSame(2, FilterVariantTranslation::count()); // ru and en
    }

    public function test_translation_can_be_cleared(): void
    {
        $filter = $this->createFilter();
        $variant = $filter->variants->firstOrFail();

        $translate = fn (array $data) => $this->actingAs($this->staff)->put("/admin/filters/{$filter->id}/translations/en", [
            'name' => 'Type',
            'unit' => '',
            'is_active' => true,
            'variants' => [['id' => $variant->id, 'name_in_filter' => 'Corner', 'name_full' => 'Corner sofa', 'name_buy' => 'Buy', 'slug' => '', 'seo_template_id' => null, 'is_active' => true]],
            ...$data,
        ]);

        $translate([])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('filter_trans', ['filter_id' => $filter->id, 'lang' => 'en']);

        $translate([
            'name' => '',
            'variants' => [['id' => $variant->id, 'name_in_filter' => '', 'name_full' => '', 'name_buy' => '', 'slug' => '', 'seo_template_id' => null, 'is_active' => true]],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('filter_trans', ['filter_id' => $filter->id, 'lang' => 'en']);
        $this->assertDatabaseMissing('filter_variant_trans', ['filter_variant_id' => $variant->id, 'lang' => 'en']);
        $this->assertDatabaseHas('filter_trans', ['filter_id' => $filter->id, 'lang' => 'ru']);
    }

    public function test_translation_validation(): void
    {
        $filter = $this->createFilter();
        $variant = $filter->variants->firstOrFail();

        // A half-filled variant and a variant without the filter name
        $this->actingAs($this->staff)
            ->put("/admin/filters/{$filter->id}/translations/en", [
                'name' => '',
                'unit' => '',
                'is_active' => true,
                'variants' => [['id' => $variant->id, 'name_in_filter' => 'Corner', 'name_full' => '', 'name_buy' => '', 'slug' => '', 'seo_template_id' => null, 'is_active' => true]],
            ])
            ->assertSessionHasErrors(['name', 'variants.0.name_full', 'variants.0.name_buy']);

        // A template without a translation in this language
        $ruOnly = $this->templateWith('ru');

        $this->actingAs($this->staff)
            ->put("/admin/filters/{$filter->id}/translations/en", [
                'name' => 'Type',
                'unit' => '',
                'is_active' => true,
                'variants' => [['id' => $variant->id, 'name_in_filter' => 'Corner', 'name_full' => 'Corner sofa', 'name_buy' => 'Buy', 'slug' => '', 'seo_template_id' => $ruOnly->id, 'is_active' => true]],
            ])
            ->assertSessionHasErrors('variants.0.seo_template_id');
    }

    public function test_translation_slug_is_unique_within_category_and_language(): void
    {
        $filter = $this->createFilter();
        $second = $this->createFilter(['name' => 'Форма', 'variants' => [$this->variant(['slug' => 'other'])]]);

        $translate = fn (Filter $target, string $slug) => $this->actingAs($this->staff)->put("/admin/filters/{$target->id}/translations/en", [
            'name' => 'Name',
            'unit' => '',
            'is_active' => true,
            'variants' => [['id' => $target->variants->first()->id, 'name_in_filter' => 'A', 'name_full' => 'B', 'name_buy' => 'C', 'slug' => $slug, 'seo_template_id' => null, 'is_active' => true]],
        ]);

        $translate($filter, 'sofa')->assertSessionHasNoErrors();
        $translate($second, 'sofa')->assertSessionHasErrors('variants.0.slug');
    }

    public function test_base_and_unknown_languages_have_no_translation_page(): void
    {
        $filter = $this->createFilter();

        $this->actingAs($this->staff)->get("/admin/filters/{$filter->id}/translations/ru")->assertNotFound();
        $this->actingAs($this->staff)->get("/admin/filters/{$filter->id}/translations/de")->assertNotFound();
    }
}
