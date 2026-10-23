<?php

namespace App\Http\Requests\Account;

use App\Models\Company;

/**
 * Editing of the company in the account. email is the contact email of the company, not the login one.
 *
 * socials: {facebook: url, ...} for the keys of Company::SOCIALS
 */
class CompanyUpdateRequest extends CompanyRequest
{
    protected function company(): ?Company
    {
        return $this->user()?->company;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $socials = [];

        foreach (array_keys(Company::SOCIALS) as $key) {
            $socials[$key] = $this->normalizeUrl($this->input("socials.{$key}"));
        }

        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email', ''))) ?: null,
            'socials' => $socials,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->companyRules(),
            'email' => ['nullable', 'email', 'max:255'],
            'socials' => ['array'],
            'socials.*' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }

    public function companyData(): array
    {
        return [
            ...parent::companyData(),
            'email' => $this->validated('email'),
            // Empty networks are not stored
            'socials' => array_filter($this->validated('socials', [])) ?: null,
        ];
    }

    public function attributes(): array
    {
        return [
            ...parent::attributes(),
            'socials.*' => 'ссылка',
        ];
    }
}
