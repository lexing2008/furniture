<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\Concerns\NormalizesCompanyInput;
use Illuminate\Foundation\Http\FormRequest;

class CompanyWebsiteRequest extends FormRequest
{
    use NormalizesCompanyInput;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'website' => $this->normalizeUrl($this->input('website')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'website' => ['required', 'url:http,https', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'website' => __('сайт'),
        ];
    }
}
