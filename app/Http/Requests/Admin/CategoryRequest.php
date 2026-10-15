<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Store and update of a category.
 *
 * translations: [{lang, name, slug, seo_template_id, seo_title, seo_description, seo_block}, ...]
 * A language block with an empty name means "no translation for this language".
 */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $translations = collect($this->input('translations', []))
            ->map(function ($translation) {
                $translation = is_array($translation) ? $translation : [];
                $name = trim((string) ($translation['name'] ?? ''));
                $slug = trim((string) ($translation['slug'] ?? ''));

                return [
                    ...$translation,
                    'name' => $name,
                    // Str::slug transliterates Cyrillic: "Мягкая мебель" -> "miagkaia-mebel"
                    'slug' => Str::slug($slug !== '' ? $slug : $name),
                    // The select sends "" for "no template"
                    'seo_template_id' => ($translation['seo_template_id'] ?? null) ?: null,
                ];
            })
            ->all();

        $this->merge([
            'translations' => $translations,
            // The select sends "" for "no template"
            'seo_template_id' => $this->input('seo_template_id') ?: null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');

        $rules = [
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id'), $this->parentRule($category)],
            'seo_template_id' => ['nullable', 'integer', Rule::exists('seo_templates', 'id')],
            'is_active' => ['required', 'boolean'],
            'translations' => ['required', 'array'],
            'translations.*.lang' => ['required', 'string', 'distinct', Rule::exists('languages', 'code')],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.seo_block' => ['nullable', 'string', 'max:65000'],
        ];

        foreach ($this->input('translations', []) as $index => $translation) {
            $filled = ($translation['name'] ?? '') !== '';

            $rules["translations.{$index}.name"] = $filled ? ['string', 'max:255'] : ['nullable'];

            $rules["translations.{$index}.slug"] = $filled
                ? [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('category_translations', 'slug')
                        ->where('lang', (string) ($translation['lang'] ?? ''))
                        ->when($category, fn ($rule) => $rule->whereNot('category_id', $category->id)),
                ]
                : ['nullable'];

            // Only a template that has a translation in the same language can be linked
            $rules["translations.{$index}.seo_template_id"] = [
                'nullable',
                'integer',
                Rule::exists('seo_template_translations', 'seo_template_id')
                    ->where('lang', (string) ($translation['lang'] ?? '')),
            ];
        }

        return $rules;
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $hasName = collect($this->input('translations', []))
                    ->contains(fn ($translation) => ($translation['name'] ?? '') !== '');

                if (! $hasName) {
                    $validator->errors()->add('translations', 'Заполните название хотя бы на одном языке.');
                }

                // SEO texts without a name would be silently dropped otherwise
                foreach ($this->input('translations', []) as $index => $translation) {
                    $hasSeo = ($translation['seo_template_id'] ?? null) !== null
                        || collect(['seo_title', 'seo_description', 'seo_block'])
                            ->contains(fn ($field) => trim((string) ($translation[$field] ?? '')) !== '');

                    if (($translation['name'] ?? '') === '' && $hasSeo) {
                        $validator->errors()->add("translations.{$index}.name", 'Укажите название, иначе SEO-поля не сохранятся.');
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'translations.*.slug.unique' => 'Такой slug уже используется для этого языка.',
            'translations.*.slug.required' => 'Не удалось построить slug из названия, укажите его вручную.',
            'translations.*.seo_template_id.exists' => 'У выбранного SEO-шаблона нет перевода на этот язык.',
        ];
    }

    /**
     * Translations that should be saved (with a name).
     *
     * @return array<int, array<string, mixed>>
     */
    public function filledTranslations(): array
    {
        return collect($this->validated('translations'))
            ->filter(fn (array $translation) => ($translation['name'] ?? '') !== '')
            ->values()
            ->all();
    }

    /**
     * Languages whose translation was cleared in the form.
     *
     * @return array<int, string>
     */
    public function clearedLanguageCodes(): array
    {
        return collect($this->validated('translations'))
            ->filter(fn (array $translation) => ($translation['name'] ?? '') === '')
            ->pluck('lang')
            ->all();
    }

    private function parentRule(?Category $category): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($category) {
            if ($category === null || $value === null) {
                return;
            }

            if ((int) $value === $category->id || in_array((int) $value, $category->descendantIds(), true)) {
                $fail('Нельзя выбрать родителем саму категорию или её подкатегорию.');
            }
        };
    }
}
