<?php

return [
    'default_courier' => env('DEFAULT_COURIER', 'bosta'),

    'couriers' => [
        'bosta' => [
            'api_key' => env('BOSTA_API_KEY', ''),
            'base_url' => env('BOSTA_BASE_URL', 'https://api.bosta.co/v2/'),
        ],
        'aramex' => [
            'username' => env('ARAMEX_USERNAME'),
            'password' => env('ARAMEX_PASSWORD'),
            'account_number' => env('ARAMEX_ACCOUNT_NUMBER'),
            'base_url' => env('ARAMEX_BASE_URL', 'https://ws.aramex.net'),
        ],
    ],
];
