<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
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
    public function boot(): void
    {
        // 1. Vite Asset Prefetching (standard for Laravel 11 + Inertia)
        Vite::prefetch(concurrency: 3);

        // 2. Force HTTPS on Render / Production environments
        if (config('app.env') !== 'local' || env('FORCE_HTTPS', false)) {
            URL::forceScheme('https');
        }

        // 3. Custom Password Reset Mail Notification
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Reset your SPT Platform password')
                ->greeting("Hello {$notifiable->first_name},")
                ->line('We received a request to reset the password for your PNP SPT Platform account.')
                ->action('Reset Password', $url)
                ->line('This link expires in 60 minutes.')
                ->line('If you did not request a password reset, no action is needed. Your password will not change.')
                ->salutation('PNP SPT Platform');
        });
    }
}