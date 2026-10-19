<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FilterType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterRequest;
use App\Models\Category;
use App\Models\Filter;
use App\Models\Language;
use App\Models\SeoTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Filters of a category. They are filled in the base language only (ru),
 * other languages are translated on a separate page, see FilterTranslationController.
 */
class FilterController extends Controller
{
    public function index(Category $category): Response
    {
        $category->load('translations');

        $filters = $category->filters()
            ->with(['baseTranslation', 'translations:id,filter_id,lang'])
            ->withCount('variants')
            ->get()
            ->map(fn (Filter $filter) => [
                'id' => $filter->id,
                'name' => $filter->displayName(),
                'unit' => $filter->baseTranslation?->unit,
                'type' => $filter->type->value,
                'type_label' => $filter->type->label(),
                'digits_after_point' => $filter->digits_after_point,
                'variants_count' => $filter->variants_count,
                'is_active' => $filter->is_active,
                'translated' => $filter->translations->pluck('lang')->values(),
            ]);

        return Inertia::render('filters/IndexFilters', [
            'category' => ['id' => $category->id, 'name' => $category->displayName()],
            'filters' => $filters,
            'languages' => $this->otherLanguages(),
            'hasBaseLanguage' => Language::query()->where('code', Filter::BASE_LANG)->exists(),
        ]);
    }

    public function create(Category $category): Response
    {
        $category->load('translations');

        return Inertia::render('filters/CreateFilter', [
            'category' => ['id' => $category->id, 'name' => $category->displayName()],
            'types' => FilterType::options(),
            'seoTemplates' => SeoTemplate::options(),
        ]);
    }

    public function store(FilterRequest $request, Category $category): RedirectResponse
    {
        DB::transaction(function () use ($request, $category) {
            $filter = $category->filters()->create([
                ...$request->safe()->only(['type', 'digits_after_point', 'is_active']),
                'order' => ($category->filters()->max('order') ?? -1) + 1,
            ]);

            $this->saveBaseTranslation($filter, $request);
            $this->syncVariants($filter, $request->validated('variants', []));
        });

        return redirect()
            ->route('admin.categories.filters.index', $category)
            ->with('success', 'Фильтр добавлен.');
    }

    public function edit(Filter $filter): Response
    {
        $filter->load(['category.translations', 'baseTranslation', 'variants.translations']);

        return Inertia::render('filters/EditFilter', [
            'category' => ['id' => $filter->category->id, 'name' => $filter->category->displayName()],
            'filter' => [
                'id' => $filter->id,
                'type' => $filter->type->value,
                'digits_after_point' => $filter->digits_after_point,
                'is_active' => $filter->is_active,
                'name' => $filter->baseTranslation?->name ?? '',
                'unit' => $filter->baseTranslation?->unit ?? '',
                'variants' => $filter->variants->map(function ($variant) {
                    $translation = $variant->translations->firstWhere('lang', Filter::BASE_LANG);

                    return [
                        'id' => $variant->id,
                        'name_in_filter' => $translation?->name_in_filter ?? '',
                        'name_full' => $translation?->name_full ?? '',
                        'name_buy' => $translation?->name_buy ?? '',
                        'slug' => $translation?->slug ?? '',
                        'seo_template_id' => $variant->seo_template_id,
                        'translation_seo_template_id' => $translation?->seo_template_id,
                        'is_active' => $translation?->is_active ?? true,
                    ];
                })->values(),
            ],
            'types' => FilterType::options(),
            'seoTemplates' => SeoTemplate::options(),
        ]);
    }

    public function update(FilterRequest $request, Filter $filter): RedirectResponse
    {
        DB::transaction(function () use ($request, $filter) {
            $filter->update($request->safe()->only(['digits_after_point', 'is_active']));

            $this->saveBaseTranslation($filter, $request);
            $this->syncVariants($filter, $request->validated('variants', []));
        });

        return redirect()
            ->route('admin.categories.filters.index', $filter->category_id)
            ->with('success', 'Фильтр сохранён.');
    }

    public function toggle(Filter $filter): RedirectResponse
    {
        $filter->update(['is_active' => ! $filter->is_active]);

        return back()->with('success', $filter->is_active ? 'Фильтр активирован.' : 'Фильтр деактивирован.');
    }

    /**
     * Soft delete: the filter, its variants and translations stay in the database.
     */
    public function destroy(Filter $filter): RedirectResponse
    {
        $filter->delete();

        return back()->with('success', 'Фильтр удалён.');
    }

    public function move(Request $request, Filter $filter): RedirectResponse
    {
        $direction = $request->validate(['direction' => ['required', 'in:up,down']])['direction'];

        DB::transaction(function () use ($filter, $direction) {
            $ids = $filter->category->filters()->pluck('id')->all();

            $position = array_search($filter->id, $ids, true);
            $target = $direction === 'up' ? $position - 1 : $position + 1;

            if ($position === false || ! isset($ids[$target])) {
                return;
            }

            [$ids[$position], $ids[$target]] = [$ids[$target], $ids[$position]];

            // Orders are rewritten as 0..n, so equal or gappy values cannot break the swap
            foreach ($ids as $order => $id) {
                Filter::query()->whereKey($id)->update(['order' => $order]);
            }
        });

        return back();
    }

    private function saveBaseTranslation(Filter $filter, FilterRequest $request): void
    {
        $filter->translations()->updateOrCreate(
            ['lang' => Filter::BASE_LANG],
            ['name' => $request->validated('name'), 'unit' => $request->validated('unit')],
        );
    }

    /**
     * The variants come in the display order. Those that are missing are deleted (soft).
     *
     * @param  array<int, array<string, mixed>>  $variants
     */
    private function syncVariants(Filter $filter, array $variants): void
    {
        $kept = [];

        foreach ($variants as $position => $data) {
            $variant = isset($data['id'])
                ? $filter->variants()->findOrFail($data['id'])
                : $filter->variants()->make();

            $variant->fill(['order' => $position, 'seo_template_id' => $data['seo_template_id'] ?? null]);
            $variant->filter()->associate($filter)->save();

            $variant->translations()->updateOrCreate(
                ['lang' => Filter::BASE_LANG],
                [
                    'name_in_filter' => $data['name_in_filter'],
                    'name_full' => $data['name_full'],
                    'name_buy' => $data['name_buy'],
                    'slug' => $data['slug'],
                    'seo_template_id' => $data['translation_seo_template_id'] ?? null,
                    'is_active' => $data['is_active'],
                ],
            );

            $kept[] = $variant->id;
        }

        $filter->variants()->whereNotIn('id', $kept)->get()->each->delete();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{id: int, code: string, name: string}>
     */
    private function otherLanguages()
    {
        return Language::query()
            ->where('code', '!=', Filter::BASE_LANG)
            ->orderBy('id')
            ->get(['id', 'code', 'name']);
    }
}
