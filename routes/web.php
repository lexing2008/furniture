<?php

use App\Enums\Roles;
use App\Http\Controllers\RegionPageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Http\Middleware\HandleInertiaAdminRequests;

// Region pages: berlin.furniture.com. Registered first, the main domain never matches "{subdomain}.domain"
Route::domain('{region}.'.config('app.domain'))->group(function () {
    Route::get('/', [RegionPageController::class, 'show'])
        ->where('region', '[a-z0-9-]+')
        ->name('region.show');
});

Route::get('/', function () {
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
