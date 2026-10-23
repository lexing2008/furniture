<?php

use App\Enums\Roles;
use App\Http\Controllers\Account\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Account\Auth\NewPasswordController;
use App\Http\Controllers\Account\Auth\PasswordResetLinkController;
use App\Http\Controllers\Account\CompanyController;
use App\Http\Controllers\Account\CompanyRegistrationController;
use App\Http\Controllers\RegionPageController;
use App\Http\Middleware\HandleInertiaAccountRequests;
use App\Http\Middleware\HandleInertiaAdminRequests;
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

    Route::prefix('account')->middleware(['auth', 'role:customer', HandleInertiaAccountRequests::class])->group(function () {
        Route::get('/', [CompanyController::class, 'edit'])->name('account.index');
        Route::put('/', [CompanyController::class, 'update'])->name('account.update');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroyFromAccount'])
            ->name('account.logout');
    });
    // - - - end Account
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
