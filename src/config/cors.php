<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3876',
        'http://127.0.0.1:5173',
        'https://zigzagdev.github.io',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // Sanctum's cookie-based SPA auth sends requests with `credentials: 'include'`.
    // If this is false, the browser will discard the session cookie on cross-origin
    // responses even though the cookie itself was issued successfully, so the
    // frontend appears logged in but authenticated requests (e.g. fetching the
    // current user) fail silently.
    'supports_credentials' => true,
];