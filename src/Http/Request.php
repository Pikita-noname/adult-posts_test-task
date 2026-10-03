<?php

namespace App\Http;

final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly ?string $path,
        public readonly array $query,
    ) {
    }

    public static function capture(): self
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        return new self(
            $_SERVER['REQUEST_METHOD'] ?? 'GET',
            is_string($path) ? $path : null,
            $_GET,
        );
    }
}
