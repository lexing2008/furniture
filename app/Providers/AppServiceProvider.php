<?php

namespace App\Providers;

use App\Http\Services\RegionService;
use App\Models\Region;
use App\Services\MenuCategoryService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(RegionService $regionService): void
    {
        Vite::prefetch(concurrency: 3);

        // Не более двух переключений избранного в секунду на пользователя/IP.
        RateLimiter::for('favorite-toggle', function (Request $request) {
            return Limit::perSecond(2)->by(
                $request->user()?->getAuthIdentifier() ?: 'ip:'.$request->ip()
            );
        });

        // Указываем Laravel, что параметр subdomain {region} — это модель Region,
        // и искать её нужно по полю 'subdomain'
        Route::bind('region', function ($value) {
            // 1. Получаем основной домен приложения из конфига (например, mysite.com)
            $mainDomain = config('app.domain');
            $currentHost = request()->getHost();

            // 2. Проверяем, является ли текущий хост поддоменом основного домена
            // Например: ://mysite.com
            $isSubdomain = ($currentHost === "{$value}.{$mainDomain}");

            // Если это не поддомен (а, например, часть обычного пути),
            // возвращаем значение как обычную строку, не превращая в модель
            if (! $isSubdomain) {
                return $value;
            }

            // подставляем значение в URL по умолчанию
            URL::defaults(['region' => $value]);

            // 3. Если это действительно поддомен, берём регион через общий
            return Region::query()
                ->where('subdomain', $value)
                ->where('is_active', true)
                ->firstOrFail();
        });

        // pass data to template
        View::composer(
            ['layouts.site', 'region.*', 'guest', 'layouts.partials._header', 'layouts.partials._footer'],
            function ($view) use ($regionService) {
                // Получаем модель Region из текущего маршрута
                $region = request()->route('region');
                if (! $region) {
                    return;
                }
                $country = $region->root();

                $locale = app()->getLocale();
                $menuCategories = MenuCategoryService::forLocale($locale)
                    ->map(function (array $category) use ($region) {
                        return [
                            ...$category,
                            'url' => route('region.category', [
                                'region' => $region->subdomain,
                                'categorySlug' => $category['slug'],
                            ]),
                        ];
                    })
                    ->values();

                $view->with([
                    'region' => $region,
                    'country' => $country,
                    'capitalCities' => $regionService->getCapitalCities($country),
                    // The locale is set by App\Http\Middleware\SetLocaleFromRegion
                    'language' => $locale,
                    'menuCategories' => $menuCategories,
                ]);
            });
    }
}
