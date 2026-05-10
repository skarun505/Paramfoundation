<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ── Razorpay ─────────────────────────────────────────────────────────────
    'razorpay' => [
        'key'    => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
    ],

    // ── WhatsApp (Interakt / WATI / Meta Cloud API) ───────────────────────────
    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'url'   => env('WHATSAPP_URL', 'https://api.interakt.ai/v1/public/message/'),
    ],

];
