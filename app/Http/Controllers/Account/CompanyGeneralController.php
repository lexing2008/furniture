<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Account\Concerns\ResolvesCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CompanyUpdateRequest;
use App\Http\Requests\Account\CompanyWebsiteRequest;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * General information about the company of the logged in user.
 */
class CompanyGeneralController extends Controller
{
    use ResolvesCompany;

    public function edit(Request $request): Response
    {
        $company = $this->company($request, ['region']);

        // The language of the region the visitor is on, otherwise of the region of the company
        $region = Region::fromHost($request->getHost()) ?? $company->region;

        return Inertia::render('Company/Edit', [
            'company' => [
                'region_id' => $company->region_id,
                'legal_name' => $company->legal_name,
                'tax_id' => $company->tax_id,
                'legal_address' => $company->legal_address ?? '',
                'actual_address' => $company->actual_address ?? '',
                'display_name' => $company->display_name,
                'description' => $company->description ?? '',
                'is_online_store' => $company->is_online_store,
                'website' => $company->website ?? '',
                'categories' => $company->categories()->pluck('categories.id')->values(),
            ],
            'regions' => Region::cityTree(),
            'categories' => Category::selectableTree($region?->language()),
        ]);
    }

    public function updateWebsite(CompanyWebsiteRequest $request): JsonResponse
    {
        $company = $this->company($request);
        $company->update(['website' => $request->validated('website')]);

        return response()->json([
            'website' => $company->website,
        ]);
    }

    public function update(CompanyUpdateRequest $request): RedirectResponse
    {
        $company = $this->company($request);
        $region = Region::findOrFail($request->validated('region_id'));

        DB::transaction(function () use ($request, $company, $region) {
            $company->update([
                ...$request->companyData(),
                'country_id' => $region->root()->id,
            ]);
            $company->syncCategories($request->validated('categories'));
        });

        return back()->with('success', __('Данные компании сохранены.'));
    }
}
