<?php

use App\Enums\Roles;
use App\Http\Controllers\Account\AdController;
use App\Http\Controllers\Account\AdImageController;
use App\Http\Controllers\Account\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Account\Auth\NewPasswordController;
use App\Http\Controllers\Account\Auth\PasswordResetLinkController;
use App\Http\Controllers\Account\CompanyContactsController;
use App\Http\Controllers\Account\CompanyController;
use App\Http\Controllers\Account\CompanyDeliveryController;
use App\Http\Controllers\Account\CompanyGeneralController;
use App\Http\Controllers\Account\CompanyPaymentController;
use App\Http\Controllers\Account\CompanyRegistrationController;
use App\Http\Controllers\Account\CompanyScheduleController;
use App\Http\Controllers\Account\CompanySocialsController;
use App\Http\Controllers\Account\LanguageController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RegionCategoryController;
use App\Http\Controllers\RegionPageController;
use App\Http\Middleware\HandleInertiaAccountRequests;
use App\Http\Middleware\HandleInertiaAdminRequests;
use App\Http\Middleware\SetLocaleFromUser;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Region pages: berlin.furniture.com. Registered first, the main domain never matches "{subdomain}.domain"
Route::domain('{region}.'.config('app.domain'))->group(function () {
    Route::get('/', [RegionPageController::class, 'show'])
        ->where('region', '[a-z0-9-]+')
        ->name('region.show');

    // Account
    Route::prefix('account')->middleware(['guest', HandleInertiaAccountRequests::class])->group(function () {
        // Registration of a company
        Route::get('register', [CompanyRegistrationController::class, 'create'])->name('account.register');
        Route::post('register', [CompanyRegistrationController::class, 'store']);

        // Auth
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('account.login');

        Route::post('login', [AuthenticatedSessionController::class, 'store']);

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('account.password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('account.password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('account.password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('account.password.store');
        // - - - end Auth
    });

    Route::prefix('account')->middleware(['auth', 'role:customer', SetLocaleFromUser::class, HandleInertiaAccountRequests::class])->group(function () {
        // Main page of the account (empty for now)
        Route::get('/', [CompanyController::class, 'index'])->name('account.index');
        Route::patch('language', [LanguageController::class, 'update'])->name('account.language.update');

        // Products and services
        Route::get('ads', [AdController::class, 'index'])->name('account.ads.index');
        Route::get('ads/create', [AdController::class, 'create'])->name('account.ads.create');
        Route::post('ads', [AdController::class, 'store'])->name('account.ads.store');
        Route::get('ads/filters', [AdController::class, 'filters'])->name('account.ads.filters');
        Route::post('ads/images', [AdImageController::class, 'store'])->name('account.ads.images.store');
        Route::post('ads/images/url', [AdImageController::class, 'storeUrl'])->name('account.ads.images.url');
        Route::delete('ads/images/{tmpFile}', [AdImageController::class, 'destroy'])->name('account.ads.images.destroy');
        Route::get('ads/{ad}/edit', [AdController::class, 'edit'])->name('account.ads.edit');
        Route::put('ads/{ad}', [AdController::class, 'update'])->name('account.ads.update');
        Route::patch('ads/{ad}/price', [AdController::class, 'updatePrice'])->name('account.ads.price.update');
        Route::patch('ads/{ad}/actualize', [AdController::class, 'actualize'])->name('account.ads.actualize');
        Route::patch('ads/{ad}/active', [AdController::class, 'toggleActive'])->name('account.ads.active.update');

        // General information about the company
        Route::get('general', [CompanyGeneralController::class, 'edit'])->name('account.general');
        Route::put('general', [CompanyGeneralController::class, 'update'])->name('account.general.update');
        Route::put('website', [CompanyGeneralController::class, 'updateWebsite'])->name('account.website.update');

        // Working hours of the company and its non-working days
        Route::get('schedule', [CompanyScheduleController::class, 'edit'])->name('account.schedule');
        Route::put('schedule', [CompanyScheduleController::class, 'update'])->name('account.schedule.update');

        // Delivery and payment descriptions of the company
        Route::get('delivery', [CompanyDeliveryController::class, 'edit'])->name('account.delivery');
        Route::put('delivery', [CompanyDeliveryController::class, 'update'])->name('account.delivery.update');

        Route::get('payment', [CompanyPaymentController::class, 'edit'])->name('account.payment');
        Route::put('payment', [CompanyPaymentController::class, 'update'])->name('account.payment.update');

        // Contacts of the company: email and phones
        Route::get('contacts', [CompanyContactsController::class, 'edit'])->name('account.contacts.edit');
        Route::put('contacts', [CompanyContactsController::class, 'update'])->name('account.contacts.update');

        // Links to the social networks of the company
        Route::get('socials', [CompanySocialsController::class, 'edit'])->name('account.socials.edit');
        Route::put('socials', [CompanySocialsController::class, 'update'])->name('account.socials.update');

        // Password of the logged in user
        Route::get('password', [PasswordController::class, 'edit'])->name('account.password.edit');
        Route::put('password', [PasswordController::class, 'update'])->name('account.password.update');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroyFromAccount'])
            ->name('account.logout');
    });
    // - - - end Account

    Route::get('categories/search', [RegionCategoryController::class, 'searchCategories'])
        ->name('region.categories.search');

    Route::get('favorites', [FavoriteController::class, 'index'])
        ->name('favorites.index');
    Route::post('favorites/{ad}', [FavoriteController::class, 'toggle'])
        ->middleware('throttle:favorite-toggle')
        ->name('favorites.toggle');

    // Placeholder category page and lazy-loaded category menu.
    Route::get('categories/{category}/children', [RegionCategoryController::class, 'children'])
        ->whereNumber('category')
        ->name('region.category.children');

    Route::get('/{categorySlug}/{filterSlugs?}', [RegionCategoryController::class, 'show'])
        ->where('categorySlug', '[a-z0-9-]+')
        ->where('filterSlugs', '[a-z0-9;-]+')
        ->name('region.category');
});

Route::get('/', function () {
    // todo: delete
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Index');
})->middleware(['auth', 'verified', 'role:'.implode(',', Roles::employeeRoles()), HandleInertiaAdminRequests::class])->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/account.php';
require __DIR__.'/auth.php';
