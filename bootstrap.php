<?php

use Dotenv\Dotenv;

require_once __DIR__ . '/vendor/autoload.php';

// PHP may omit E from variables_order; preserve process environment overrides.
$_ENV += getenv();
Dotenv::createImmutable(__DIR__)->safeLoad();

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

return [
    'db' => require __DIR__ . '/config/db.php',
];
