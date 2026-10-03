<?php

namespace App\Http;

final class Request
{
    public readonly string $method;
    public readonly ?string $path;
    public readonly array $query;

    public function __construct(string $method, ?string $path, array $query)
    {
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
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
