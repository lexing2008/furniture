<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CompanyUpdateRequest;
use App\Models\Category;
use App\Models\Company;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The company of the logged in user.
 */
class CompanyController extends Controller
{
    public function edit(Request $request): Response
    {
        $company = $this->company($request);

        // The language of the region the visitor is on, otherwise of the region of the company
        $region = Region::fromHost($request->getHost()) ?? $company->region;

        return Inertia::render('Company/Edit', [
            'company' => [
                'region_id' => $company->region_id,
                'legal_name' => $company->legal_name,
                'tax_id' => $company->tax_id,
                'display_name' => $company->display_name,
                'description' => $company->description ?? '',
                'is_online_store' => $company->is_online_store,
                'website' => $company->website ?? '',
                'email' => $company->email ?? '',
                'socials' => (object) ($company->socials ?? []),
                'phones' => $company->phones->map->only(['code', 'number', 'description'])->values(),
                'categories' => $company->categories()->pluck('categories.id')->values(),
            ],
            'socials' => Company::SOCIALS,
            'regions' => Region::cityOptions(),
            'categories' => Category::selectableTree($region?->language()),
            'regionName' => $company->region->name,
        ]);
    }

    public function update(CompanyUpdateRequest $request): RedirectResponse
    {
        $company = $this->company($request);

        DB::transaction(function () use ($request, $company) {
            $company->update($request->companyData());
            $company->syncRelations($request->validated('phones'), $request->validated('categories'));
        });

        return back()->with('success', 'Данные компании сохранены.');
    }

    private function company(Request $request): Company
    {
        return $request->user()->company()->with(['phones', 'region'])->firstOrFail();
    }
}
