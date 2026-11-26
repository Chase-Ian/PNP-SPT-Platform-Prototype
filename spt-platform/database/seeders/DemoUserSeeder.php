<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Three demo accounts (trainee, supervisor, admin) for local development and
 * presentations. They must never exist on the live site with a shared or
 * published password: that would let anyone in. So this seeder does nothing
 * in production, and the password comes from DEMO_PASSWORD in your local
 * .env, or is generated and printed once.
 */
class DemoUserSeeder extends Seeder
{
    private const ACCOUNTS = [
        'maria.cruz@pnp.gov.ph' => [
            'first_name' => 'Maria',
            'last_name' => 'Cruz',
            'rank' => 'Patrolman/Patrolwoman (Pat)',
            'unit_office' => 'Manila Police District - Station 1 (Ermita)',
            'region' => 'NCRPO - National Capital Region Police Office',
            'role' => 'trainee',
        ],
        'admin.demo@pnp.gov.ph' => [
            'first_name' => 'Admin',
            'last_name' => 'Demo',
            'rank' => 'Police Colonel (PCol)',
            'unit_office' => 'PNP National Headquarters',
            'region' => 'NHQ Camp Crame - PNP National Headquarters',
            'role' => 'admin',
        ],
        'supervisor.demo@pnp.gov.ph' => [
            'first_name' => 'Supervisor',
            'last_name' => 'Demo',
            'rank' => 'Police Lieutenant Colonel (PLtCol)',
            'unit_office' => 'Regional Training Command',
            'region' => 'NCRPO - National Capital Region Police Office',
            'role' => 'supervisor',
        ],
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Demo accounts are not created in production.');

            return;
        }

        $chosen = (string) env('DEMO_PASSWORD', '');
        $plain = $chosen !== '' ? $chosen : Str::password(16);
        $hash = Hash::make($plain);

        foreach (self::ACCOUNTS as $email => $attributes) {
            // withTrashed: a deleted demo account is reinstated by an admin, not duplicated.
            User::withTrashed()->firstOrCreate(
                ['email' => $email],
                [...$attributes, 'password' => $hash, 'two_factor_verified' => true],
            );
        }

        $emails = array_keys(self::ACCOUNTS);

        // Demo accounts skip the email step so local demos work offline...
        User::whereIn('email', $emails)->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);

        // ...and start with the current Terms of Service and Privacy Notice accepted.
        User::whereIn('email', $emails)->whereNull('terms_accepted_version')
            ->update(['terms_accepted_version' => config('legal.version'), 'terms_accepted_at' => now()]);

        if ($chosen === '') {
            $this->command?->info("Password for new demo accounts: {$plain} (set DEMO_PASSWORD in .env to choose one)");
        }

        // No sample notifications: notifications come only from real events.
    }
}
