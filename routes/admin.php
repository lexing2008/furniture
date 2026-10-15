<?php

use App\Enums\Roles;
use App\Http\Middleware\HandleInertiaAdminRequests;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\SeoTemplateController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// GUEST
Route::prefix('admin')->middleware(['guest', HandleInertiaAdminRequests::class])->group(function () {
    Route::get('/', [MainController::class, 'index'])->name('admin.index');

    // todo should solve
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);
});
// - - - GUEST

// AUTH (staff only)
Route::prefix('admin')->middleware(['auth', 'role:'.implode(',', Roles::employeeRoles()), HandleInertiaAdminRequests::class])->group(function () {
    Route::get('/', [MainController::class, 'index'])->name('admin.index');

    // Languages
    Route::prefix('languages')->group(function () {
        Route::get('/', [LanguageController::class, 'index'])->name('admin.languages.index');
        Route::get('/create', [LanguageController::class, 'create'])->name('admin.languages.create');
        Route::post('/', [LanguageController::class, 'store'])->name('admin.languages.store');
    });

    // Furniture categories
    Route::resource('categories', CategoryController::class)
        ->except(['show', 'destroy'])
        ->names('admin.categories');

    // Regions: country -> area -> city
    Route::resource('regions', RegionController::class)
        ->except(['show', 'destroy'])
        ->names('admin.regions');
    Route::patch('regions/{region}/toggle', [RegionController::class, 'toggle'])
        ->name('admin.regions.toggle');

    // SEO templates
    Route::resource('seo-templates', SeoTemplateController::class)
        ->except(['show', 'destroy'])
        ->names('admin.seo-templates');

    // Users (staff)
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)
            ->except('show')
            ->names('admin.users');

        Route::post('users/{user}/verification-notification', [UserController::class, 'sendVerification'])
            ->middleware('throttle:6,1')
            ->name('admin.users.verification.send');
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('admin.profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('admin.profile.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('admin.profile.destroy');
    });


    // todo should solve
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
