<?php

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
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorRecoveryController;
use App\Http\Controllers\Security\TwoFactorSetupController;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');

    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.login');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store']);

    // Lost authenticator: email a reset link to the verified address.
    Route::post('/two-factor-challenge/email-recovery', [TwoFactorRecoveryController::class, 'send'])
        ->middleware('throttle:3,10')->name('two-factor.recovery.send');
    Route::get('/two-factor-recovery/{user}', [TwoFactorRecoveryController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])->name('two-factor.recovery.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    //old laravel controller routes for password confirmation and update, commented out for now
    //Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
    //    ->name('password.confirm');

    //Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // "Secure your account" (forced by EnsureAccountIsSecured when 2FA is required)
    Route::get('security/two-factor', [TwoFactorSetupController::class, 'show'])->name('security.setup');
    Route::post('security/two-factor/start', [TwoFactorSetupController::class, 'start'])->name('security.setup.start');
    Route::post('security/two-factor/confirm', [TwoFactorSetupController::class, 'confirm'])
        ->middleware('throttle:10,1')->name('security.setup.confirm');
    Route::post('security/two-factor/acknowledge', [TwoFactorSetupController::class, 'acknowledge'])->name('security.setup.acknowledge');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
