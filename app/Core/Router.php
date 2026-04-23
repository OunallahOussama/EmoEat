<?php

namespace App\Core;

/**
 * Router — maps HTTP method + URI pattern to controller/action
 * Supports {param} placeholders in routes
 */
class Router
{
    private array $routes = [];

    public function add(string $method, string $pattern, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    /**
     * @return array{controller: string, action: string, params: array}|null
     */
    public function match(string $method, string $uri): ?array
    {
        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $regex = $this->patternToRegex($route['pattern']);

            if (preg_match($regex, $uri, $matches)) {
                // Extract named params
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                return [
                    'controller' => $route['controller'],
                    'action'     => $route['action'],
                    'params'     => array_values($params),
                ];
            }
        }

        return null;
    }

    private function patternToRegex(string $pattern): string
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        return '#^' . $regex . '$#';
    }
}
