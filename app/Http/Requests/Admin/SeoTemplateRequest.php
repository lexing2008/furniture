<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Store and update of a SEO template.
 *
 * translations: [{lang, name, seo_title, seo_description, seo_block}, ...]
 * A language block with an empty name means "no translation for this language".
 */
class SeoTemplateRequest extends FormRequest
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

                return [
                    ...$translation,
                    'name' => trim((string) ($translation['name'] ?? '')),
                ];
            })
            ->all();

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'translations' => $translations,
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $template = $this->route('seo_template');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('seo_templates', 'name')->when($template, fn ($rule) => $rule->ignore($template)),
            ],
            'translations' => ['required', 'array'],
            'translations.*.lang' => ['required', 'string', 'distinct', Rule::exists('languages', 'code')],
            'translations.*.name' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_title' => ['nullable', 'string', 'max:255'],
            'translations.*.seo_description' => ['nullable', 'string', 'max:500'],
            'translations.*.seo_block' => ['nullable', 'string', 'max:65000'],
        ];
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
                    $hasSeo = collect(['seo_title', 'seo_description', 'seo_block'])
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
            'name.unique' => 'Шаблон с таким названием уже существует.',
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
}
