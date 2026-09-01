<?php
declare(strict_types=1);
namespace Sierra\Router;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use function FastRoute\simpleDispatcher;

final class Router
{
    /** @var Route[] */
    private array $routes = [];
    private string $groupPrefix = '';
    private array $groupMiddleware = [];

    public function add(string $method, string $uri, mixed $handler): Route
    {
        $normalizedUri = '/' . ltrim($uri, '/');
        if ($normalizedUri !== '/' && str_ends_with($normalizedUri, '/')) {
            $normalizedUri = rtrim($normalizedUri, '/');
        }

        $fullUri = $this->groupPrefix . ($normalizedUri === '/' && $this->groupPrefix !== '' ? '' : $normalizedUri);
        $fullUri = '/' . ltrim($fullUri, '/');

        $route = new Route(strtoupper($method), $fullUri, $handler);
        if (!empty($this->groupMiddleware)) {
            $route->middleware($this->groupMiddleware);
        }

        $this->routes[] = $route;
        return $route;
    }

    public function get(string $uri, mixed $handler): Route { return $this->add('GET', $uri, $handler); }
    public function post(string $uri, mixed $handler): Route { return $this->add('POST', $uri, $handler); }
    public function put(string $uri, mixed $handler): Route { return $this->add('PUT', $uri, $handler); }
    public function patch(string $uri, mixed $handler): Route { return $this->add('PATCH', $uri, $handler); }
    public function delete(string $uri, mixed $handler): Route { return $this->add('DELETE', $uri, $handler); }
    public function options(string $uri, mixed $handler): Route { return $this->add('OPTIONS', $uri, $handler); }
    public function head(string $uri, mixed $handler): Route { return $this->add('HEAD', $uri, $handler); }

    /**
     * @param string[] $methods
     * @return Route[]
     */
    public function match(array $methods, string $uri, mixed $handler): array
    {
        $routes = [];
        foreach ($methods as $method) {
            $routes[] = $this->add(strtoupper($method), $uri, $handler);
        }
        return $routes;
    }

    /** @return Route[] */
    public function any(string $uri, mixed $handler): array
    {
        return $this->match(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'HEAD'], $uri, $handler);
    }

    public function group(string|array $attributes, \Closure $callback): void
    {
        $prefix = is_array($attributes) ? ($attributes['prefix'] ?? '') : $attributes;
        $middleware = is_array($attributes) ? (array)($attributes['middleware'] ?? []) : [];

        $prevPrefix = $this->groupPrefix;
        $prevMiddleware = $this->groupMiddleware;

        $normalizedPrefix = '/' . trim($prefix, '/');
        $this->groupPrefix = $prevPrefix . ($normalizedPrefix === '/' ? '' : $normalizedPrefix);
        $this->groupMiddleware = array_merge($prevMiddleware, $middleware);

        $callback($this);

        $this->groupPrefix = $prevPrefix;
        $this->groupMiddleware = $prevMiddleware;
    }

    /** @return Route[] */
    public function getRoutes(): array { return $this->routes; }

    public function dispatch(string $method, string $uri): array
    {
        $dispatcher = simpleDispatcher(function(RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route->method, $route->uri, $route);
            }
        });

        return $dispatcher->dispatch(strtoupper($method), $uri);
    }
}
