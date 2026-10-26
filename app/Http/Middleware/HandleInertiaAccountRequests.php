<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaAccountRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'account';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app_name' => config('app.name'),
            'locale' => app()->getLocale(),
            'company_region' => function () use ($request) {
                $region = $request->user()?->company()->with('region')->first()?->region;

                return $region === null ? null : [
                    'url' => $region->url(),
                    'name' => app()->getLocale() === 'en' ? $region->name_en : $region->name,
                ];
            },
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                // "verification-link-sent" after the letter was sent again
                'status' => fn () => $request->session()->get('status'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
