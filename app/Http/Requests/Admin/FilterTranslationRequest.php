<?php

namespace App\Http\Requests\Admin;

use App\Models\Filter;
use App\Models\FilterVariantTranslation;
use App\Support\Slug;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Translation of a filter to one language (route parameter "lang").
 *
 * An empty name means "no translation": the same for a variant, where the three names go together.
 * variants: [{id, name_in_filter, name_full, name_buy, slug, seo_template_id, is_active}, ...]
 */
class FilterTranslationRequest extends FormRequest
{
    private const VARIANT_NAMES = ['name_in_filter', 'name_full', 'name_buy'];

    public function authorize(): bool
    {
        return true;
    }

    public function lang(): string
    {
        return (string) $this->route('lang');
    }

    protected function prepareForValidation(): void
    {
        $unit = trim((string) $this->input('unit', ''));

        $variants = collect($this->input('variants', []))
            ->map(function ($variant) {
                $variant = is_array($variant) ? $variant : [];

                $names = [];
                foreach (self::VARIANT_NAMES as $field) {
                    $names[$field] = trim((string) ($variant[$field] ?? ''));
                }

                $slug = trim((string) ($variant['slug'] ?? ''));
                $filled = array_filter($names) !== [];

                return [
                    ...$variant,
                    ...$names,
                    'slug' => $filled
                        ? Slug::make($slug !== '' ? $slug : ($names['name_full'] !== '' ? $names['name_full'] : $names['name_in_filter']))
                        : '',
                    'seo_template_id' => ($variant['seo_template_id'] ?? null) ?: null,
                    'is_active' => array_key_exists('is_active', $variant)
                        ? filter_var($variant['is_active'], FILTER_VALIDATE_BOOLEAN)
                        : true,
                ];
            })
            ->values()
            ->all();

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'unit' => $unit === '' ? null : $unit,
            'is_active' => $this->boolean('is_active'),
            'variants' => $variants,
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $filter = $this->route('filter');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'is_active' => ['required', 'boolean'],
            'variants' => ['array'],
            'variants.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('filter_variants', 'id')->where('filter_id', $filter->id)->whereNull('deleted_at'),
            ],
            'variants.*.name_in_filter' => ['nullable', 'string', 'max:255'],
            'variants.*.name_full' => ['nullable', 'string', 'max:255'],
            'variants.*.name_buy' => ['nullable', 'string', 'max:255'],
            'variants.*.slug' => ['nullable', 'string', 'max:255'],
            // Only a template that has a translation in this language can be linked
            'variants.*.seo_template_id' => [
                'nullable',
                'integer',
                Rule::exists('seo_template_translations', 'seo_template_id')->where('lang', $this->lang()),
            ],
            'variants.*.is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $variants = $this->input('variants', []);
                $seen = [];
                $anyFilled = false;

                foreach ($variants as $index => $variant) {
                    $filled = array_filter(array_map(fn ($field) => $variant[$field] ?? '', self::VARIANT_NAMES)) !== [];

                    if (! $filled) {
                        continue;
                    }

                    $anyFilled = true;

                    // The three names go together: a half-filled variant is not a translation
                    foreach (self::VARIANT_NAMES as $field) {
                        if (($variant[$field] ?? '') === '') {
                            $validator->errors()->add("variants.{$index}.{$field}", 'Заполните все названия варианта или очистите их все.');
                        }
                    }

                    $slug = $variant['slug'] ?? '';

                    if ($slug === '') {
                        $validator->errors()->add("variants.{$index}.slug", 'Не удалось построить slug из названия, укажите его вручную.');

                        continue;
                    }

                    if (isset($seen[$slug])) {
                        $validator->errors()->add("variants.{$index}.slug", 'Такой slug уже есть у другого варианта этого фильтра.');

                        continue;
                    }

                    $seen[$slug] = true;

                    if (FilterVariantTranslation::slugTaken($this->route('filter')->category_id, $this->lang(), $slug, $variant['id'] ?? null)) {
                        $validator->errors()->add("variants.{$index}.slug", 'Такой slug уже используется в этой категории.');
                    }
                }

                // Variants and the unit without a filter name would be silently dropped otherwise
                if ($this->input('name') === '' && ($anyFilled || $this->input('unit') !== null)) {
                    $validator->errors()->add('name', 'Укажите название фильтра, иначе остальные поля не сохранятся.');
                }
            },
        ];
    }
}
