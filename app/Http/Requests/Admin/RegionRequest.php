<?php

namespace App\Http\Requests\Admin;

use App\Models\Region;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store and update of a region (country, area or city).
 */
class RegionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $parentId = $this->input('parent_id') ?: null;

        $data = [
            'parent_id' => $parentId,
            'subdomain' => mb_strtolower(trim((string) $this->input('subdomain', ''))),
            // Areas and cities use the language of their country
            'lang' => $parentId === null ? ($this->input('lang') ?: null) : null,
            'timezone' => trim((string) $this->input('timezone', '')) ?: null,
            'is_capital' => $this->boolean('is_active', false),
            'is_active' => $this->boolean('is_active', true),
        ];

        foreach (['name', 'name_en', 'name_in'] as $field) {
            $data[$field] = trim((string) $this->input($field, ''));
        }

        foreach (Region::SEO_FIELDS as $field) {
            $value = trim((string) $this->input($field, ''));
            $data[$field] = $value === '' ? null : $value;
        }

        $this->merge($data);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $region = $this->route('region');

        // Only a country needs its own values: other regions fall back to it
        $isCountry = $this->input('parent_id') === null;

        return [
            'parent_id' => ['nullable', 'integer', Rule::exists('regions', 'id'), $this->parentRule($region)],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_in' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/',
                Rule::notIn(Region::RESERVED_SUBDOMAINS),
                Rule::unique('regions', 'subdomain')->when($region, fn ($rule) => $rule->ignore($region)),
            ],
            'lang' => [
                Rule::requiredIf($isCountry),
                'nullable',
                'string',
                Rule::exists('languages', 'code'),
            ],
            'timezone' => [Rule::requiredIf($isCountry), 'nullable', 'string', 'timezone'],
            'seo_title' => [Rule::requiredIf($isCountry), 'nullable', 'string', 'max:255'],
            'seo_description' => [Rule::requiredIf($isCountry), 'nullable', 'string', 'max:500'],
            'seo_block' => [Rule::requiredIf($isCountry), 'nullable', 'string', 'max:65000'],
            'is_capital' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.regex' => 'Поддомен может содержать только латинские буквы, цифры и дефис (не в начале и не в конце).',
            'subdomain.unique' => 'Такой поддомен уже занят.',
            'subdomain.not_in' => 'Этот поддомен зарезервирован.',
            'lang.required' => 'Укажите язык страны.',
            'timezone.required' => 'Укажите часовой пояс страны.',
            'timezone.timezone' => 'Выберите часовой пояс из списка.',
            'seo_title.required' => 'Для страны SEO-поля обязательны: из неё их наследуют области и города.',
            'seo_description.required' => 'Для страны SEO-поля обязательны: из неё их наследуют области и города.',
            'seo_block.required' => 'Для страны SEO-поля обязательны: из неё их наследуют области и города.',
        ];
    }

    private function parentRule(?Region $region): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($region) {
            if ($value === null) {
                return;
            }

            if (! Region::allowedParents($region)->contains('id', (int) $value)) {
                $fail('Эта область или страна не может быть родителем: регионы бывают трёх уровней — страна, область, город.');
            }
        };
    }
}
