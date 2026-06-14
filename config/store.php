<?php

return [
    // Admin contact details
    'admin_phone' => env('ADMIN_PHONE', '201094022327'),
    'admin_whatsapp' => env('ADMIN_WHATSAPP', env('ADMIN_PHONE', '01094022327')),
    'admin_email' => env('ADMIN_EMAIL', 'ashourali1v@gmail.com'),

    // Social media
    'facebook' => env('STORE_FACEBOOK', 'https://www.facebook.com/ashour.ali.963'),
    'instagram' => env('STORE_INSTAGRAM', 'https://www.instagram.com/ashour.ali.963/'),
    'whatsapp_url' => env('STORE_WHATSAPP_URL', 'https://api.whatsapp.com/send/?phone=201094022327&text&type=phone_number&app_absent=0'),

    // Default shipping fallback (in EGP)
    'default_shipping' => env('DEFAULT_SHIPPING', 30),

    // Low stock threshold (notify when stock drops to this or below)
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 1),
];
