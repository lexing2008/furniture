<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\Concerns\NormalizesCompanyInput;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Contacts of the company: the contact email and the phones. The login email of
 * the user is a different field and is not changed here.
 */
class CompanyContactsRequest extends FormRequest
{
    use NormalizesCompanyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->normalizeEmail($this->input('email')),
            'phones' => $this->normalizePhones($this->input('phones', [])),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['nullable', 'email', 'max:255'],
            'phones' => ['required', 'array', 'min:1', 'max:10'],
            'phones.*.code' => ['required', 'regex:/^\+\d{1,5}$/'],
            'phones.*.number' => ['required', 'regex:/^\d[\d\s\-()]{2,17}\d$/'],
            'phones.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __('Заполните поле «:attribute».'),
            'string' => __('Поле «:attribute» должно быть текстом.'),
            'max.string' => __('Поле «:attribute» не должно быть длиннее :max символов.'),
            'email' => __('Укажите корректный email.'),
            'min.array' => __('Добавьте хотя бы один элемент: «:attribute».'),
            'max.array' => __('В поле «:attribute» не больше :max элементов.'),
            'phones.*.code.regex' => __('Код — это цифры, например +375.'),
            'phones.*.number.regex' => __('Номер — это цифры, пробелы, дефисы и скобки.'),
            'phones.min' => __('Добавьте хотя бы один телефон.'),
            'phones.required' => __('Добавьте хотя бы один телефон.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'email',
            'phones' => __('телефоны'),
            'phones.*.code' => __('код'),
            'phones.*.number' => __('номер'),
            'phones.*.description' => __('пояснение к телефону'),
        ];
    }
}
