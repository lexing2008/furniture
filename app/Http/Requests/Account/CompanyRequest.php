<?php

namespace App\Http\Requests\Account;

use App\Models\Category;
use App\Models\Company;
use App\Models\Region;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Fields of a company, common for the registration and for the account.
 *
 * phones: [{code, number, description}, ...]; categories: [category id, ...]
 */
abstract class CompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The company that is being edited, null on registration.
     */
    protected function company(): ?Company
    {
        return null;
    }

    protected function prepareForValidation(): void
    {
        $phones = collect($this->input('phones', []))
            ->map(function ($phone) {
                $phone = is_array($phone) ? $phone : [];

                $code = preg_replace('/\s+/', '', trim((string) ($phone['code'] ?? '')));

                return [
                    // "375" and "+375" are the same
                    'code' => $code === '' ? '' : '+'.ltrim($code, '+'),
                    'number' => trim((string) ($phone['number'] ?? '')),
                    'description' => trim((string) ($phone['description'] ?? '')) ?: null,
                ];
            })
            // A row that was left blank is not a phone
            ->reject(fn (array $phone) => $phone['code'] === '' && $phone['number'] === '' && $phone['description'] === null)
            ->values()
            ->all();

        $this->merge([
            'legal_name' => trim((string) $this->input('legal_name', '')),
            'tax_id' => trim((string) $this->input('tax_id', '')),
            'display_name' => trim((string) $this->input('display_name', '')),
            'is_online_store' => $this->boolean('is_online_store'),
            'website' => $this->normalizeUrl($this->input('website')),
            'phones' => $phones,
            'categories' => collect($this->input('categories', []))->map(fn ($id) => (int) $id)->unique()->values()->all(),
        ]);
    }

    /**
     * "example.com" -> "https://example.com", an empty value is null.
     */
    protected function normalizeUrl(mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return preg_match('~^[a-z][a-z0-9+.-]*://~i', $value) ? $value : 'https://'.$value;
    }

    /**
     * @return array<string, mixed>
     */
    protected function companyRules(): array
    {
        $company = $this->company();

        return [
            'legal_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:50'/*, Rule::unique('companies', 'tax_id')->ignore($company?->id)*/],
            'display_name' => ['required', 'string', 'max:255'],
            'region_id' => ['required', 'integer', $this->regionRule($company)],
            // Cleaned on save: App\Casts\PurifiedHtml, profile "company"
            'description' => ['nullable', 'string', 'max:20000'],
            'is_online_store' => ['required', 'boolean'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'phones' => ['required', 'array', 'min:1', 'max:10'],
            'phones.*.code' => ['required', 'regex:/^\+\d{1,5}$/'],
            'phones.*.number' => ['required', 'regex:/^\d[\d\s\-()]{2,17}\d$/'],
            'phones.*.description' => ['nullable', 'string', 'max:255'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer'],
        ];
    }

    /**
     * Only a city that is shown on the site. The current region of a company is always allowed:
     * it is not the owner's fault when the region is disabled later.
     */
    private function regionRule(?Company $company): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($company) {
            if ($company !== null && (int) $value === $company->region_id) {
                return;
            }

            $isCity = collect(Region::cityOptions())->pluck('cities')->flatten(1)->contains('id', (int) $value);

            if (! $isCity) {
                $fail('Выберите город из списка.');
            }
        };
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $ids = $this->input('categories', []);

                if ($ids === [] || $validator->errors()->has('categories')) {
                    return;
                }

                // Active categories without subcategories
                $valid = Category::query()
                    ->whereKey($ids)
                    ->where('is_active', true)
                    ->whereDoesntHave('children')
                    ->count();

                if ($valid !== count($ids)) {
                    $validator->errors()->add('categories', 'Можно выбрать только категории без подкатегорий.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Заполните поле «:attribute».',
            'string' => 'Поле «:attribute» должно быть текстом.',
            'max.string' => 'Поле «:attribute» не должно быть длиннее :max символов.',
            'email' => 'Укажите корректный email.',
            'unique' => 'Значение поля «:attribute» уже занято.',
            'url' => 'Укажите корректный адрес, например https://example.com.',
            'confirmed' => 'Пароли не совпадают.',
            'accepted' => 'Необходимо согласие с правилами и политикой.',
            'min.array' => 'Выберите или добавьте хотя бы один элемент: «:attribute».',
            'max.array' => 'В поле «:attribute» не больше :max элементов.',
            'phones.*.code.regex' => 'Код — это цифры, например +375.',
            'phones.*.number.regex' => 'Номер — это цифры, пробелы, дефисы и скобки.',
            'phones.min' => 'Добавьте хотя бы один телефон.',
            'phones.required' => 'Добавьте хотя бы один телефон.',
            'categories.required' => 'Выберите хотя бы одну категорию.',
            'categories.min' => 'Выберите хотя бы одну категорию.',
        ];
    }

    public function attributes(): array
    {
        return [
            'legal_name' => 'официальное название',
            'tax_id' => 'Tax ID',
            'display_name' => 'отображаемое название',
            'region_id' => 'регион',
            'description' => 'описание',
            'website' => 'сайт',
            'phones' => 'телефоны',
            'phones.*.code' => 'код',
            'phones.*.number' => 'номер',
            'phones.*.description' => 'пояснение к телефону',
            'categories' => 'категории',
            'name' => 'имя',
            'email' => 'email',
            'password' => 'пароль',
            'terms' => 'согласие',
        ];
    }

    /**
     * The columns of the companies table that come from the form.
     *
     * @return array<string, mixed>
     */
    public function companyData(): array
    {
        return $this->safe()->only([
            'region_id', 'legal_name', 'tax_id', 'display_name', 'description', 'is_online_store', 'website',
        ]);
    }
}
