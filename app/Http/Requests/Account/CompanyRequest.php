<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\Concerns\NormalizesCompanyInput;
use App\Models\Category;
use App\Models\Company;
use App\Models\Region;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Fields of a company, common for the registration and for the account.
 *
 * phones: [{code, number, description}, ...]; categories: [category id, ...]
 */
abstract class CompanyRequest extends FormRequest
{
    use NormalizesCompanyInput;

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
        $this->merge([
            'legal_name' => $this->plainText($this->input('legal_name', '')),
            'tax_id' => $this->plainText($this->input('tax_id', '')),
            'display_name' => $this->plainText($this->input('display_name', '')),
            'is_online_store' => $this->boolean('is_online_store'),
            'website' => $this->normalizeUrl($this->input('website')),
            'phones' => $this->normalizePhones($this->input('phones', [])),
            'categories' => collect($this->input('categories', []))->map(fn ($id) => (int) $id)->unique()->values()->all(),
        ]);
    }

    /**
     * All fields of a company: the registration form has them on a single page.
     *
     * @return array<string, mixed>
     */
    protected function companyRules(): array
    {
        return [
            ...$this->companyFieldsRules(),
            ...$this->phonesRules(),
        ];
    }

    /**
     * The company itself: the "About" page of the account.
     *
     * @return array<string, mixed>
     */
    protected function companyFieldsRules(): array
    {
        $company = $this->company();

        return [
            'legal_name' => ['required', 'string', 'max:255'],
            'tax_id' => ['required', 'string', 'max:50'/* , Rule::unique('companies', 'tax_id')->ignore($company?->id) */],
            'display_name' => ['required', 'string', 'max:255'],
            'region_id' => ['required', 'integer', $this->regionRule($company)],
            // Cleaned on save: App\Casts\PurifiedHtml, profile "company"
            'description' => ['nullable', 'string', 'max:20000'],
            'is_online_store' => ['required', 'boolean'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer'],
        ];
    }

    /**
     * The contacts page of the account.
     *
     * @return array<string, mixed>
     */
    protected function phonesRules(): array
    {
        return [
            'phones' => ['required', 'array', 'min:1', 'max:10'],
            'phones.*.code' => ['required', 'regex:/^\+\d{1,5}$/'],
            'phones.*.number' => ['required', 'regex:/^\d[\d\s\-()]{2,17}\d$/'],
            'phones.*.description' => ['nullable', 'string', 'max:255'],
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
                $fail(__('Выберите город из списка.'));
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
                    $validator->errors()->add('categories', __('Можно выбрать только категории без подкатегорий.'));
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __('Заполните поле «:attribute».'),
            'string' => __('Поле «:attribute» должно быть текстом.'),
            'max.string' => __('Поле «:attribute» не должно быть длиннее :max символов.'),
            'email' => __('Укажите корректный email.'),
            'unique' => __('Значение поля «:attribute» уже занято.'),
            'url' => __('Укажите корректный адрес, например https://example.com.'),
            'confirmed' => __('Пароли не совпадают.'),
            'accepted' => __('Необходимо согласие с правилами и политикой.'),
            'min.array' => __('Выберите или добавьте хотя бы один элемент: «:attribute».'),
            'max.array' => __('В поле «:attribute» не больше :max элементов.'),
            'phones.*.code.regex' => __('Код — это цифры, например +375.'),
            'phones.*.number.regex' => __('Номер — это цифры, пробелы, дефисы и скобки.'),
            'phones.min' => __('Добавьте хотя бы один телефон.'),
            'phones.required' => __('Добавьте хотя бы один телефон.'),
            'categories.required' => __('Выберите хотя бы одну категорию.'),
            'categories.min' => __('Выберите хотя бы одну категорию.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'legal_name' => __('официальное название'),
            'tax_id' => 'Tax ID',
            'display_name' => __('отображаемое название'),
            'region_id' => __('регион'),
            'description' => __('описание'),
            'website' => __('сайт'),
            'phones' => __('телефоны'),
            'phones.*.code' => __('код'),
            'phones.*.number' => __('номер'),
            'phones.*.description' => __('пояснение к телефону'),
            'categories' => __('категории'),
            'name' => __('имя'),
            'email' => 'email',
            'password' => __('пароль'),
            'terms' => __('согласие'),
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
