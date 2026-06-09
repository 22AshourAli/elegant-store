<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default shipping rate when no specific rate is configured.
    |--------------------------------------------------------------------------
    */
    'default_rate' => env('SHIPPING_DEFAULT_RATE', 50),

    /*
    |--------------------------------------------------------------------------
    | Courier configurations for future API integration.
    | Each driver should implement App\Contracts\CourierDriver interface.
    |--------------------------------------------------------------------------
    */
    'couriers' => [
        'bosta' => [
            'api_key' => env('BOSTA_API_KEY'),
            'base_url' => env('BOSTA_BASE_URL', 'https://api.bosta.com'),
        ],
        'aramex' => [
            'username' => env('ARAMEX_USERNAME'),
            'password' => env('ARAMEX_PASSWORD'),
            'account_number' => env('ARAMEX_ACCOUNT_NUMBER'),
            'base_url' => env('ARAMEX_BASE_URL', 'https://ws.aramex.net'),
        ],
    ],
];
