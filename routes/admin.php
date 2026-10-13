<?php

use App\Http\Middleware\HandleInertiaAdminRequests;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\LanguageController;
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

// AUTH
Route::prefix('admin')->middleware(['auth', HandleInertiaAdminRequests::class])->group(function () {
    Route::get('/', [MainController::class, 'index'])->name('admin.index');

    // Languages
    Route::prefix('languages')->group(function () {
        Route::get('/', [LanguageController::class, 'index'])->name('admin.languages.index');
        Route::get('/create', [LanguageController::class, 'create'])->name('admin.languages.create');
    });


    // todo should solve
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
