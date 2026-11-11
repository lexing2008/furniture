<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Country data of a region with parent_id = null.
 */
class CountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // The phone code is stored without "+"
            'phone_code' => ltrim(trim((string) $this->input('phone_code', '')), '+'),
            'currency' => mb_strtoupper(trim((string) $this->input('currency', ''))),
            'tax_id_label' => trim((string) $this->input('tax_id_label', '')) ?: null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'phone_code' => ['required', 'regex:/^\d{1,5}$/'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'currency_decimals' => ['required', 'integer', 'between:0,4'],
            'tax_id_label' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_code.required' => 'Укажите код телефона страны.',
            'phone_code.regex' => 'Код телефона — только цифры, без знака +.',
            'currency.required' => 'Укажите валюту страны.',
            'currency.size' => 'Валюта должна состоять из трёх букв, например BYN.',
            'currency.regex' => 'Валюта — три латинские буквы, например BYN.',
            'currency_decimals.required' => 'Укажите количество знаков после запятой.',
            'currency_decimals.between' => 'Количество знаков после запятой — от 0 до 4.',
        ];
    }

    public function attributes(): array
    {
        return [
            'phone_code' => 'код телефона',
            'currency' => 'валюта',
            'currency_decimals' => 'количество знаков после запятой',
            'tax_id_label' => 'название налогового номера',
        ];
    }
}
