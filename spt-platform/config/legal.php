<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Terms of Service and Privacy Notice
    |--------------------------------------------------------------------------
    |
    | Bump LEGAL_VERSION whenever either document changes in a way users
    | should know about. Everyone who accepted an older version is asked to
    | accept again on their next page visit (see EnsureAccountIsSecured).
    |
    */

    'version' => env('LEGAL_VERSION', '2026-11-24'),

    'effective_date' => env('LEGAL_EFFECTIVE_DATE', '24 November 2026'),

    // Shows a "draft for legal review" banner on both documents. Set to false
    // only after the PNP Legal Service and the Data Protection Officer have
    // approved the text and every [bracketed placeholder] is filled in.
    'draft' => (bool) env('LEGAL_DRAFT', true),

    // The personal information controller under RA 10173.
    'controller' => [
        'name' => env('LEGAL_CONTROLLER_NAME', 'Philippine National Police'),
        'office' => env('LEGAL_CONTROLLER_OFFICE', 'Directorate for Human Resource and Doctrine Development (DHRDD)'),
        'address' => env('LEGAL_CONTROLLER_ADDRESS') ?: '[Office address]',
    ],

    'dpo' => [
        'email' => env('LEGAL_DPO_EMAIL') ?: '[Data Protection Officer email]',
        'phone' => env('LEGAL_DPO_PHONE') ?: '[Data Protection Officer phone]',
    ],

    // Where each service provider processes data. Keep in step with the
    // actual deployment (render.yaml, AWS_DEFAULT_REGION, MAIL_*).
    'processors' => [
        'hosting' => env('LEGAL_HOSTING_PROVIDER', 'Render (application servers in the United States)'),
        'database' => env('LEGAL_DATABASE_PROVIDER') ?: '[Database hosting provider and country]',
        'storage' => env('LEGAL_STORAGE_PROVIDER', 'Amazon Web Services S3 (Sydney, Australia region)'),
        'email' => env('LEGAL_EMAIL_PROVIDER') ?: '[Email delivery provider and country]',
    ],

    // Turn on when the AI lesson assistant is deployed, so the Privacy Notice
    // describes it.
    'ai_assistant' => (bool) env('LEGAL_AI_ASSISTANT', false),
];
