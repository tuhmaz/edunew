<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'], 
    'allowed_methods' => ['*'], 
    'allowed_origins' => ['https://alemedu.com', 'https://www.alemedu.com'], 
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'], 
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];