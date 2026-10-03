<?php
return [
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
    'name' => $_ENV['DB_NAME'] ?? 'adult_posts',
    'user' => $_ENV['DB_USER'] ?? 'adult_posts',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
];
