<?php
return [
    'paths' => [
        'api/*',
        'login', // 追加
        'logout', // 追加
        'sanctum/csrf-cookie',
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => false,
    'max_age' => false,
    'supports_credentials' => true,
];
