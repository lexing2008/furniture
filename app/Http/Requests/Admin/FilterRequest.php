<?php

namespace App\Http\Requests\Admin;

use App\Enums\FilterType;
use App\Models\Category;
use App\Models\Filter;
use App\Models\FilterVariantTranslation;
use App\Models\Language;
use App\Support\Slug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Store and update of a category filter in the base language (ru).
 *
 * variants (checkbox and radio only), in the display order:
 * [{id, name_in_filter, name_full, name_buy, slug, seo_template_id, translation_seo_template_id, is_active}, ...]
 * A variant that is not sent on update is deleted.
 */
class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function category(): Category
    {
        return $this->route('category') ?? $this->route('filter')->category;
    }

    private function type(): ?FilterType
    {
        // The type of an existing filter cannot be changed: its variants would lose their meaning
        return $this->route('filter')?->type ?? FilterType::tryFrom((string) $this->input('type'));
    }

    protected function prepareForValidation(): void
    {
        $type = $this->type();

        $variants = $this->input('variants', []);

        if ($type?->hasVariants() && is_array($variants)) {
            $variants = collect($variants)
                ->map(fn ($variant) => $this->normalizeVariant(is_array($variant) ? $variant : []))
                ->values()
                ->all();
        }

        $unit = trim((string) $this->input('unit', ''));

        $this->merge([
            'type' => $type?->value,
            'digits_after_point' => $type === FilterType::INPUT_FLOAT ? $this->input('digits_after_point') : null,
            'name' => trim((string) $this->input('name', '')),
            'unit' => $unit === '' ? null : $unit,
            'is_active' => $this->boolean('is_active'),
            'variants' => $variants,
        ]);
    }

    /**
     * @param  array<string, mixed>  $variant
     * @return array<string, mixed>
     */
    private function normalizeVariant(array $variant): array
    {
        $nameInFilter = trim((string) ($variant['name_in_filter'] ?? ''));
        $nameFull = trim((string) ($variant['name_full'] ?? ''));
        $slug = trim((string) ($variant['slug'] ?? ''));

        return [
            ...$variant,
            'id' => ($variant['id'] ?? null) ?: null,
            'name_in_filter' => $nameInFilter,
            'name_full' => $nameFull,
            'name_buy' => trim((string) ($variant['name_buy'] ?? '')),
            // Slug transliterates Cyrillic: "Угловой диван" -> "uglovoy-divan"
            'slug' => Slug::make($slug !== '' ? $slug : ($nameFull !== '' ? $nameFull : $nameInFilter)),
            'seo_template_id' => ($variant['seo_template_id'] ?? null) ?: null,
            'translation_seo_template_id' => ($variant['translation_seo_template_id'] ?? null) ?: null,
            'is_active' => array_key_exists('is_active', $variant)
                ? filter_var($variant['is_active'], FILTER_VALIDATE_BOOLEAN)
                : true,
        ];
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $filter = $this->route('filter');
        $type = $this->type();

        return [
            'type' => ['required', Rule::enum(FilterType::class)],
            'digits_after_point' => [
                Rule::requiredIf($type === FilterType::INPUT_FLOAT),
                'nullable',
                'integer',
                'between:1,6',
            ],
            'is_active' => ['required', 'boolean'],
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! Language::query()->where('code', Filter::BASE_LANG)->exists()) {
                        $fail('Сначала добавьте язык «'.Filter::BASE_LANG.'»: фильтры заполняются на русском.');
                    }
                },
            ],
            'unit' => ['nullable', 'string', 'max:50'],

            // Numeric filters have no list of values
            'variants' => $type?->hasVariants() === false ? ['prohibited'] : ['array'],
            'variants.*.id' => $filter
                ? ['nullable', 'integer', Rule::exists('filter_variants', 'id')->where('filter_id', $filter->id)->whereNull('deleted_at')]
                : ['prohibited'],
            'variants.*.name_in_filter' => ['required', 'string', 'max:255'],
            'variants.*.name_full' => ['required', 'string', 'max:255'],
            'variants.*.name_buy' => ['required', 'string', 'max:255'],
            'variants.*.slug' => ['required', 'string', 'max:255'],
            'variants.*.seo_template_id' => ['nullable', 'integer', Rule::exists('seo_templates', 'id')],
            // Only a template that has a translation in the base language can be linked
            'variants.*.translation_seo_template_id' => [
                'nullable',
                'integer',
                Rule::exists('seo_template_translations', 'seo_template_id')->where('lang', Filter::BASE_LANG),
            ],
            'variants.*.is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $seen = [];

                foreach ($this->input('variants', []) as $index => $variant) {
                    $slug = $variant['slug'] ?? '';

                    if ($slug === '') {
                        continue;
                    }

                    if (isset($seen[$slug])) {
                        $validator->errors()->add("variants.{$index}.slug", 'Такой slug уже есть у другого варианта этого фильтра.');

                        continue;
                    }

                    $seen[$slug] = true;

                    if (FilterVariantTranslation::slugTaken($this->category()->id, Filter::BASE_LANG, $slug, $variant['id'] ?? null)) {
                        $validator->errors()->add("variants.{$index}.slug", 'Такой slug уже используется в этой категории.');
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Выберите тип фильтра.',
            'digits_after_point.required' => 'Укажите количество знаков после запятой.',
            'digits_after_point.between' => 'Количество знаков после запятой — от 1 до 6.',
            'variants.prohibited' => 'У числовых фильтров не бывает вариантов.',
            'variants.*.slug.required' => 'Не удалось построить slug из названия, укажите его вручную.',
            'variants.*.translation_seo_template_id.exists' => 'У выбранного SEO-шаблона нет перевода на русский.',
        ];
    }
}
