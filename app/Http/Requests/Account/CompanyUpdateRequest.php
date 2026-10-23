<?php

namespace App\Http\Requests\Account;

use App\Models\Company;

/**
 * Editing of the company information in the account. The contacts and the socials
 * are edited on their own pages and are not touched here.
 */
class CompanyUpdateRequest extends CompanyRequest
{
    protected function company(): ?Company
    {
        return $this->user()?->company;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->companyFieldsRules();
    }
}
