<?php

return [
    'paths' => ['*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://smartbookingvr.hu',
        'https://www.smartbookingvr.hu',
        'http://localhost:8080',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // EZT ÁLLÍTSD TRUE-RA A LOGIN MIATT
];
