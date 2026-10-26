<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Delivery and payment descriptions of a company. Each page submits only its own field.
 */
class CompanyContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'delivery' => trim((string) $this->input('delivery', '')),
            'payment' => trim((string) $this->input('payment', '')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Cleaned on save: App\Casts\PurifiedHtml, profile "company"
            'delivery' => ['nullable', 'string', 'max:20000'],
            'payment' => ['nullable', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'string' => __('Поле «:attribute» должно быть текстом.'),
            'max.string' => __('Поле «:attribute» не должно быть длиннее :max символов.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'delivery' => __('описание доставки'),
            'payment' => __('описание оплаты'),
        ];
    }
}
