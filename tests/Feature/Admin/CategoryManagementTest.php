<?php

namespace Tests\Feature\Admin;

use App\Enums\Roles;
use App\Models\Category;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private Language $ru;

    private Language $en;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => Roles::MANAGER]);
        $this->ru = Language::create(['code' => 'ru', 'name' => 'Русский', 'name_en' => 'Russian']);
        $this->en = Language::create(['code' => 'en', 'name' => 'English', 'name_en' => 'English']);
    }

    private function translation(Language $language, array $data = []): array
    {
        return [
            'lang' => $language->code,
            'name' => '',
            'slug' => '',
            'seo_title' => '',
            'seo_description' => '',
            'seo_block' => '',
            ...$data,
        ];
    }

    private function createCategory(string $ruName, ?int $parentId = null): Category
    {
        $this->actingAs($this->staff)->post('/admin/categories', [
            'parent_id' => $parentId,
            'is_active' => true,
            'translations' => [$this->translation($this->ru, ['name' => $ruName])],
        ])->assertSessionHasNoErrors();

        return Category::latest('id')->firstOrFail();
    }

    public function test_category_can_be_created_with_translations(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'parent_id' => null,
                'is_active' => true,
                'translations' => [
                    $this->translation($this->ru, [
                        'name' => 'Мягкая мебель',
                        'seo_title' => 'Купить мягкую мебель',
                        'seo_description' => 'Описание',
                        'seo_block' => 'Длинный SEO-текст',
                    ]),
                    $this->translation($this->en, ['name' => 'Upholstered', 'slug' => 'Soft Furniture']),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/categories');

        $category = Category::with('translations')->firstOrFail();

        $this->assertTrue($category->is_active);
        $this->assertCount(2, $category->translations);

        $ru = $category->translations->firstWhere('lang', 'ru');
        $this->assertSame('Мягкая мебель', $ru->name);
        $this->assertNotSame('', $ru->slug); // generated from the Cyrillic name
        $this->assertSame('Длинный SEO-текст', $ru->seo_block);

        $en = $category->translations->firstWhere('lang', 'en');
        $this->assertSame('soft-furniture', $en->slug);
    }

    public function test_empty_language_blocks_are_skipped(): void
    {
        $category = $this->createCategory('Диваны');

        $this->assertCount(1, $category->translations);
    }

    public function test_at_least_one_name_is_required(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'is_active' => true,
                'translations' => [$this->translation($this->ru), $this->translation($this->en)],
            ])
            ->assertSessionHasErrors('translations');
    }

    public function test_seo_fields_without_name_are_rejected(): void
    {
        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'is_active' => true,
                'translations' => [
                    $this->translation($this->ru, ['name' => 'Диваны']),
                    $this->translation($this->en, ['seo_title' => 'Sofas']),
                ],
            ])
            ->assertSessionHasErrors('translations.1.name');
    }

    public function test_slug_must_be_unique_within_language(): void
    {
        $this->createCategory('Диваны');

        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'is_active' => true,
                'translations' => [$this->translation($this->ru, ['name' => 'Диваны'])],
            ])
            ->assertSessionHasErrors('translations.0.slug');

        // The same slug in another language is fine
        $this->actingAs($this->staff)
            ->post('/admin/categories', [
                'is_active' => true,
                'translations' => [$this->translation($this->en, ['name' => 'Divany'])],
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_category_can_be_updated_and_translation_removed(): void
    {
        $category = $this->createCategory('Диваны');
        $slug = $category->translations->first()->slug;

        $this->actingAs($this->staff)
            ->put("/admin/categories/{$category->id}", [
                'parent_id' => null,
                'is_active' => false,
                'translations' => [
                    // Keeping its own slug must not trigger the unique rule
                    $this->translation($this->ru, ['name' => 'Диваны и кресла', 'slug' => $slug]),
                    $this->translation($this->en, ['name' => 'Sofas']),
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/admin/categories');

        $category->refresh()->load('translations');

        $this->assertFalse($category->is_active);
        $this->assertCount(2, $category->translations);
        $this->assertSame('Диваны и кресла', $category->translations->firstWhere('lang', 'ru')->name);

        // Clearing the English name removes the English translation
        $this->actingAs($this->staff)
            ->put("/admin/categories/{$category->id}", [
                'parent_id' => null,
                'is_active' => true,
                'translations' => [
                    $this->translation($this->ru, ['name' => 'Диваны и кресла', 'slug' => $slug]),
                    $this->translation($this->en),
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $category->refresh()->translations);
    }

    public function test_parent_cannot_be_self_or_descendant(): void
    {
        $parent = $this->createCategory('Мягкая мебель');
        $child = $this->createCategory('Диваны', $parent->id);
        $grandChild = $this->createCategory('Угловые диваны', $child->id);

        foreach ([$parent->id, $grandChild->id] as $invalidParent) {
            $this->actingAs($this->staff)
                ->put("/admin/categories/{$parent->id}", [
                    'parent_id' => $invalidParent,
                    'is_active' => true,
                    'translations' => [$this->translation($this->ru, ['name' => 'Мягкая мебель'])],
                ])
                ->assertSessionHasErrors('parent_id');
        }

        $this->assertNull($parent->refresh()->parent_id);
    }

    public function test_index_shows_tree_and_can_disable_pagination(): void
    {
        $parent = $this->createCategory('Мягкая мебель');
        $this->createCategory('Диваны', $parent->id);

        for ($i = 1; $i <= 25; $i++) {
            $this->createCategory("Категория {$i}");
        }

        $this->actingAs($this->staff)
            ->get('/admin/categories')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('categories/IndexCategories')
                ->has('categories.data', 20)
                ->where('categories.total', 27)
                ->where('showAll', false)
            );

        $this->actingAs($this->staff)
            ->get('/admin/categories?all=1')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('categories.data', 27)
                ->where('showAll', true)
                ->where('categories.data', function ($data) {
                    $data = collect($data);
                    $parentIndex = $data->search(fn ($c) => $c['name'] === 'Мягкая мебель');
                    $child = $data[$parentIndex + 1];

                    // Child goes right after its parent, one level deeper
                    return $child['name'] === 'Диваны' && $child['depth'] === 1;
                })
            );
    }

    public function test_customer_cannot_manage_categories(): void
    {
        $customer = User::factory()->create(['role' => Roles::CUSTOMER]);

        $this->actingAs($customer)->get('/admin/categories')->assertForbidden();
    }
}
