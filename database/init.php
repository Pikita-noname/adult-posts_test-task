<?php

use App\Database\Connection;

try {
    $config = require dirname(__DIR__) . '/bootstrap.php';
    $schema = file_get_contents(__DIR__ . '/schema.sql');

    if ($schema === false) {
        throw new RuntimeException('Cannot read database/schema.sql');
    }

    Connection::create($config['db'])->exec($schema);
    echo "Database schema initialized\n";
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
