<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
            ],
            // Grace-period reminder before 2FA becomes mandatory for this user.
            'security' => function () use ($request) {
                $user = $request->user();
                if (! $user) {
                    return null;
                }
                $policy = app(\App\Services\TwoFactorPolicy::class);

                return [
                    'twoFactorReminderUntil' => $policy->shouldRemind($user)
                        ? $policy->graceEndsAt($user)?->toDateString()
                        : null,
                ];
            },
        ];
    }
}
