<?php

namespace App\Providers;

use App\Http\Services\RegionService;
use App\Models\Region;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


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
            if (!$isSubdomain) {
                return $value;
            }

            // подставляем значение в URL по умолчанию
            URL::defaults(['region' => $value]);

            // 3. Если это действительно поддомен, ищем регион в базе данных
            return Region::query()
                ->where('subdomain', $value)
                ->where('is_active', true) // Хорошая практика: проверять, активен ли регион
                ->firstOrFail();
        });


        // pass data to template
        View::composer(['layouts.site', 'region.*', 'guest'], function ($view) use($regionService) {
            // Получаем модель Region из текущего маршрута
            $region = request()->route('region');
            if(!$region){
                return;
            }
            $country = $region->root();
            $language = $country->lang;

            if ($language !== null) {
                App::setLocale($language);
            }

            $view->with([
                'region'    => $region,
                'country'   => $country,
                'areas'     => $regionService->getRegionsTreeForPopup($country),
                'language'  => $language ?? config('app.locale'),
            ]);
        });
    }
}
