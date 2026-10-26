<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Account\Concerns\ResolvesCompany;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CompanyContentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Payment description of the company of the logged in user.
 */
class CompanyPaymentController extends Controller
{
    use ResolvesCompany;

    public function edit(Request $request): Response
    {
        $company = $this->company($request);

        return Inertia::render('Company/Payment', [
            'company' => [
                'payment' => $company->payment ?? '',
            ],
        ]);
    }

    public function update(CompanyContentRequest $request): RedirectResponse
    {
        $this->company($request)->update([
            'payment' => $request->validated('payment'),
        ]);

        return back()->with('success', __('Информация об оплате сохранена.'));
    }
}
