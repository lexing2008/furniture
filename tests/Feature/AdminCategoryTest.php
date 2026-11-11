<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Category;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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

        Language::create([
            'code' => 'ru',
            'name' => 'Русский',
            'name_en' => 'Russian',
        ]);
    }

    public function test_category_name_buy_is_saved_and_listed(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), [
                'parent_id' => null,
                'seo_template_id' => null,
                'is_active' => true,
                'translations' => [[
                    'lang' => 'ru',
                    'name' => 'Диваны',
                    'menu_name' => 'Диваны в меню',
                    'name_buy' => 'Купить диваны',
                    'slug' => 'divany',
                    'seo_template_id' => null,
                    'seo_title' => null,
                    'seo_description' => null,
                    'seo_block' => null,
                ]],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->firstOrFail();

        $this->assertDatabaseHas('category_translations', [
            'category_id' => $category->id,
            'lang' => 'ru',
            'name_buy' => 'Купить диваны',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('categories.data.0.translations.0.name_buy', 'Купить диваны'));
    }
}
