<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', '*'], // Tambahkan '*' sementara untuk memastikan semua route ter-cover

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000', 'http://127.0.0.1:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // <--- Ini WAJIB true jika pakai Sanctum / Cookie
];
