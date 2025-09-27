<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default gateway that will be used when no
    | specific gateway is requested. Make sure this gateway is enabled
    | in the enabled gateways list below.
    |
    */

    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),

    /*
    |--------------------------------------------------------------------------
    | Enabled Payment Gateways
    |--------------------------------------------------------------------------
    |
    | List of enabled payment gateways. Only gateways listed here will be
    | available for processing payments. Remove gateways from this list
    | to disable them completely.
    |
    */

    'enabled' => array_filter(explode(',', env('PAYMENT_GATEWAYS_ENABLED', 'stripe,paypal,credit_card'))),

    /*
    |--------------------------------------------------------------------------
    | Gateway Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration for each payment gateway. Each gateway can have its own
    | set of configuration options loaded from environment variables.
    |
    */

    'gateways' => [

        'credit_card' => [
            'enabled' => env('CREDIT_CARD_GATEWAY_ENABLED', false),
            'name' => 'Credit Card Gateway',
            'description' => 'Process credit card payments',
            'class' => \App\Services\Payments\Gateways\CreditCardGateway::class,
            'config' => [
                'api_key' => env('CREDIT_CARD_GATEWAY_API_KEY'),
                'secret' => env('CREDIT_CARD_GATEWAY_SECRET'),
                'sandbox' => env('CREDIT_CARD_GATEWAY_SANDBOX', true),
                'api_url' => env('CREDIT_CARD_GATEWAY_SANDBOX', true)
                    ? 'https://api-sandbox.creditcard.com/v1'
                    : 'https://api.creditcard.com/v1',
            ],
        ],

        'paypal' => [
            'enabled' => env('PAYPAL_GATEWAY_ENABLED', false),
            'name' => 'PayPal Gateway',
            'description' => 'Process PayPal payments',
            'class' => \App\Services\Payments\Gateways\PaypalGateway::class,
            'config' => [
                'client_id' => env('PAYPAL_CLIENT_ID'),
                'client_secret' => env('PAYPAL_CLIENT_SECRET'),
                'sandbox' => env('PAYPAL_SANDBOX', true),
                'api_url' => env('PAYPAL_SANDBOX', true)
                    ? 'https://api-m.sandbox.paypal.com'
                    : 'https://api-m.paypal.com',
            ],
        ],

        'stripe' => [
            'enabled' => env('STRIPE_GATEWAY_ENABLED', false),
            'name' => 'Stripe Gateway',
            'description' => 'Process Stripe payments',
            'class' => \App\Services\Payments\Gateways\StripeGateway::class,
            'config' => [
                'public_key' => env('STRIPE_PUBLIC_KEY'),
                'secret_key' => env('STRIPE_SECRET_KEY'),
                'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
                'api_version' => '2023-10-16',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Gateway Discovery
    |--------------------------------------------------------------------------
    |
    | Settings for automatic gateway discovery and loading. This allows
    | the system to automatically find and register new gateways.
    |
    */

    'discovery' => [
        'auto_discover' => env('PAYMENT_GATEWAY_AUTO_DISCOVER', true),
        'scan_paths' => [
            app_path('Services/Payments/Gateways'),
        ],
        'cache_duration' => env('PAYMENT_GATEWAY_CACHE_DURATION', 3600), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Settings
    |--------------------------------------------------------------------------
    |
    | Global settings that apply to all payment gateways.
    |
    */

    'global' => [
        'timeout' => env('PAYMENT_GATEWAY_TIMEOUT', 30),
        'retry_attempts' => env('PAYMENT_GATEWAY_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('PAYMENT_GATEWAY_RETRY_DELAY', 1), // seconds
        'currency' => env('PAYMENT_DEFAULT_CURRENCY', 'USD'),
        'test_mode' => env('PAYMENT_TEST_MODE', env('APP_ENV') !== 'production'),
    ],

];
