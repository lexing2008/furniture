<?php

namespace App\Http\Controllers\Account;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CompanyRegistrationRequest;
use App\Models\Category;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Registration of a company: the company and the user it belongs to. Works on every region subdomain.
 */
class CompanyRegistrationController extends Controller
{
    public function create(Request $request): Response
    {
        // Указываем Inertia использовать другой Blade-шаблон
        Inertia::setRootView('guest');

        $region = Region::fromHost($request->getHost());
        $country = $region?->root();

        return Inertia::render('Company/Register', [
            'regions' => Region::cityTree(),
            'defaultRegionId' => $region?->isCity() ? $region->id : null,
            'phoneCode' => $country?->countryData?->phone_code,
            // Category names are in the language of the country of the region
            'categories' => Category::selectableTree($region?->language()),
            'terms' => $this->termsLinks(),
        ]);
    }

    public function store(CompanyRegistrationRequest $request): SymfonyResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role' => Roles::CUSTOMER,
            ]);

            $region = Region::findOrFail($request->validated('region_id'));

            $company = new Company($request->companyData());
            // The country is the root region: country -> area -> city
            $company->country_id = $region->root()->id;
            $company->user()->associate($user)->save();

            $company->syncRelations($request->validated('phones'), $request->validated('categories'));

            return $user;
        });

        // Sends the letter with the confirmation link
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        // A full page load, otherwise the account would keep the "guest" Blade shell of the registration page
        return Inertia::location(
            redirect()
                ->route('account.index')
                ->with('success', __('Регистрация завершена. Мы отправили письмо со ссылкой подтверждения на :email.', ['email' => $user->email]))
        );
    }

    /**
     * @return array{rules: string, privacy: string}
     */
    private function termsLinks(): array
    {
        return [
            'rules' => url('/terms'),
            'privacy' => url('/privacy'),
        ];
    }
}
