<?php

namespace App\Http;

final class Router
{
    private array $routes = [];

    /** @param array{class-string, string} $handler */
    public function get(string $path, array $handler): void
    {
        $segments = explode('/', trim($path, '/'));
        foreach ($segments as &$segment) {
            if (preg_match('/^\{([a-zA-Z_][a-zA-Z0-9_]*)\}$/D', $segment, $parameter)) {
                $segment = '(?P<' . $parameter[1] . '>[a-z0-9]+(?:-[a-z0-9]+)*)';
            } else {
                $segment = preg_quote($segment, '~');
            }
        }
        unset($segment);

        $pattern = $path === '/' ? '~^/$~D' : '~^/' . implode('/', $segments) . '/?$~D';
        $this->routes[] = ['pattern' => $pattern, 'handler' => $handler];
    }

    /** @return array{handler: array{class-string, string}, parameters: array} */
    public function match(?string $path): array
    {
        foreach ($this->routes as $route) {
            if ($path !== null && preg_match($route['pattern'], $path, $matches)) {
                return [
                    'handler' => $route['handler'],
                    'parameters' => array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY),
                ];
            }
        }

        throw new NotFound();
    }
}
