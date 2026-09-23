<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var list<array{method:string,pattern:string,handler:array{0:class-string,1:string}}> */
    private array $routes = [];

    /** @param array{0:class-string,1:string} $handler */
    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /** @param array{0:class-string,1:string} $handler */
    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /** @param array{0:class-string,1:string} $handler */
    private function add(string $method, string $pattern, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => '/' . trim($pattern, '/'),
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $path = $request->path();
        $method = $request->method();
        $pathMatched = false;

        foreach ($this->routes as $route) {
            $params = $this->match($route['pattern'], $path);

            if ($params === null) {
                continue;
            }

            $pathMatched = true;

            if ($route['method'] !== $method) {
                continue;
            }

            $request->setRouteParams($params);
            [$class, $action] = $route['handler'];

            $controller = new $class();
            echo $controller->{$action}($request);

            return;
        }

        if ($pathMatched) {
            $allowed = [];
            foreach ($this->routes as $route) {
                if ($this->match($route['pattern'], $path) !== null) {
                    $allowed[] = $route['method'];
                }
            }

            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($allowed)));
            echo 'Method Not Allowed';

            return;
        }

        Response::notFound();
    }

    /** @return array<string,string>|null */
    private function match(string $pattern, string $path): ?array
    {
        // Quote the literal parts so "/sitemap.xml" means a real dot, not "any character".
        $regex = '';
        foreach (preg_split('#(\{[a-z_]+\})#', $pattern, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [] as $part) {
            $regex .= preg_match('#^\{([a-z_]+)\}$#', $part, $name) === 1
                ? '(?P<' . $name[1] . '>[A-Za-z0-9_-]+)'
                : preg_quote($part, '#');
        }

        if (!preg_match('#^' . $regex . '$#', $path, $matches)) {
            return null;
        }

        return array_filter($matches, static fn (string|int $key): bool => is_string($key), ARRAY_FILTER_USE_KEY);
    }
}
