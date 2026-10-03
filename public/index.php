<?php

use App\View\View;

$root = dirname(__DIR__);
require $root . '/bootstrap.php';

$view = new View($root, [
    'name' => $_ENV['APP_NAME'] ?? 'Adult Posts',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
]);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
header('Content-Type: text/html; charset=utf-8');
