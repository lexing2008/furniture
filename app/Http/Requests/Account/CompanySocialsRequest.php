<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\Concerns\NormalizesCompanyInput;
use App\Models\Company;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Links to the social networks of the company: {facebook: url, ...} for the keys of Company::SOCIALS.
 */
class CompanySocialsRequest extends FormRequest
{
    use NormalizesCompanyInput;

    /**
     * Domains that belong to each social network.
     *
     * @var array<string, list<string>>
     */
    private const SOCIAL_DOMAINS = [
        'facebook' => ['facebook.com', 'fb.com'],
        'instagram' => ['instagram.com'],
        'youtube' => ['youtube.com', 'youtu.be'],
        'vk' => ['vk.com', 'vk.ru'],
        'telegram' => ['t.me', 'telegram.me'],
        'tiktok' => ['tiktok.com'],
        'linkedin' => ['linkedin.com'],
        'x' => ['x.com', 'twitter.com'],
        'ok' => ['ok.ru', 'odnoklassniki.ru'],
        'pinterest' => ['pinterest.com', 'pin.it'],
    ];

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
            'socials.*' => [
                'nullable',
                'url:http,https',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $key = substr($attribute, (int) strrpos($attribute, '.') + 1);
                    $allowedDomains = self::SOCIAL_DOMAINS[$key] ?? [];

                    if ($allowedDomains === []) {
                        return;
                    }

                    $host = rtrim(strtolower((string) parse_url($value, PHP_URL_HOST)), '.');

                    foreach ($allowedDomains as $domain) {
                        if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                            return;
                        }
                    }

                    $network = Company::SOCIALS[$key] ?? __('соцсеть');
                    $fail(__('Ссылка должна вести на :network.', ['network' => $network]));
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'max.string' => __('Поле «:attribute» не должно быть длиннее :max символов.'),
            'url' => __('Укажите корректный адрес, например https://example.com.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'socials.*' => __('ссылка'),
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
