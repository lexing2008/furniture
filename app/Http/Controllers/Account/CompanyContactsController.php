<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Account\Concerns\ResolvesCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CompanyContactsRequest;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The contacts of the company of the logged in user: the contact email and the phones.
 */
class CompanyContactsController extends Controller
{
    use ResolvesCompany;

    public function edit(Request $request): Response
    {
        $company = $this->company($request, ['phones', 'region']);

        // The phone code of the country of the region the visitor is on, otherwise of the company region
        $region = $request->route('region');
        $region = $region instanceof Region ? $region : $company->region;
        $country = $region?->root();

        return Inertia::render('Company/Contacts', [
            'company' => [
                'email' => $company->email ?? '',
                'phones' => $company->phones->map->only(['code', 'number', 'description'])->values(),
            ],
            'phoneCode' => $country?->countryData?->phone_code,
        ]);
    }

    public function update(CompanyContactsRequest $request): RedirectResponse
    {
        $company = $this->company($request);

        DB::transaction(function () use ($request, $company) {
            $company->update(['email' => $request->validated('email')]);
            $company->syncPhones($request->validated('phones'));
        });

        return back()->with('success', __('Контактная информация сохранена.'));
    }
}
