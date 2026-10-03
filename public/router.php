<?php

// Router for PHP's development server. Serve only files inside public/.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = is_string($path) ? realpath(__DIR__ . rawurldecode($path)) : false;
if ($file !== false && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR)
    && is_file($file) && strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') {
    return false;
}

require __DIR__ . '/index.php';
