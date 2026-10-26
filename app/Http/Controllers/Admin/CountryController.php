<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryRequest;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Countries (regions with parent_id = null): phone code and currency data.
 */
class CountryController extends Controller
{
    public function index(): Response
    {
        $countries = Region::query()
            ->whereNull('parent_id')
            ->with('countryData')
            ->orderBy('name')
            ->get()
            ->map(fn (Region $region) => [
                'id' => $region->id,
                'name' => $region->name,
                'name_en' => $region->name_en,
                'subdomain' => $region->subdomain,
                'timezone' => $region->timezone,
                'phone_code' => $region->countryData?->phone_code,
                'currency' => $region->countryData?->currency,
                'currency_decimals' => $region->countryData?->currency_decimals,
            ]);

        return Inertia::render('countries/IndexCountries', [
            'countries' => $countries,
        ]);
    }

    public function edit(Region $country): Response
    {
        abort_unless($country->parent_id === null, 404);

        $country->loadMissing('countryData');

        return Inertia::render('countries/EditCountry', [
            'country' => [
                'id' => $country->id,
                'name' => $country->name,
                'name_en' => $country->name_en,
                'subdomain' => $country->subdomain,
                'timezone' => $country->timezone,
                'phone_code' => $country->countryData?->phone_code ?? '',
                'currency' => $country->countryData?->currency ?? '',
                'currency_decimals' => $country->countryData?->currency_decimals ?? 2,
            ],
        ]);
    }

    public function update(CountryRequest $request, Region $country): RedirectResponse
    {
        abort_unless($country->parent_id === null, 404);

        $country->countryData()->updateOrCreate([], $request->validated());

        return redirect()
            ->route('admin.countries.index')
            ->with('success', 'Данные страны сохранены.');
    }
}
