<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Account\Concerns\ResolvesCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CompanySocialsRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * The links to the social networks of the company of the logged in user.
 */
class CompanySocialsController extends Controller
{
    use ResolvesCompany;

    public function edit(Request $request): Response
    {
        $company = $this->company($request);

        return Inertia::render('Company/Socials', [
            'company' => [
                'socials' => (object) ($company->socials ?? []),
            ],
            'socials' => Company::SOCIALS,
        ]);
    }

    public function update(CompanySocialsRequest $request): RedirectResponse
    {
        $this->company($request)->update([
            'socials' => $request->socialsData(),
        ]);

        return back()->with('success', __('Ссылки на соцсети сохранены.'));
    }
}
