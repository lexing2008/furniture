<?php

namespace App\Http\Requests\Account;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Registration of a company together with the user it belongs to.
 */
class CompanyRegistrationRequest extends CompanyRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();

        $this->merge([
            'name' => $this->plainText($this->input('name', '')),
            'email' => mb_strtolower($this->plainText($this->input('email', ''))),
            'terms' => $this->boolean('terms'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->companyRules(),
            'name' => ['required', 'string', 'max:255'],
            // The users of the soft deleted accounts count too: their email is not free
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];
    }
}
