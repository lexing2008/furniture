<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtolower(trim((string) $this->input('code'))),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Unique index in DB covers soft-deleted rows too, so check them as well
            'code' => ['required', 'string', 'max:5', 'regex:/^[a-z]{2}(-[a-z]{2})?$/', Rule::unique('languages', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Код должен быть в формате ISO: "en" или "pt-br".',
            'code.unique' => 'Язык с таким кодом уже существует.',
        ];
    }
}
