<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FilterTranslationRequest;
use App\Models\Filter;
use App\Models\Language;
use App\Models\SeoTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Translation of a filter to a language other than the base one. The base (ru) values are shown as a hint.
 */
class FilterTranslationController extends Controller
{
    public function edit(Filter $filter, string $lang): Response
    {
        $language = $this->language($lang);

        $filter->load(['category.translations', 'translations', 'variants.translations']);

        $base = $filter->translations->firstWhere('lang', Filter::BASE_LANG);
        $own = $filter->translations->firstWhere('lang', $lang);

        return Inertia::render('filters/EditFilterTranslation', [
            'category' => ['id' => $filter->category->id, 'name' => $filter->category->displayName()],
            'filter' => [
                'id' => $filter->id,
                'type' => $filter->type->value,
                'digits_after_point' => $filter->digits_after_point,
            ],
            'language' => $language->only(['code', 'name']),
            'languages' => Language::query()
                ->where('code', '!=', Filter::BASE_LANG)
                ->orderBy('id')
                ->get(['id', 'code', 'name']),
            // Base language values: shown as a hint, not editable
            'base' => [
                'name' => $base?->name ?? '',
                'unit' => $base?->unit ?? '',
            ],
            'form' => [
                'name' => $own?->name ?? '',
                'unit' => $own?->unit ?? '',
                'is_active' => $own?->is_active ?? true,
                'variants' => $filter->variants->map(function ($variant) use ($lang) {
                    $base = $variant->translations->firstWhere('lang', Filter::BASE_LANG);
                    $own = $variant->translations->firstWhere('lang', $lang);

                    return [
                        'id' => $variant->id,
                        'base' => [
                            'name_in_filter' => $base?->name_in_filter ?? '',
                            'name_full' => $base?->name_full ?? '',
                            'name_buy' => $base?->name_buy ?? '',
                            'slug' => $base?->slug ?? '',
                        ],
                        'name_in_filter' => $own?->name_in_filter ?? '',
                        'name_full' => $own?->name_full ?? '',
                        'name_buy' => $own?->name_buy ?? '',
                        'slug' => $own?->slug ?? '',
                        'seo_template_id' => $own?->seo_template_id,
                        'is_active' => $own?->is_active ?? true,
                    ];
                })->values(),
            ],
            'seoTemplates' => SeoTemplate::options(),
        ]);
    }

    public function update(FilterTranslationRequest $request, Filter $filter, string $lang): RedirectResponse
    {
        $this->language($lang);

        DB::transaction(function () use ($request, $filter, $lang) {
            if ($request->validated('name') !== '' && $request->validated('name') !== null) {
                $filter->translations()->updateOrCreate(
                    ['lang' => $lang],
                    $request->safe()->only(['name', 'unit', 'is_active']),
                );
            } else {
                $filter->translations()->where('lang', $lang)->forceDelete();
            }

            foreach ($request->validated('variants', []) as $data) {
                $variant = $filter->variants()->findOrFail($data['id']);

                $filled = ($data['name_in_filter'] ?? '') !== '';

                if ($filled) {
                    $variant->translations()->updateOrCreate(
                        ['lang' => $lang],
                        [
                            'name_in_filter' => $data['name_in_filter'],
                            'name_full' => $data['name_full'],
                            'name_buy' => $data['name_buy'],
                            'slug' => $data['slug'],
                            'seo_template_id' => $data['seo_template_id'] ?? null,
                            'is_active' => $data['is_active'],
                        ],
                    );
                } else {
                    $variant->translations()->where('lang', $lang)->forceDelete();
                }
            }
        });

        return redirect()
            ->route('admin.categories.filters.index', $filter->category_id)
            ->with('success', 'Перевод фильтра сохранён.');
    }

    /**
     * The base language is not a translation, it is edited together with the filter.
     */
    private function language(string $lang): Language
    {
        abort_if($lang === Filter::BASE_LANG, 404);

        return Language::query()->where('code', $lang)->firstOrFail();
    }
}
