<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\Concerns\NormalizesCompanyInput;
use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Links to the social networks of the company: {facebook: url, ...} for the keys of Company::SOCIALS.
 */
class CompanySocialsRequest extends FormRequest
{
    use NormalizesCompanyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $socials = [];

        foreach (array_keys(Company::SOCIALS) as $key) {
            $socials[$key] = $this->normalizeUrl($this->input("socials.{$key}"));
        }

        $this->merge(['socials' => $socials]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'socials' => ['array'],
            'socials.*' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'max.string' => 'Поле «:attribute» не должно быть длиннее :max символов.',
            'url' => 'Укажите корректный адрес, например https://example.com.',
        ];
    }

    public function attributes(): array
    {
        return [
            'socials.*' => 'ссылка',
        ];
    }

    /**
     * Empty networks are not stored.
     *
     * @return array<string, string>|null
     */
    public function socialsData(): ?array
    {
        return array_filter($this->validated('socials', [])) ?: null;
    }
}
