<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Language;
use App\Models\SeoTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): Response
    {
        $showAll = $request->boolean('all');

        // The tree is built in memory: categories count is small, and a tree can't be paginated by SQL
        $tree = Category::tree()->map(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->displayName(),
            'menu_name' => $category->menuName(),
            'depth' => $category->depth,
            'is_active' => $category->is_active,
            'filters_count' => $category->filters_count,
            'translations' => $category->translations
                ->map(fn (CategoryTranslation $t) => [
                    'code' => $t->lang,
                    'slug' => $t->slug,
                ])
                ->sortBy('code')
                ->values(),
        ]);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = $showAll ? max($tree->count(), 1) : self::PER_PAGE;

        $categories = new LengthAwarePaginator(
            $tree->forPage($showAll ? 1 : $page, $perPage)->values(),
            $tree->count(),
            $perPage,
            $showAll ? 1 : $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return Inertia::render('categories/IndexCategories', [
            'categories' => $categories,
            'showAll' => $showAll,
            'languagesCount' => Language::query()->count(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('categories/CreateCategory', [
            'languages' => $this->languages(),
            'parents' => $this->parentOptions(),
            'seoTemplates' => SeoTemplate::options(),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $category = Category::create($request->safe()->only(['parent_id', 'seo_template_id', 'is_active']));

            $category->translations()->createMany($request->filledTranslations());
        });

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Категория добавлена.');
    }

    public function edit(Category $category): Response
    {
        $category->load('translations');

        return Inertia::render('categories/EditCategory', [
            'category' => [
                'id' => $category->id,
                'parent_id' => $category->parent_id,
                'seo_template_id' => $category->seo_template_id,
                'is_active' => $category->is_active,
                'translations' => $category->translations->keyBy('lang'),
            ],
            'languages' => $this->languages(),
            'parents' => $this->parentOptions($category),
            'seoTemplates' => SeoTemplate::options(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        DB::transaction(function () use ($request, $category) {
            $category->update($request->safe()->only(['parent_id', 'seo_template_id', 'is_active']));

            foreach ($request->filledTranslations() as $translation) {
                $category->translations()->updateOrCreate(
                    ['lang' => $translation['lang']],
                    $translation,
                );
            }

            $category->translations()->whereIn('lang', $request->clearedLanguageCodes())->delete();
        });

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Категория сохранена.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: int, code: string, name: string}>
     */
    private function languages()
    {
        return Language::query()->orderBy('id')->get(['id', 'code', 'name']);
    }

    /**
     * Possible parents: the whole tree, except the category itself and its subcategories.
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, name: string, depth: int}>
     */
    private function parentOptions(?Category $category = null)
    {
        $excluded = $category ? [$category->id, ...$category->descendantIds()] : [];

        return Category::tree()
            ->reject(fn (Category $c) => in_array($c->id, $excluded, true))
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->displayName(),
                'depth' => $c->depth,
            ])
            ->values();
    }
}
