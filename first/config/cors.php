<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8080', 'http://localhost:3000', 'http://localhost:5173'], // Add your frontend URL
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Access-Control-Allow-Origin'],
    'max_age' => 0,
    'supports_credentials' => true, // Important for cookies/session
];
