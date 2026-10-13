<?php

use App\Enums\Roles;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Middleware\HandleInertiaAdminRequests;

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
