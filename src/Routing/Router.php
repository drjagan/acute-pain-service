<?php
namespace Routing;

/**
 * Matches an HTTP request to an explicit controller action definition.
 */
class Router
{
    private $routes;

    /**
     * @param array $routes Route definitions from config/routes.php
     */
    public function __construct(array $routes)
    {
        $this->routes = array_map([$this, 'normalizeRoute'], $routes);
    }

    /**
     * Match a request method and URI to a route definition.
     */
    public function match(string $method, string $uri): array
    {
        $method = strtoupper($method);
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $path = self::normalizePath($uri);
        $allowedMethods = [];

        foreach ($this->routes as $route) {
            $params = [];

            if (!$this->pathMatches($route, $path, $params)) {
                continue;
            }

            if ($route['method'] !== $method) {
                $allowedMethods[] = $route['method'];
                continue;
            }

            return [
                'status' => 'matched',
                'controller' => $route['controller'],
                'action' => $route['action'],
                'params' => $params,
                'policy' => $route['policy'],
            ];
        }

        if (!empty($allowedMethods)) {
            return [
                'status' => 'method_not_allowed',
                'allowed_methods' => array_values(array_unique($allowedMethods)),
            ];
        }

        return ['status' => 'not_found'];
    }

    /**
     * Normalize request and route paths to a shared comparable form.
     */
    public static function normalizePath(string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * Normalize one route definition.
     */
    private function normalizeRoute(array $route): array
    {
        return [
            'method' => strtoupper($route['method']),
            'path' => self::normalizePath($route['path']),
            'controller' => $route['controller'],
            'action' => $route['action'],
            'policy' => $route['policy'] ?? [],
        ];
    }

    /**
     * Determine whether a route path matches and collect ordered parameters.
     */
    private function pathMatches(array $route, string $path, array &$params): bool
    {
        $paramNames = [];
        $regex = $this->compilePath($route['path'], $paramNames);

        if (!preg_match($regex, $path, $matches)) {
            return false;
        }

        foreach ($paramNames as $name) {
            $params[] = $matches[$name];
        }

        return true;
    }

    /**
     * Compile a route path with {parameter} segments into a regular expression.
     */
    private function compilePath(string $path, array &$paramNames): string
    {
        if ($path === '/') {
            return '#^/$#';
        }

        $segments = explode('/', trim($path, '/'));
        $compiled = [];

        foreach ($segments as $segment) {
            if (preg_match('/^\{([A-Za-z_][A-Za-z0-9_]*)\}$/', $segment, $match)) {
                $paramNames[] = $match[1];
                $compiled[] = '(?P<' . $match[1] . '>[^/]+)';
                continue;
            }

            $compiled[] = preg_quote($segment, '#');
        }

        return '#^/' . implode('/', $compiled) . '/?$#';
    }
}
