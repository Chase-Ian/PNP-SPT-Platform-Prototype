<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Two-factor authentication requirement
    |--------------------------------------------------------------------------
    |
    | Who must have 2FA set up before they can use the platform:
    |   none  - nobody is forced (2FA stays optional on the profile page)
    |   staff - admins and supervisors
    |   all   - every account, trainees included
    |
    | Changing this needs no code change: set REQUIRE_2FA in .env.
    |
    */

    'require_2fa' => env('REQUIRE_2FA', 'none'),

    /*
    | When enforcement started (a date, e.g. 2026-12-01). Accounts created
    | before this date get a grace period of `grace_days` days from it, during
    | which they see a reminder instead of being forced into setup. Accounts
    | created on or after it are enforced right away. Leave empty for no grace.
    */

    'enforced_since' => env('TWO_FACTOR_ENFORCED_SINCE'),

    'grace_days' => (int) env('TWO_FACTOR_GRACE_DAYS', 14),

    /*
    | Comma-separated emails that are never forced into 2FA setup, e.g. the
    | demo accounts used in live presentations. Keep this empty in production.
    */

    'exempt_emails' => array_values(array_filter(array_map(
        fn ($e) => strtolower(trim($e)),
        explode(',', (string) env('TWO_FACTOR_EXEMPT_EMAILS', ''))
    ))),

    /*
    | Minutes an emailed "lost my authenticator" link stays valid.
    */

    'recovery_link_minutes' => (int) env('TWO_FACTOR_RECOVERY_LINK_MINUTES', 30),
];
